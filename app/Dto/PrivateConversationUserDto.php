<?php

namespace App\Dto;

class PrivateConversationUserDto
{
    public int $userId;
    public string $encryptedKey;
    public ?int $ConversationId;

}
