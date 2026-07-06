package com.metrics.processor.topology;

import com.fasterxml.jackson.databind.ObjectMapper;
import com.metrics.processor.model.HttpEvent;
import org.apache.kafka.clients.consumer.ConsumerRecord;
import org.apache.kafka.streams.processor.TimestampExtractor;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

/**
 * Extracts the timestamp from the event payload itself ("event_timestamp") so that
 * windowed aggregation reflects when the HTTP request actually happened on the
 * Laravel side, rather than when Kafka happened to receive/process the record.
 * Falls back to the record's own timestamp if the payload can't be parsed.
 */
public class EventTimestampExtractor implements TimestampExtractor {

    private static final Logger log = LoggerFactory.getLogger(EventTimestampExtractor.class);
    private static final ObjectMapper MAPPER = new ObjectMapper().findAndRegisterModules();

    @Override
    public long extract(ConsumerRecord<Object, Object> record, long partitionTime) {
        try {
            Object value = record.value();
            if (value == null) {
                return fallback(record, partitionTime);
            }
            HttpEvent event = MAPPER.readValue(value.toString(), HttpEvent.class);
            if (event.getEventTimestamp() != null) {
                return event.getEventTimestamp().toEpochMilli();
            }
        } catch (Exception e) {
            log.warn("Could not extract event_timestamp from record at offset {}: {}", record.offset(), e.getMessage());
        }
        return fallback(record, partitionTime);
    }

    private long fallback(ConsumerRecord<Object, Object> record, long partitionTime) {
        return record.timestamp() >= 0 ? record.timestamp() : partitionTime;
    }
}
