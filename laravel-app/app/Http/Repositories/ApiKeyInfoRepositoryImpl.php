<?php

namespace App\Http\Repositories;

use App\Http\Controllers\ApiKeyInfo;
use App\Http\DataTransferObjects\ApiKeyInfoDTO;
use Illuminate\Database\ConnectionInterface;
use Ramsey\Uuid\Uuid;

class ApiKeyInfoRepositoryImpl implements ApiKeyInfoRepositoryI
{
    public ConnectionInterface $connection;
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function insertApiKeyLog(Uuid $api_id, string $event_type, string $endpoint, int $status_code): void
    {
        $api_usage_id = Uuid::uuid4()->toString();
        $this->connection
            ->table('tbl_api_key_log')
            ->insert([
                "api_info_id" => $api_usage_id,
                "api_id" => $api_id,
                "event_type" => $event_type,
                "endpoint" => $endpoint,
                "status_code" => $status_code
            ]);
    }

    public function getApiKeyLogForIdPaginated(Uuid $uuid, int $pagenumber): array
    {
        $array = new \ArrayObject();
        $offset = $pagenumber - 1;
        $collection = $this->connection
            ->table('tbl_api_key_log')
            ->where('api_info_id', $uuid->toString())
            ->orderBy("api_id", "desc")
            ->offset($offset)
            ->limit(10)
            ->get();
        foreach($collection as $apilog){
            $array->append(new ApiKeyInfoDTO(
                $apilog->api_id,
                $apilog->event_type,
                $apilog->endpoint,
                $apilog->status_code
            ));
        }
        return $array->getArrayCopy();
    }
}
