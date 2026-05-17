<?php

namespace App\Dto;

class ChatMember
{
    private string $userId;
    private string $encryptedKey;
    private bool $isAdmin=false;

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }
    public function getUserId(): string
    {
        return $this->userId;
    }
    public function setEncryptedKey(string $encryptedKey): void
    {
        $this->encryptedKey = $encryptedKey;
    }
    public function getEncryptedKey(): string
    {
        return $this->encryptedKey;
    }
    public function setAdmin(): void
    {
        $this->isAdmin = true;
    }
    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

}
