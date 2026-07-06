<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiExpiredException;
use App\Http\Repositories\ApiKeyRepositoryI;
use App\Http\Services\ApiKeyService;
use Closure;
use Illuminate\Http\Request;

class ValidateApiKey
{
    private ApiKeyService $apiKeyService;

    /**
     * @param ApiKeyService $apiKeyService
     */
    public function __construct(ApiKeyService $apiKeyService)
    {
        $this->apiKeyService = $apiKeyService;
    }

    /**
     * @throws ApiExpiredException
     */
    public function handle(Request $request, Closure $next)
    {
        $api_key = $request->header("X-API-KEY");
        if(!$api_key){
            return response()->json([
                "Error" => "API KEY MISSING",
                "Message" => "Valid Api Key needed to access this resource"
            ]);
        }
        $this->apiKeyService->checkValidityApiKey($api_key);
        return $next($request);
    }
}
