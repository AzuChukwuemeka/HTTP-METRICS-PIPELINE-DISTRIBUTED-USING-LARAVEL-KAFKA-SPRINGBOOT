<?php

namespace App\Http\DataTransferObjects;

use Carbon\Carbon;
use JsonSerializable;

class UserDTO implements JsonSerializable
{
    public string $user_id;
    public string $email;
    public string $roles;
    public Carbon $created_at;

    public function __construct(
        string $user_id,
        string $email,
        string $roles,
        \DateTime $created_at
    ){
        $this->user_id = $user_id;
        $this->email = $email;
        $this->roles = $roles;
        $this->created_at = $created_at;
    }

    public function getUserId(): string
    {
        return $this->user_id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRoles(): string
    {
        return $this->roles;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->created_at;
    }

    public function jsonSerialize(): array
    {
        return [
            "user_id" => $this->user_id,
            "email" => $this->email,
            "roles" => $this->roles,
            "created_at" => $this->created_at
        ];
    }
}
