<?php

namespace App\Repository;
use App\Exception\UserNotFoundException;
use App\Interface\UserRepoInterface;
use App\Models\User;


class UserRepository implements UserRepoInterface{

    public function createUsers(array $userDto)
    {
        return User::create($userDto);
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

    public function searchUser(string $name)
    {
        $users = User::where('name', 'like', '%' . $name . '%')
            ->limit(10)
            ->get(['id', 'name']);

        $result = [];

        foreach ($users as $user) {
            $result[] = [
                'id' => $user->id,
                'name' => $user->name,
            ];
        }
        return $result;
    }


}
