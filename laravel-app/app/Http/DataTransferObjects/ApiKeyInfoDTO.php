<?php

namespace App\Http\DataTransferObjects;

use JsonSerializable;

class ApiKeyInfoDTO implements JsonSerializable
{
    public string $api_id;
    public string $event_type;
    public string $endpoint;
    public int $statuscode;

    /**
     * @param string $api_id
     * @param string $event_type
     * @param string $endpoint
     * @param int $statuscode
     */
    public function __construct(string $api_id, string $event_type, string $endpoint, int $statuscode)
    {
        $this->api_id = $api_id;
        $this->event_type = $event_type;
        $this->endpoint = $endpoint;
        $this->statuscode = $statuscode;
    }

    public function getApiId(): string
    {
        return $this->api_id;
    }

    public function getEventType(): string
    {
        return $this->event_type;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function getStatuscode(): int
    {
        return $this->statuscode;
    }


    public function jsonSerialize()
    {
        return [
            "api_id" => $this->api_id,
            "event_type" => $this->event_type,
            "endpoint" => $this->endpoint,
            "statuscode" => $this->statuscode
        ];
    }
}
