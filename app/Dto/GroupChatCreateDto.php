<?php

namespace App\Dto;

class GroupChatCreateDto
{
    public string $name;
    private array $members=[];

    public function addMember(ChatMember $members): void{
       $this->members[]=$members;
    }

    public function getMembers(): array{
        return $this->members;
    }

}
