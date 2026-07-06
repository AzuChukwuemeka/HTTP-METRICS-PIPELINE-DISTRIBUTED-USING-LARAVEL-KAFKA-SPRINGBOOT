<?php

namespace App\Http\Repositories;

use Ramsey\Uuid\Uuid;

interface ApiKeyInfoRepositoryI
{
    public function insertApiKeyLog(Uuid $api_id, string $event_type, string $endpoint, int $status_code) : void;
    public function getApiKeyLogForIdPaginated(Uuid $uuid, int $pagenumber) : array;
}
