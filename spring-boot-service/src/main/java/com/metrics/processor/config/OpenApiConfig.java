package com.metrics.processor.config;

import io.swagger.v3.oas.models.OpenAPI;
import io.swagger.v3.oas.models.info.Info;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;

@Configuration
public class OpenApiConfig {

    @Bean
    public OpenAPI metricsProcessorOpenApi() {
        return new OpenAPI().info(new Info()
                .title("Metrics Processor API")
                .version("1.0.0")
                .description("Kafka Streams service that consumes the 'http-events' topic published "
                        + "by the Laravel collector, windows events by (event_type, url), and writes "
                        + "the aggregated counts into the shared tbl_events table."));
    }
}
