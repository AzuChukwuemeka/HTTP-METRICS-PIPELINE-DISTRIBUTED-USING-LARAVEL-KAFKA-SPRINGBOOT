package com.metrics.processor.repository;

import org.springframework.jdbc.core.JdbcTemplate;
import org.springframework.stereotype.Repository;

import java.sql.Timestamp;
import java.time.Instant;
import java.util.List;
import java.util.Map;
import java.util.UUID;

/**
 * Reads and writes the {@code tbl_events} table that is created by the Laravel
 * migration (2025_09_12_192118_create_tbl_events.php):
 *
 *   event_id uuid primary key
 *   event_type varchar
 *   url varchar
 *   "time-bucket" timestamp
 *   count bigint
 *   unique(event_type, url, "time-bucket")
 *
 * Both services share this single Postgres database/table: Laravel owns the
 * schema (migrations), this service only ever upserts rows into it.
 * Note the "time-bucket" column name contains a hyphen and must always be
 * double-quoted in SQL.
 */
@Repository
public class EventAggregateRepository {

    private static final String UPSERT_SQL = """
            INSERT INTO tbl_events (event_id, event_type, url, "time-bucket", count, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, now(), now())
            ON CONFLICT (event_type, url, "time-bucket")
            DO UPDATE SET count = EXCLUDED.count, updated_at = now()
            """;

    private static final String RECENT_SQL = """
            SELECT event_id, event_type, url, "time-bucket" AS time_bucket, count
            FROM tbl_events
            WHERE (? IS NULL OR event_type = ?)
              AND (? IS NULL OR url = ?)
            ORDER BY "time-bucket" DESC
            LIMIT ?
            """;

    private final JdbcTemplate jdbcTemplate;

    public EventAggregateRepository(JdbcTemplate jdbcTemplate) {
        this.jdbcTemplate = jdbcTemplate;
    }

    /**
     * Upserts the running total for a single (event_type, url, window) combination.
     * The count passed in is the absolute count for that window as computed by the
     * Kafka Streams windowed KTable, not a delta, so this always sets (not adds to)
     * the stored value.
     */
    public void upsertWindowCount(String eventType, String url, Instant windowStart, long count) {
        jdbcTemplate.update(
                UPSERT_SQL,
                UUID.randomUUID().toString(),
                eventType,
                url,
                Timestamp.from(windowStart),
                count
        );
    }

    public List<Map<String, Object>> findRecent(String eventType, String url, int limit) {
        return jdbcTemplate.queryForList(RECENT_SQL, eventType, eventType, url, url, limit);
    }
}
