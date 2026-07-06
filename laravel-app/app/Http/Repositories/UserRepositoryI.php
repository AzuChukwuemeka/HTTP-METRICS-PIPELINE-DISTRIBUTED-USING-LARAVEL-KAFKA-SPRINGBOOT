<?php

namespace App\Http\Repositories;

use App\Http\DataTransferObjects\UserDTO;
use phpDocumentor\Reflection\Types\Boolean;
use Ramsey\Uuid\Uuid;

interface UserRepositoryI
{
    public function createRegularUser(string $email, string $password, array $roles) : UserDTO;
    public function createAdminUser(string $email, string $password, array $roles) : UserDTO;
    public function getUserById(string $uuid) : UserDTO;
    public function getUserByEmail(string $email) : UserDTO;
    public function deleteUserById(string $uuid) : bool;
    public function deleteUserByEmail(string $email) : bool;
    public function updateUserPassword(string $user_id,string $new_password) : bool;
    public function promoteRegularUserToAdmin(string $email) : bool;
}
