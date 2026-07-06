<?php

namespace App\Http\Controllers;

use App\Http\DataTransferObjects\ApiKeyDTO;
use App\Http\Services\ApiKeyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use Ramsey\Uuid\Nonstandard\Uuid;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Tag(name="API Keys", description="Issue and manage API keys used to authenticate event submissions")
 */
class ApiKeyController extends Controller
{
    private ApiKeyService $apiKeyService;

    public function __construct(ApiKeyService $apiKeyService)
    {
        $this->apiKeyService = $apiKeyService;
    }

    /**
     * @OA\Post(
     *     path="/apikeys/createKey/{name}",
     *     tags={"API Keys"},
     *     summary="Create a new API key for the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="name", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="API key created")
     * )
     */
    public function createApiKey($name): JsonResponse
    {
        $user_id = auth()->user()->user_id;
        $apiKeyDTO = $this->apiKeyService->createApiKey($user_id, $name);
        return response()->json([
            "apiKey" => $apiKeyDTO,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/apikeys/getApiDetails/{api_id}",
     *     tags={"API Keys"},
     *     summary="Get details for a specific API key",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="api_id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="API key details")
     * )
     */
    public function getApiKeyById(string $api_id) : JsonResponse{
        $apiKeyDTO = $this->apiKeyService->getApiKeyById($api_id);
        return response()->json([
            "data" => $apiKeyDTO,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/apikeys/getAllKeys",
     *     tags={"API Keys"},
     *     summary="List all API keys (admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="pagenumber", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Response(response=200, description="Paginated list of API keys")
     * )
     */
    public function getAllApiKeys() : JsonResponse{
        $pagenumber = request()->query("pagenumber") ?? 1;
        $allApiKeys = $this->apiKeyService->getAllApiKeys($pagenumber);
        return response()->json([
            "data" => $allApiKeys,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/apikeys/getAllKeysForId/{user_id}",
     *     tags={"API Keys"},
     *     summary="List all API keys belonging to a user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="user_id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="pagenumber", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Response(response=200, description="Paginated list of API keys")
     * )
     */
    public function getAllApiKeysForId(string $user_id) : JsonResponse{
        $pagenumber = request()->query("pagenumber") ?? 1;
        $allApiKeys = $this->apiKeyService->getAllApiKeysForId($user_id, $pagenumber);
        return response()->json([
            "data" => $allApiKeys,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/apikeys/activateKey/{api_id}",
     *     tags={"API Keys"},
     *     summary="Activate an API key (admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="api_id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Key activated")
     * )
     */
    public function activateApiKey(string $api_id): void
    {
        $this->apiKeyService->activateApiKey($api_id);
    }

    /**
     * @OA\Post(
     *     path="/apikeys/deactivateKey/{api_id}",
     *     tags={"API Keys"},
     *     summary="Deactivate an API key (admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="api_id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Key deactivated")
     * )
     */
    public function deactivateApiKey(string $api_id): void
    {
        $this->apiKeyService->deactivateApiKey($api_id);
    }
}
