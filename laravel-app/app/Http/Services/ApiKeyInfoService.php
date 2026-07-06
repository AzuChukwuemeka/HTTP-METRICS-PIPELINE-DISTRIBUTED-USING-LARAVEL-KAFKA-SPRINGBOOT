<?php

namespace App\Http\Services;

use App\Http\Repositories\ApiKeyInfoRepositoryI;
use Ramsey\Uuid\Uuid;

class ApiKeyInfoService
{
    private ApiKeyInfoRepositoryI $repository;
    public function __construct(ApiKeyInfoRepositoryI $repository)
    {
        $this->repository = $repository;
    }

    public function insertApiKeyLog(Uuid $api_id, string $event_type, string $endpoint, int $status_code): void
    {
        $this->repository->insertApiKeyLog($api_id, $event_type, $endpoint, $status_code);
    }
    public function getApiKeyLogForIdPaginated(Uuid $uuid, int $pagenumber): array
    {
        return $this->repository->getApiKeyLogForIdPaginated($uuid, $pagenumber);
    }
}
