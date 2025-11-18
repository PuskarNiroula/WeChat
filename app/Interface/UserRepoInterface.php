<?php

namespace App\Interface;
use App\Models\User;

interface UserRepoInterface
{
    public function createUsers(array $userDto);
    public function getUserById(int $id);
    public function updateUser(User $user, array $data);
    public function deleteUser(int $id);
    public function emailExists(string $email):bool;

    public function searchUser(string $name);
}
