<?php

namespace App\Http\Controllers;

use App\Exceptions\UserLoginException;
use App\Http\DataTransferObjects\UserDTO;
use App\Http\Services\UserService;
use App\Models\User;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordAlias;
use Mockery\Exception;
use phpDocumentor\Reflection\Types\Boolean;
use Ramsey\Uuid\Uuid;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use function PHPUnit\Framework\isEmpty;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Users", description="User registration, authentication and administration")
 */
class UserController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @OA\Post(
     *     path="/users/createAdminUser",
     *     tags={"Users"},
     *     summary="Create an admin user (admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"email","password"},
     *         @OA\Property(property="email", type="string", format="email"),
     *         @OA\Property(property="password", type="string", format="password")
     *     )),
     *     @OA\Response(response=200, description="Admin user created")
     * )
     */
    public function createAdminUser(Request $request) : JsonResponse{
        $validated = $request->validate([
            "email" => "required |email",
            "password" => [
                'required',
                'string',
            ]
        ]);
        $userDTO = $this->userService->createRegularUser($validated["email"], $validated["password"]);
        return response()->json([
            "data" => $userDTO
        ]);
    }
    /**
     * @OA\Post(
     *     path="/users/createUser",
     *     tags={"Users"},
     *     summary="Register a new regular user",
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"email","password"},
     *         @OA\Property(property="email", type="string", format="email"),
     *         @OA\Property(property="password", type="string", format="password")
     *     )),
     *     @OA\Response(response=200, description="User created")
     * )
     */
    public function createRegularUser(Request $request) : JsonResponse
    {
        $validated = $request->validate([
            "email" => "required |email",
            "password" => [
                'required',
                'string',
            ]
        ]);
        $userDTO = $this->userService->createRegularUser($validated["email"], $validated["password"]);
        return response()->json([
            "data" => $userDTO
        ]);
     }
//
    /**
     * @OA\Post(
     *     path="/users/login",
     *     tags={"Users"},
     *     summary="Log in and receive a JWT bearer token",
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"email","password"},
     *         @OA\Property(property="email", type="string", format="email"),
     *         @OA\Property(property="password", type="string", format="password")
     *     )),
     *     @OA\Response(response=200, description="Bearer token issued",
     *         @OA\JsonContent(
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="expires_in", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     *
     * @throws UserLoginException
     */
    public function userLogin(Request $request) : JsonResponse {
        $credentials = $request->validate([
            "email" => "required |email",
            "password" => [
                'required',
                'string',
            ]
        ]);
        $token = $this->userService->userLogin($credentials["email"], $credentials["password"]);
        return response()->json([
            "token_type" => "bearer",
            "access_token" => $token,
            "expires_in" => JWTAuth::factory()->getTTL() . " Minutes"
        ]);
    }
//    /**
//     */
    /**
     * @OA\Post(
     *     path="/users/getUserByEmail/{email}",
     *     tags={"Users"},
     *     summary="Get a user by email (admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="email", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="User found")
     * )
     */
    public function getUserByEmail($email) : JsonResponse{
        $userDTO = $this->userService->getUserByEmail($email);
        return response()->json([
            "data" => $userDTO
        ]);
    }
    /**
     * @OA\Post(
     *     path="/users/getUserById",
     *     tags={"Users"},
     *     summary="Get the authenticated user's profile",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="User found")
     * )
     */
    public function getUserById() : JsonResponse{
        $user_id = auth()->user()->user_id;
        $userDTO = $this->userService->getUserById($user_id);
        return response()->json([
            "data" => $userDTO
        ]);
    }
    /**
     * @OA\Post(
     *     path="/users/deleteUserById",
     *     tags={"Users"},
     *     summary="Delete the authenticated user's own account",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Deletion status")
     * )
     */
    public function deleteUserById() : JsonResponse{
        $user_id = auth()->user()->user_id;
        $operation_success_status = $this->userService->deleteUserById($user_id);
        return response()->json([
            "success_status" => $operation_success_status
        ]);
    }
    /**
     * @OA\Post(
     *     path="/users/deleteUserByEmail/{email}",
     *     tags={"Users"},
     *     summary="Delete a user by email (admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="email", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Deletion status")
     * )
     */
    public function deleteUserByEmail($email) : JsonResponse{
        $operation_success_status = $this->userService->deleteUserByEmail($email);
        return response()->json([
            "success_status" => $operation_success_status
        ]);
    }
//
//    /**
//     * @throws JWTException
//     */
    /**
     * @OA\Post(
     *     path="/users/updatePassword",
     *     tags={"Users"},
     *     summary="Update the authenticated user's password",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="new_password", type="string", format="password")
     *     )),
     *     @OA\Response(response=200, description="Update status")
     * )
     */
    public function updateUserPassword(Request $request) : JsonResponse{
        $user_id = auth()->user()->user_id;
        $new_password = $request->input("new_password");
        $operation_success_status = $this->userService->updateUserPassword($user_id, $new_password);
        return response()->json([
            "success_status" => $operation_success_status
        ]);
    }
    /**
     * @OA\Post(
     *     path="/users/promoteRegularUserToAdmin/{email}",
     *     tags={"Users"},
     *     summary="Promote a regular user to admin (admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="email", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Promotion status")
     * )
     */
    public function promoteRegularUserToAdmin(string $email) : JsonResponse{
        $operation_success_status = $this->userService->promoteRegularUserToAdmin($email);
        return response()->json([
            "success_status" => $operation_success_status
        ]);
    }
    /**
     * @throws UserLoginException
     */
    public function checkOldPasswordMatches(Request $request){
        $email = auth()->user()->email;
        return $this->userService->checkOldPasswordMatches($email, $request->input("old_password"));
    }
}
