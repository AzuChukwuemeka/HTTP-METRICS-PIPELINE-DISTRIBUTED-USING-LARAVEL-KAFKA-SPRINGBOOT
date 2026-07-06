<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Kafka brokers
    |--------------------------------------------------------------------------
    |
    | Comma separated list of host:port pairs, e.g. "kafka1:9092,kafka2:9092".
    | In docker-compose this points at the internal "kafka" service.
    |
    */
    'brokers' => env('KAFKA_BROKERS', 'localhost:9092'),

    'consumers' => [
        'default' => [
            'options' => [
                'group_id' => env('KAFKA_CONSUMER_GROUP', 'laravel-metrics-collector'),
                'auto_offset_reset' => \Junges\Kafka\Config\Config::OFFSET_RESET_EARLIEST,
                'enable_auto_commit' => true,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    |
    | Leave as plaintext for local docker-compose development. Switch to
    | SASL_SSL / SASL_PLAINTEXT with real credentials in production.
    |
    */
    'sasl' => [
        'username' => env('KAFKA_SASL_USERNAME'),
        'password' => env('KAFKA_SASL_PASSWORD'),
        'mechanisms' => env('KAFKA_SASL_MECHANISMS', 'PLAIN'),
    ],

    'security_protocol' => env('KAFKA_SECURITY_PROTOCOL', 'plaintext'),

    /*
    |--------------------------------------------------------------------------
    | Topics used by this application
    |--------------------------------------------------------------------------
    */
    'topics' => [
        'http_events' => env('KAFKA_EVENTS_TOPIC', 'http-events'),
    ],
];
