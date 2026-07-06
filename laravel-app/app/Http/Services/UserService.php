<?php

namespace App\Http\Services;

use App\Exceptions\UserLoginException;
use App\Http\DataTransferObjects\UserDTO;
use App\Http\Repositories\UserRepositoryI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use phpDocumentor\Reflection\Types\Boolean;
use Ramsey\Uuid\Uuid;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserService
{
    private UserRepositoryI $userRepository;

    public function __construct(UserRepositoryI $userRepositoryI)
    {
        $this->userRepository = $userRepositoryI;
    }
    public function createAdminUser(string $email, string $password) : UserDTO{
        $password = Hash::make("password");
        return $this->userRepository->createAdminUser($email, $password, ["USER", "ADMIN"]);
    }
    public function createRegularUser(string $email, string $password) : UserDTO{
        $password = Hash::make($password);
        return $this->userRepository->createRegularUser($email, $password, ["USER"]);
    }

    /**
     * @throws UserLoginException
     */
    public function userLogin(string $email, string $password) : string {
      if(!$token = auth()->attempt(['email' => $email, 'password' => $password])) {
          throw new UserLoginException();
      }
      return $token;
    }
    public function getUserByEmail(string $email) : UserDTO{
        return $this->userRepository->getUserByEmail($email);
    }
    public function getUserById(string $uuid) : UserDTO{
        return $this->userRepository->getUserById($uuid);
    }
    public function deleteUserById(string $uuid) : bool{
        return $this->userRepository->deleteUserById($uuid);
    }
    public function deleteUserByEmail(string $email) : bool{
        return $this->userRepository->deleteUserByEmail($email);
    }
    public function updateUserPassword(string $user_id, string $new_password) : Boolean{
        return $this->userRepository->updateUserPassword($user_id,Hash::make($new_password));
    }
    public function promoteRegularUserToAdmin(string $email) : bool{
        return $this->userRepository->promoteRegularUserToAdmin($email);
    }

    /**
     * @throws UserLoginException
     */
    public function checkOldPasswordMatches(string $email, string $password) : bool{
        if(! $token = JWTAuth::attempt(['email' => $email, 'password' => $password])) {
            throw new UserLoginException();
        }
        return true;
    }
}
