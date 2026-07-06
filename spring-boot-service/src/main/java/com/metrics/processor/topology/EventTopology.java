package com.metrics.processor.topology;

import com.fasterxml.jackson.databind.ObjectMapper;
import com.metrics.processor.config.KafkaProcessorProperties;
import com.metrics.processor.model.HttpEvent;
import com.metrics.processor.repository.EventAggregateRepository;
import org.apache.kafka.common.serialization.Serdes;
import org.apache.kafka.streams.KeyValue;
import org.apache.kafka.streams.StreamsBuilder;
import org.apache.kafka.streams.kstream.Consumed;
import org.apache.kafka.streams.kstream.KStream;
import org.apache.kafka.streams.kstream.KTable;
import org.apache.kafka.streams.kstream.Materialized;
import org.apache.kafka.streams.kstream.TimeWindows;
import org.apache.kafka.streams.kstream.Windowed;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.stereotype.Component;

import java.time.Duration;
import java.time.Instant;

/**
 * The real-time aggregation pipeline described in the project README:
 *
 *   Laravel  --(produces JSON)-->  "http-events" topic
 *      --> keyed by "eventType::url"
 *      --> tumbling window (default 60s, 30s grace for late events)
 *      --> windowed count()
 *      --> upsert absolute count into the shared tbl_events table
 *
 * Each emission from the windowed KTable's changelog stream carries the
 * up-to-date running total for that window, so writes to Postgres are
 * idempotent "set" upserts rather than increments.
 */
@Component
public class EventTopology {

    private static final Logger log = LoggerFactory.getLogger(EventTopology.class);
    private static final ObjectMapper MAPPER = new ObjectMapper().findAndRegisterModules();
    private static final String KEY_SEPARATOR = "::";

    public EventTopology(StreamsBuilder streamsBuilder,
                          KafkaProcessorProperties properties,
                          EventAggregateRepository repository) {
        buildTopology(streamsBuilder, properties, repository);
    }

    private void buildTopology(StreamsBuilder streamsBuilder,
                                KafkaProcessorProperties properties,
                                EventAggregateRepository repository) {

        Consumed<String, String> consumed = Consumed
                .with(Serdes.String(), Serdes.String())
                .withTimestampExtractor(new EventTimestampExtractor());

        KStream<String, String> rawEvents = streamsBuilder.stream(properties.getEventsTopic(), consumed);

        KStream<String, HttpEvent> parsedEvents = rawEvents
                .mapValues(EventTopology::parseEvent)
                .filter((key, event) -> event != null && event.isValid());

        KStream<String, HttpEvent> rekeyed = parsedEvents
                .selectKey((key, event) -> event.getEventType() + KEY_SEPARATOR + event.getUrl());

        TimeWindows windows = TimeWindows
                .ofSizeAndGrace(
                        Duration.ofSeconds(properties.getWindowSizeSeconds()),
                        Duration.ofSeconds(properties.getGracePeriodSeconds())
                );

        KTable<Windowed<String>, Long> windowedCounts = rekeyed
                .groupByKey()
                .windowedBy(windows)
                .count(Materialized.with(Serdes.String(), Serdes.Long()));

        windowedCounts
                .toStream()
                .filter((windowedKey, count) -> count != null)
                .map((windowedKey, count) -> {
                    String[] parts = windowedKey.key().split(KEY_SEPARATOR, 2);
                    String eventType = parts[0];
                    String url = parts.length > 1 ? parts[1] : "";
                    Instant windowStart = windowedKey.window().startTime();
                    return KeyValue.pair(windowedKey.key(), new WindowCount(eventType, url, windowStart, count));
                })
                .foreach((key, windowCount) -> {
                    try {
                        repository.upsertWindowCount(
                                windowCount.eventType(),
                                windowCount.url(),
                                windowCount.windowStart(),
                                windowCount.count()
                        );
                    } catch (Exception e) {
                        log.error("Failed to upsert window count for key={} window={}: {}",
                                key, windowCount.windowStart(), e.getMessage(), e);
                    }
                });
    }

    private static HttpEvent parseEvent(String json) {
        try {
            return MAPPER.readValue(json, HttpEvent.class);
        } catch (Exception e) {
            log.warn("Skipping unparseable event payload: {}", e.getMessage());
            return null;
        }
    }

    private record WindowCount(String eventType, String url, Instant windowStart, long count) {
    }
}
