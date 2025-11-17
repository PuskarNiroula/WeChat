<?php

namespace App\Repository;
use App\Interface\UserRepoInterface;
use App\Models\User;


class UserRepository implements UserRepoInterface{

    public function createUsers(array $data)
    {
        return User::create($data);
    }

    public function getUserById(int $id)
    {
      User::findOrFail($id);
    }

    public function updateUser(User $user, array $data)
    {
        if (!empty($data['avatar'])) {
            $user->avatar = $data['avatar'];
        }

        $user->name = $data['name'];
        $user->save();

        return $user;
    }

    public function deleteUser(int $id)
    {
        User::destroy($id);
    }

    public function emailExists(string $email): bool
    {
        return User::where('email', $email)->exists();
    }
}
