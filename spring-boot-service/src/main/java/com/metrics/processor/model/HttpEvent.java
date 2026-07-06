package com.metrics.processor.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;

import java.time.Instant;

/**
 * Mirrors the JSON payload published by {@code App\Http\Services\EventService}
 * on the Laravel side:
 * {
 *   "event_id": "...",
 *   "api_id": "...",
 *   "event_type": "click-event",
 *   "url": "/products/42",
 *   "event_timestamp": "2026-07-04T12:00:00.000Z"
 * }
 */
@JsonIgnoreProperties(ignoreUnknown = true)
public class HttpEvent {

    @JsonProperty("event_id")
    private String eventId;

    @JsonProperty("api_id")
    private String apiId;

    @JsonProperty("event_type")
    private String eventType;

    @JsonProperty("url")
    private String url;

    @JsonProperty("event_timestamp")
    private Instant eventTimestamp;

    public HttpEvent() {
    }

    public String getEventId() {
        return eventId;
    }

    public void setEventId(String eventId) {
        this.eventId = eventId;
    }

    public String getApiId() {
        return apiId;
    }

    public void setApiId(String apiId) {
        this.apiId = apiId;
    }

    public String getEventType() {
        return eventType;
    }

    public void setEventType(String eventType) {
        this.eventType = eventType;
    }

    public String getUrl() {
        return url;
    }

    public void setUrl(String url) {
        this.url = url;
    }

    public Instant getEventTimestamp() {
        return eventTimestamp;
    }

    public void setEventTimestamp(Instant eventTimestamp) {
        this.eventTimestamp = eventTimestamp;
    }

    public boolean isValid() {
        return eventType != null && !eventType.isBlank() && url != null && !url.isBlank();
    }
}
