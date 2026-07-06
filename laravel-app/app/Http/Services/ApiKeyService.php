<?php

namespace App\Http\Services;

use App\Exceptions\ApiExpiredException;
use App\Http\DataTransferObjects\ApiKeyDTO;
use App\Http\Repositories\ApiKeyRepositoryI;
use Ramsey\Uuid\Nonstandard\Uuid;

class ApiKeyService
{
    private ApiKeyRepositoryI $apiKeyRepository;
    public function __construct(ApiKeyRepositoryI $apiKeyRepository)
    {
        $this->apiKeyRepository = $apiKeyRepository;
    }
    public function createApiKey(string $user_id, string $name): ApiKeyDTO
    {
        return $this->apiKeyRepository->createApiKey($user_id, $name);
    }
    /**
     * @throws ApiExpiredException
     */
    public function checkValidityApiKey(string $api_id): void
    {
        $apiKeyDTO = $this->apiKeyRepository->getApiKeyById($api_id);
        if(now() > $apiKeyDTO->getExpiresAt()) throw new ApiExpiredException();
    }
    public function updateLastUsedValue(string $api_id): void
    {
        $this->apiKeyRepository->updateLastUsedValue($api_id);
    }
    public function activateApiKey(string $api_id): void
    {
        $this->apiKeyRepository->updateLastUsedValue($api_id);
    }
    public function deactivateApiKey(string $api_id): void
    {
        $this->apiKeyRepository->deactivateApiKey($api_id);
    }
    public function getAllApiKeys(int $pagenumber) : array{
        return $this->apiKeyRepository->getAllApiKeys($pagenumber);
    }
    public function getAllApiKeysForId(string $user_id, int $pagenumber) : array{
        return $this->apiKeyRepository->getAllApiKeysForId($user_id, $pagenumber);
    }
    public function getApiKeyById(string $api_id): ApiKeyDTO
    {
        return $this->apiKeyRepository->getApiKeyById($api_id);
    }
}
