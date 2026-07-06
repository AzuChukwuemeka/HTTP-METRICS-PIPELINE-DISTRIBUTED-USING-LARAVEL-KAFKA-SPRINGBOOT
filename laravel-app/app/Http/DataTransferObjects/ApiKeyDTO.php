<?php

namespace App\Http\DataTransferObjects;

use Carbon\Carbon;

class ApiKeyDTO implements \JsonSerializable
{
    private string $api_key;
    private string $api_name;
    private Carbon $expires_at;
    private string $created_at;
    public function __construct(
        string $api_key,
        string $api_name,
        Carbon $expires_at,
        string $created_at
    ){
        $this->api_key = $api_key;
        $this->api_name = $api_name;
        $this->expires_at = $expires_at;
        $this->created_at = $created_at;
    }

    public function getApiKey(): string
    {
        return $this->api_key;
    }

    public function getApiName(): string
    {
        return $this->api_name;
    }

    public function getExpiresAt(): Carbon
    {
        return $this->expires_at;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    public function jsonSerialize(): array
    {
        return [
            'api_key' => $this->api_key,
            'api_name' => $this->api_name,
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at
        ];
    }
}
