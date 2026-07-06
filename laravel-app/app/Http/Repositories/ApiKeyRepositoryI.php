<?php

namespace App\Http\Repositories;

use App\Http\DataTransferObjects\ApiKeyDTO;
use Ramsey\Uuid\Nonstandard\Uuid;

interface ApiKeyRepositoryI
{
    public function createApiKey(string $user_id, string $name) : ApiKeyDTO;
    public function getApiKeyById(string $api_id) : ApiKeyDTO;
    public function getAllApiKeys(int $pagenumber): array;
    public function getAllApiKeysForId(string $user_id, int $pagenumber): array;
    public function updateLastUsedValue(string $api_key) : void;
    public function deactivateApiKey(string $api_id) : void;
    public function
    activateApiKey(string $api_id) : void;
}
