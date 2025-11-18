<?php

namespace App\Service;

use App\Exception\UserNotFoundException;
use App\Interface\UserRepoInterface;
use App\Models\User;


class UserService{

    protected UserRepoInterface $userRepo;
    public function __construct(UserRepoInterface $userRepo){
     $this->userRepo=$userRepo;
    }

    public function createUser(array $data){
     if($this->userRepo->emailExists($data['email'])){
         throw new \Exception('Email already exists');
     }

     $user=$this->userRepo->createUsers($data);
   if($user==null)
       throw new \Exception('Error creating user');
    $user->sendEmailVerificationNotification();
   return $user;
    }

    public function updateProfile(array $data): User
    {
        $user = auth()->user();
        if (!$user) throw new UserNotFoundException('You are not logged in');
        // Handle avatar file
        if (!empty($data['avatar'])) {
            $file = $data['avatar'];
            $extension = $file->getClientOriginalExtension();

            if (!in_array(strtolower($extension), ['jpeg','jpg','png','gif','svg','webp'])) {
                throw new \Exception('Invalid file type');
            }

            $filename = uniqid() . '_' . time() . '.' . $extension;

            // Save new avatar
            $file->move(public_path('images/avatars'), $filename);

            // Delete old avatar if exists
            if ($user->avatar && file_exists(public_path('images/avatars/' . $user->avatar))) {
                @unlink(public_path('images/avatars/' . $user->avatar));
            }

            $data['avatar'] = $filename;
        }

        // Call repository to update DB
        /** @var TYPE_NAME $user */
        return $this->userRepo->updateUser($user, $data);
    }


}
