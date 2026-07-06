<?php

namespace App\Http\Repositories;

use App\Http\DataTransferObjects\ApiKeyDTO;
use ArrayObject;
use Carbon\Carbon;
use Illuminate\Database\ConnectionInterface;
use Ramsey\Uuid\Nonstandard\Uuid;

class ApiKeyRepositoryImpl implements ApiKeyRepositoryI
{
    private ConnectionInterface $connection;
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }
    public function createApiKey(string $user_id, string $name): ApiKeyDTO
    {
        $api_id = UUID::uuid4();
        $key = \Str::random(32);
        $expires_at = now()->addYear();
        $last_used_at = null;
        $created_at = now();
        $updated_at = now();
        $this->connection
            ->table("tbl_api_keys")
            ->insert([
                "api_id" => $api_id,
                "user_id" => $user_id,
                "key" => $key,
                "name" => $name,
                "active" => true,
                "last_used_at" => $last_used_at,
                "created_at" => $created_at,
                "updated_at" => $updated_at,
            ]);
        return new ApiKeyDTO($key,$name, $expires_at, $created_at);
    }
    public function getApiKeyById(string $api_id) : ApiKeyDTO
    {
        $apidata = $this->connection->table("tbl_api_keys")
            ->select()
            ->where("api_id", $api_id)
            ->get()
            ->first();
        return new ApiKeyDTO(
            $apidata->api_id,
            $apidata->name,
            Carbon::parse($apidata->expires_at),
            Carbon::parse($apidata->created_at)
        );
    }
    public function getAllApiKeysForId(string $user_id, int $pagenumber): array
    {
        $api_key_rows = new ArrayObject();
        $offset = ($pagenumber - 1);
        $collection = $this->connection
            ->table("tbl_api_keys")
            ->select("*")
            ->where("user_id", $user_id)
            ->offset($offset)
            ->limit(10)
            ->get() ?? [];
        foreach($collection as $api_key) {
            $api_key_rows->append(
                new ApiKeyDTO($api_key->api_id,$api_key->name, Carbon::parse($api_key->expires_at),Carbon::parse($api_key->created_at))
            );
        }
        return $api_key_rows->getArrayCopy();
    }
    public function getAllApiKeys(int $pagenumber): array
    {
        $offset = ($pagenumber - 1);
        $api_key_rows = new ArrayObject();
        $collection = $this->connection
            ->table("tbl_api_keys")
            ->select("*")
            ->offset($offset)
            ->limit(10)
            ->get() ?? [];
        foreach($collection as $api_key) {
            $api_key_rows->append(
                new ApiKeyDTO($api_key->api_id,$api_key->name, Carbon::parse($api_key->expires_at),Carbon::parse($api_key->created_at))
            );
        }
        return $api_key_rows->getArrayCopy();
    }
    public function updateLastUsedValue(string $api_key): void
    {
        $this->connection
            ->table("tbl_api_keys")
            ->where("api_key", $api_key)
            ->update([
                "last_used_at" => Carbon::now(),
            ]);
    }
    public function deactivateApiKey(string $api_id): void
    {
        $this->connection
            ->table("tbl_api_keys")
            ->where("api_id", $api_id)
            ->update([
                "active" => false,
            ]);
    }
    public function activateApiKey(string $api_id): void
    {
        $this->connection
            ->table("tbl_api_keys")
            ->where("api_id", $api_id)
            ->update([
                "active" => true,
            ]);
    }
}
