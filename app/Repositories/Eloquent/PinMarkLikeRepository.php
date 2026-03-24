<?php

namespace App\Repositories\Eloquent;

use App\Models\PinMarkLike;

class PinMarkLikeRepository
{
    protected PinMarkLike $model;

    public function __construct(PinMarkLike $model)
    {
        $this->model = $model;
    }

    public function exists(int $pinMarkId, int $userId): bool
    {
        return $this->model
            ->where('pin_mark_id', $pinMarkId)
            ->where('user_id', $userId)
            ->exists();
    }

    public function createLike(int $pinMarkId, int $userId): PinMarkLike
    {
        return $this->model->create([
            'pin_mark_id' => $pinMarkId,
            'user_id' => $userId,
        ]);
    }

    public function deleteLike(int $pinMarkId, int $userId): bool
    {
        return (bool) $this->model
            ->where('pin_mark_id', $pinMarkId)
            ->where('user_id', $userId)
            ->delete();
    }
    /**
     * Get all user_ids who liked a pin
     */
    public function fetchByPinMarkId(int $pinMarkId)
    {
       
        return $this->model
            ->where('pin_mark_id', $pinMarkId)
            ->get();
    }
}
