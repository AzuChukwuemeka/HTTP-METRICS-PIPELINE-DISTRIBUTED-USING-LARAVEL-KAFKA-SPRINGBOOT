package com.metrics.processor.controller;

import com.metrics.processor.dto.MetricAggregateResponse;
import com.metrics.processor.repository.EventAggregateRepository;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.Parameter;
import io.swagger.v3.oas.annotations.tags.Tag;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

import java.sql.Timestamp;
import java.util.List;
import java.util.Map;

@RestController
@Tag(name = "Metrics", description = "Query the real-time windowed aggregates produced by the Kafka Streams pipeline")
public class MetricsController {

    private final EventAggregateRepository repository;

    public MetricsController(EventAggregateRepository repository) {
        this.repository = repository;
    }

    @Operation(
            summary = "List recent windowed event counts",
            description = "Reads the tbl_events table, which is continuously upserted by the Kafka Streams "
                    + "topology as new tumbling windows are computed from the http-events topic."
    )
    @GetMapping("/api/metrics")
    public List<MetricAggregateResponse> getRecentMetrics(
            @Parameter(description = "Filter by event type, e.g. click-event") @RequestParam(required = false) String eventType,
            @Parameter(description = "Filter by URL path") @RequestParam(required = false) String url,
            @Parameter(description = "Max rows to return") @RequestParam(defaultValue = "50") int limit
    ) {
        List<Map<String, Object>> rows = repository.findRecent(eventType, url, limit);
        return rows.stream()
                .map(row -> new MetricAggregateResponse(
                        String.valueOf(row.get("event_id")),
                        String.valueOf(row.get("event_type")),
                        String.valueOf(row.get("url")),
                        ((Timestamp) row.get("time_bucket")).toInstant(),
                        ((Number) row.get("count")).longValue()
                ))
                .toList();
    }
}
