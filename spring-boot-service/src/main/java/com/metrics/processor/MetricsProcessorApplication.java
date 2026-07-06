package com.metrics.processor;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.boot.context.properties.ConfigurationPropertiesScan;
import org.springframework.kafka.annotation.EnableKafkaStreams;

@SpringBootApplication
@EnableKafkaStreams
@ConfigurationPropertiesScan
public class MetricsProcessorApplication {

    public static void main(String[] args) {
        SpringApplication.run(MetricsProcessorApplication.class, args);
    }
}
