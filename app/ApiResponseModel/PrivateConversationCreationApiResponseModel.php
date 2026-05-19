<?php

namespace App\ApiResponseModel;

class PrivateConversationCreationApiResponseModel
{
    public int $conversationId;
    public int $id;
    public string $name;
    public ?string $avatar;
    public int $latestKeyVersion;

}
