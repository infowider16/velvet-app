<?php

namespace App\Contracts\Repositories;
interface MessageRepositoryInterface
{
    // Define any additional methods specific to the Pin repository

    public function markMessagesAsRead($senderId, $receiverId);
}