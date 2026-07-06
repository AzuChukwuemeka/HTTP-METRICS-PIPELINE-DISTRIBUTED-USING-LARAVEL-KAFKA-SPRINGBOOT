package com.metrics.processor.config;

import org.apache.kafka.common.serialization.Serdes;
import org.apache.kafka.streams.StreamsConfig;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.kafka.annotation.EnableKafkaStreams;
import org.springframework.kafka.annotation.KafkaStreamsDefaultConfiguration;
import org.springframework.kafka.config.KafkaStreamsConfiguration;

import java.util.HashMap;
import java.util.Map;

/**
 * Wires up the Kafka Streams runtime used by {@link com.metrics.processor.topology.EventTopology}.
 * Spring for Apache Kafka manages the KafkaStreams lifecycle (start/stop) for us once this
 * configuration bean is present and {@code @EnableKafkaStreams} is active.
 */
@Configuration
@EnableKafkaStreams
public class KafkaStreamsConfig {

    @Autowired
    private KafkaProcessorProperties properties;

    @Bean(name = KafkaStreamsDefaultConfiguration.DEFAULT_STREAMS_CONFIG_BEAN_NAME)
    public KafkaStreamsConfiguration kStreamsConfig() {
        Map<String, Object> props = new HashMap<>();
        props.put(StreamsConfig.APPLICATION_ID_CONFIG, properties.getApplicationId());
        props.put(StreamsConfig.BOOTSTRAP_SERVERS_CONFIG, properties.getBootstrapServers());
        props.put(StreamsConfig.DEFAULT_KEY_SERDE_CLASS_CONFIG, Serdes.StringSerde.class.getName());
        props.put(StreamsConfig.DEFAULT_VALUE_SERDE_CLASS_CONFIG, Serdes.StringSerde.class.getName());
        // Start from the beginning of the topic on first run so a freshly built
        // demo environment has data to aggregate immediately.
        props.put(StreamsConfig.consumerPrefix("auto.offset.reset"), "earliest");
        // Keep state-store changelog topics small for a local/demo cluster.
        props.put(StreamsConfig.REPLICATION_FACTOR_CONFIG, 1);
        props.put(StreamsConfig.COMMIT_INTERVAL_MS_CONFIG, 1000);
        return new KafkaStreamsConfiguration(props);
    }
}
