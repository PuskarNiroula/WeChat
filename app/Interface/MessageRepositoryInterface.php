<?php
namespace App\Interface;

interface MessageRepositoryInterface
{
    public function createMessage(array $messageDto);

}
