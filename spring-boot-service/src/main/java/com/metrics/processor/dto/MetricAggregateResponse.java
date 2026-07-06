package com.metrics.processor.dto;

import io.swagger.v3.oas.annotations.media.Schema;

import java.time.Instant;

@Schema(description = "A single windowed count for one (event_type, url) combination")
public record MetricAggregateResponse(
        @Schema(example = "b3f1c2...") String eventId,
        @Schema(example = "click-event") String eventType,
        @Schema(example = "/products/42") String url,
        @Schema(description = "Start of the tumbling window this count belongs to") Instant timeBucket,
        @Schema(example = "17") long count
) {
}
