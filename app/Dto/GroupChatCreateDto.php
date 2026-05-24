<?php

namespace App\Dto;

class GroupChatCreateDto
{
    public string $name;
    private array $members=[];

    public function getMembers(): array{
        return $this->members;
    }
    public function addMembers(array $members): void{
        $this->members=$members;
    }

}
