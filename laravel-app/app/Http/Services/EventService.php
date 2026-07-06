<?php

namespace App\Http\Services;

use App\Utils\HttpMetricEventTypes;
use Illuminate\Support\Facades\Log;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;
use Ramsey\Uuid\Uuid;

class EventService
{
    private ApiKeyInfoService $apiKeyInfoService;

    public function __construct(ApiKeyInfoService $apiKeyInfoService)
    {
        $this->apiKeyInfoService = $apiKeyInfoService;
    }

    /**
     * Validates and publishes an HTTP metric event onto the "http-events"
     * Kafka topic. The Spring Boot Kafka Streams service consumes this
     * topic, windows the events by (event_type, url) and writes the
     * aggregated counts back into the shared tbl_events table.
     *
     * @throws \Exception
     */
    public function registerEvent(string $api_id, string $event_type, string $url): bool
    {
        if (!in_array($event_type, HttpMetricEventTypes::all(), true)) {
            throw new \Exception("Invalid Event Type");
        }

        $event_id = Uuid::uuid4()->toString();
        $event_timestamp = now()->toJSON();

        $payload = [
            'event_id' => $event_id,
            'api_id' => $api_id,
            'event_type' => $event_type,
            'url' => $url,
            'event_timestamp' => $event_timestamp,
        ];

        $message = new Message(
            headers: ['source' => 'laravel-event-collector'],
            body: $payload,
            key: $event_type . '::' . $url,
        );

        Kafka::publish(config('kafka.brokers'))
            ->onTopic(config('kafka.topics.http_events'))
            ->withMessage($message)
            ->send();

        Log::info('Published http event to Kafka', $payload);

        $this->logApiKeyUsage($api_id, $event_type, $url);

        return true;
    }

    /**
     * Best-effort usage log entry for the API key that submitted this event.
     * Logging failures must never break event ingestion, so errors are caught
     * and logged rather than propagated.
     */
    private function logApiKeyUsage(string $api_id, string $event_type, string $url): void
    {
        try {
            $this->apiKeyInfoService->insertApiKeyLog(
                Uuid::fromString($api_id),
                $event_type,
                $url,
                200
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to record API key usage log', [
                'api_id' => $api_id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
