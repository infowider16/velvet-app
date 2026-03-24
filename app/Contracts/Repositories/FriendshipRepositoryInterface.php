<?php

namespace App\Contracts\Repositories;

interface FriendshipRepositoryInterface
{
  public function isBlocked($blockerId, $blockedId);
    public function getBlockedUsersList($userId, $perPage, $page);
}
