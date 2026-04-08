<?php

namespace App\Repositories\Eloquent;

use App\Models\Friendship;
use App\Contracts\Repositories\FriendshipRepositoryInterface;
use App\Models\Block;

class FriendshipRepository extends BaseRepository implements FriendshipRepositoryInterface
{
    protected $model, $blockmodel;

    public function __construct(Friendship $model, Block $blockmodel)
    {
        $this->model = $model;
        $this->blockmodel = $blockmodel;
        parent::__construct($model);
    }

    public function isBlocked($blockerId, $blockedId)
    {
        try {
           
            return $this->blockmodel
            ->where(function ($q) use ($blockerId, $blockedId) {
                $q->where('blocker_id', $blockerId)
                ->where('blocked_id', $blockedId);
            })
            ->orWhere(function ($q) use ($blockerId, $blockedId) {
                $q->where('blocker_id', $blockedId)
                ->where('blocked_id', $blockerId);
            })
            ->first();
            
        } catch (\Exception $e) {
            $this->logError(__FUNCTION__, $e);
            return null;
        }
    }


    public function createBlock(array $data)
    {
        try {
            return $this->blockmodel->create($data);
        } catch (\Exception $e) {
            $this->logError(__FUNCTION__, $e);
            return null;
        }
    }

    public function getBlockedUsersList($userId, $perPage, $page)
    {
        try {
            return $this->blockmodel->where('blocker_id', $userId)
                ->with(['blocked' => function($query) {
                    $query->select('id', 'name', 'images', 'date_of_birth', 'gender', 'location', 'lat', 'lng','is_delete');
                }])
                ->orderBy('id', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);
        } catch (\Exception $e) {
            $this->logError(__FUNCTION__, $e);
            return null;
        }
    }

    public function findBlockByUsers($blockerId, $blockedId)
    {
        try {
            return $this->blockmodel->where('blocker_id', $blockerId)
                ->where('blocked_id', $blockedId)
                ->first();
        } catch (\Exception $e) {
            $this->logError(__FUNCTION__, $e);
            return null;
        }
    }

    public function deleteBlock($blockId)
    {
        try {
            $block = $this->blockmodel->find($blockId);
            return $block ? $block->delete() : false;
        } catch (\Exception $e) {
            $this->logError(__FUNCTION__, $e);
            return false;
        }
    }
    public function friendDelete($byWhere)
    {
        try {
            return $this->model->where($byWhere)->delete();
        } catch (\Exception $e) {
            $this->logError(__FUNCTION__, $e);
            return false;
        }
    }


    public function updateBlock($byWhere, $update)
    {
        try{
            return $this->blockmodel->where($byWhere)->update($update);

        }catch(\Exception $e){
               $this->logError(__FUNCTION__, $e);
            return false;
        }
    }
}