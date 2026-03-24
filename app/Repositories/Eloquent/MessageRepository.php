<?php



namespace App\Repositories\Eloquent;



use App\Contracts\Repositories\MessageRepositoryInterface;

use App\Models\Message;
use App\Models\MutedFriend;



class MessageRepository extends BaseRepository implements MessageRepositoryInterface

{

    public $model;
    public $mutedFriend;



    public function __construct(Message $model,MutedFriend $mutedFriend)

    {

        $this->model = $model;
        $this->mutedFriend = $mutedFriend;

        parent::__construct($model);

    }

    

    /**
     * Mark all messages as read between sender and receiver where read_at is null.
     */

    public function markMessagesAsRead($senderId, $receiverId)

    {

        return $this->model

            ->where('sender_id', $senderId)

            ->where('receiver_id', $receiverId)

            ->whereNull('read_at')

            ->update(['read_at' => now()]);

    }

    /**
     * Get notification status for 1-to-1 chat between two users.
     * Returns true (on) by default if not set.
     */
    public function getNotificationStatus($userId, $otherUserId)
    {
        return  $this->mutedFriend->checkOrCreate($userId, $otherUserId);
    }

    /**
     * Set notification status for 1-to-1 chat (stores on latest message).
     */
    public function setNotificationStatus(int $userId, int $otherUserId, int $status): bool
    {
        $this->mutedFriend->updateOrCreate([
            'user_id'   => $userId,
            'friend_id' => $otherUserId,
        ],['user_id'   => $userId,
            'friend_id' => $otherUserId,
            'status'=>$status
        ]);
    
        return true;
    }



    public function deleteChat($userA, $userB)
    {
        return $this->model->where(function ($q) use ($userA, $userB) {
            $q->where('sender_id', $userA)
            ->where('receiver_id', $userB);
        })
        ->orWhere(function ($q) use ($userA, $userB) {
            $q->where('sender_id', $userB)
            ->where('receiver_id', $userA);
        })
        ->delete();
    }


}