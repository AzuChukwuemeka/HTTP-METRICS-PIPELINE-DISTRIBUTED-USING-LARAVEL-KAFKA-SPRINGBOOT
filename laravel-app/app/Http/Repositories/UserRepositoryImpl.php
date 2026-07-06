<?php

namespace App\Http\Repositories;

use App\Http\DataTransferObjects\UserDTO;
use Carbon\Carbon;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Http\Resources\Json\JsonResource;
use phpDocumentor\Reflection\Types\Boolean;
use Ramsey\Uuid\Uuid;

class UserRepositoryImpl implements UserRepositoryI
{
    private ConnectionInterface $connection;
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function createRegularUser(string $email, string $password, array $roles): UserDTO
    {
        $user_id = Uuid::uuid4();
        $present_time = now();
        $string_roles = json_encode($roles);
        $this->connection->table("tbl_users")->insert([
            "user_id" => $user_id,
            "email" => $email,
            "password" => $password,
            "email_verified_at" => null,
            "roles" => $string_roles,
            "created_at" => $present_time,
            "updated_at" => $present_time
        ]);
        return new UserDTO($user_id->toString(), $email, $string_roles, Carbon::parse($present_time));
    }

    public function createAdminUser(string $email, string $password, array $roles): UserDTO
    {
        $user_id = Uuid::uuid4();
        $present_time = now();
        $string_roles = json_encode($roles);
        $this->connection->table("tbl_users")->insert([
            "user_id" => $user_id,
            "email" => $email,
            "password" => $password,
            "email_verified_at" => null,
            "roles" => $string_roles,
            "created_at" => $present_time,
            "updated_at" => $present_time
        ]);
        return new UserDTO($user_id->toString(), $email, $string_roles, Carbon::parse($present_time));
    }

    public function getUserById(string $uuid): UserDTO
    {
        $user = $this
            ->connection
            ->table("tbl_users")
            ->select('user_id', 'email', 'roles', 'created_at')
            ->where('user_id', $uuid)
            ->get()
            ->first();
        return new UserDTO($user->user_id, $user->email, $user->roles, Carbon::parse($user->created_at));
    }

    public function getUserByEmail(string $email): UserDTO
    {
        $user = $this
            ->connection
            ->table("tbl_users")
            ->select('user_id', 'email', 'roles', 'created_at')
            ->where('email', $email)
            ->get()
            ->first();
        return new UserDTO($user->user_id, $user->email, $user->roles, Carbon::parse($user->created_at));
    }

    public function deleteUserById(string $uuid): bool
    {
        //Throws exceptions if not able too
        $this
            ->connection
            ->table("tbl_users")
            ->where('user_id', $uuid)
            ->delete();
        return true;
    }

    public function deleteUserByEmail(string $email): bool
    {
        //Throws exceptions if not able too
        $this
            ->connection
            ->table("tbl_users")
            ->where('email', $email)
            ->delete();
        return true;
    }

    public function updateUserPassword (string $user_id, string $new_password): bool
    {
        $this
            ->connection
            ->table("tbl_users")
            ->where('$user_id', $user_id)
            ->update(['password' => $new_password]);
        return true;
    }

    public function promoteRegularUserToAdmin(string $email): bool
    {
        $this
            ->connection
            ->table("tbl_users")
            ->where('email', $email)
            ->update(['roles' => 'ADMIN']);
        return true;
    }
}
