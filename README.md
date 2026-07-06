# Realtime Web Metrics

A full-stack, distributed pipeline for collecting, streaming, and analyzing HTTP/web
events in real time.

```
                 POST /api/events/registerEvent
   Client  ─────────────────────────────────────▶  Laravel API (laravel-app)
                                                       │
                                                       │ publishes JSON
                                                       ▼
                                              Kafka topic "http-events"
                                                       │
                                                       │ consumes
                                                       ▼
                                        Spring Boot Kafka Streams service
                                        (spring-boot-service)
                                          - windows by (event_type, url)
                                          - tumbling window, 60s + 30s grace
                                          - counts events per window
                                                       │
                                                       │ upserts
                                                       ▼
                                        Postgres: tbl_events (shared table)
                                                       ▲
                                                       │ reads
                                        GET /api/metrics (spring-boot-service)
```

Both services share **one Postgres database**. Laravel owns the schema (all
migrations, including `tbl_events`); the Spring Boot service only ever
reads/upserts rows into `tbl_events` — it runs no migrations of its own.

## Services

| Service | Tech | Port | Purpose |
|---|---|---|---|
| `laravel-app` | Laravel 9 / PHP 8.1 | 8000 | User & API-key management (JWT), event ingestion, publishes to Kafka |
| `spring-boot-service` | Spring Boot 3 / Java 17 / Kafka Streams | 8081 | Consumes `http-events`, windows + counts, upserts aggregates, exposes `GET /api/metrics` |
| `postgres` | Postgres 16 | 5432 | Shared database |
| `kafka` + `zookeeper` | Confluent 7.6 | 9092 (29092 host) | Event bus |
| `kafka-ui` | provectuslabs/kafka-ui | 8082 | Browse topics/messages in a browser (optional, handy for debugging) |

## Running everything

Requires Docker and Docker Compose.

```bash
docker compose up --build
```

This will, in order:
1. Start Postgres and wait for it to be healthy.
2. Start Zookeeper, then Kafka, then create the `http-events` topic (`kafka-init`).
3. Build and start `laravel-app`: on first boot the entrypoint copies `.env.example`
   to `.env`, waits for Postgres, generates `APP_KEY` and `JWT_SECRET`, runs
   `php artisan migrate`, and generates the Swagger spec.
4. Once `laravel-app` reports healthy (its Swagger doc route responds), build and
   start `spring-boot-service`, which attaches its Kafka Streams topology and
   starts writing aggregates into `tbl_events`.

First build can take a few minutes (Composer + Maven dependency downloads).

## API documentation

- **Laravel** (Swagger / OpenAPI UI, via `darkaonline/l5-swagger`):
  `http://localhost:8000/api/documentation`
  Raw spec: `http://localhost:8000/docs/api-docs.json`
- **Spring Boot** (springdoc-openapi):
  `http://localhost:8081/swagger-ui.html`
  Raw spec: `http://localhost:8081/v3/api-docs`
- **Kafka UI** (inspect the `http-events` topic live): `http://localhost:8082`

## Typical flow to try it out

1. Register a user: `POST http://localhost:8000/api/users/createUser` with
   `{"email":"you@example.com","password":"secret123"}`.
2. Log in: `POST http://localhost:8000/api/users/login` → returns a JWT bearer token.
3. Create an API key (send the bearer token as `Authorization: Bearer <token>`):
   `POST http://localhost:8000/api/apikeys/createKey/{name}`.
4. Publish events using that key as `X-API-KEY`:
   `POST http://localhost:8000/api/events/registerEvent`
   `{"event_type":"click-event","url":"/products/42"}`
5. Watch aggregates build up (allow ~60–90s for the first window to close):
   `GET http://localhost:8081/api/metrics`

## Local (non-Docker) development

Each service can still be run standalone if you have PHP 8.1 + `ext-rdkafka` +
Composer, and Java 17 + Maven installed locally, plus your own Postgres/Kafka.
Point each service's config (`laravel-app/.env`, `spring-boot-service/src/main/resources/application.yml`)
at wherever those are running — see the `KAFKA_*` / `DB_*` environment variables
each service reads.

## Repository layout

```
laravel-app/            Laravel API (event collection, users, API keys)
spring-boot-service/    Spring Boot Kafka Streams aggregation service
docker-compose.yml      Brings up the full stack
```
