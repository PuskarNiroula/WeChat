<?php

namespace App\ApiResponseModel;

class GroupChatCreationApiResponseModel
{

    public int $conversationId;
    public string $name;
    public ?string $avatar;
    public string $type="group";
    public int $latestKeyVersion;
}
