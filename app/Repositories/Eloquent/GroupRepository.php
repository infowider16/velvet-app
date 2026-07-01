<?php

namespace App\Repositories\Eloquent;
use App\Contracts\Repositories\GroupRepositoryInterface;
use App\Models\{Group,GroupMember,GroupReport};
use Illuminate\Support\Facades\Log;


class GroupRepository extends BaseRepository implements GroupRepositoryInterface

{

    // Change from protected to public
    public $model, $groupMemberModel, $groupReportModel;
    public function __construct(Group $model, GroupMember $groupMemberModel, GroupReport $groupReportModel)
    {
        $this->model = $model;
        $this->groupMemberModel = $groupMemberModel;
        $this->groupReportModel = $groupReportModel;
        parent::__construct($model);
    }

    public function addMemberToGroup($allData)
    {
        try {
           
            // Check if user exists in group
            $existing = $this->groupMemberModel
                ->where('group_id', $allData['group_id'])
                ->where('user_id', $allData['user_id'])
                ->first();

            if ($existing) {
                if ($existing->status == 2) {
                    // User had left, re-add by setting status to 0 (active)
                    $existing->status = 0;
                    if (isset($allData['role'])) $existing->role = $allData['role'];
                    if (isset($allData['is_member_permission'])) $existing->is_member_permission = $allData['is_member_permission'];
                    if (isset($allData['group_status'])) $existing->group_status = $allData['group_status'];
                    $existing->save();
                    return $existing;
                } elseif ($existing->status == 0) {
                    // Already active member, do not add again
                    return $existing;
                }
                // If status is something else, update as needed
            }

            // Otherwise, create new as usual
            return $this->groupMemberModel->create($allData);
        } catch (\Exception $e) {
            \Log::error('Error in addMemberToGroup: ' . $e->getMessage());
            return null;
        }
    }

    public function isMember($groupId, $userId)
    {
        return $this->groupMemberModel->where('group_id', $groupId)
            ->where('user_id', $userId)->where('status','!=',2)
            ->exists();
    }

    public function isAdmin($groupId, $userId)
    {
        return $this->groupMemberModel->where('group_id', $groupId)
            ->where('user_id', $userId)
            ->where('role', 'admin')
            ->exists();
    }

    public function getAdminGroupsOfUser($userId)
    {
        return $this->groupMemberModel
            ->where('user_id', $userId)
            ->where('role', 'admin')
            ->get();
    }

    public function createJoinRequest($groupId, $userId)
    {
        // You may want to use a separate table for join requests in production.
        // For simplicity, we'll use group_member with status 'pending'
        return $this->groupMemberModel->updateOrCreate(
            ['group_id' => $groupId, 'user_id' => $userId],
            ['role' => 'member', 'group_status' => 'pending']
        );
    }

    public function deleteJoinRequest($groupId, $userId)
    {
        return $this->groupMemberModel->where('group_id', $groupId)
            ->where('user_id', $userId)
            ->where('group_status', 'pending')
            ->delete();
    }


    public function delete($byWhere)
    {
        return $this->groupMemberModel->where($byWhere)->delete();
    }

    // Add this function for checking group name existence
    public function isGroupNameExists($name)
    {
        return $this->model->where('name', $name)->exists();
    }

    // Helper: Check if user is removed from group (status=2)
    public function isRemovedFromGroup($groupId, $userId)
    {
        $member = $this->groupMemberModel
            ->where('group_id', $groupId)
            ->where('user_id', $userId)
            ->first();
        return $member && $member->status == 2;
    }

    // Search groups by keyword with pagination
    public function searchGroups($keyword = '', $perPage = 15, $page = 1)
    {
        // $query = $this->model->with(['members', 'creator']);
        $query = $this->model
            ->with(['creator']) // keep only if needed
            ->withCount([
                'members' => function ($q) {
                    $q->whereNotIn('status', [1, 2])
                    ->where('is_delete', 0)
                    ->where('group_status', 'accept');
                }
            ]);
            
        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                  ->orWhere('description', 'like', '%' . $keyword . '%');
            });
        }
        
        $userId = auth()->id();
        $blockedGroupIds = $this->groupMemberModel
            ->where('user_id', $userId)
            ->where('status', 1)
            ->pluck('group_id')
            ->toArray();

        if (!empty($blockedGroupIds)) {
            $query->whereNotIn('id', $blockedGroupIds);
        }

        return $query->orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);
    }

    // Remove a member from a group
    public function removeMemberFromGroup($groupId, $userId)
    {
        try {
            return $this->groupMemberModel
                ->where('group_id', $groupId)
                ->where('user_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            \Log::error('Error in removeMemberFromGroup: ' . $e->getMessage());
            return false;
        }
    }

    // Update status (block/unblock) for a group member
    public function updateGroupMemberStatus($groupId, $userId, $status)
    { 
        // status: 0 = active, 1 = blocked, 2 = removed
        try {
            
            $data =  $this->groupMemberModel
                ->where('group_id', $groupId)
                ->where('user_id', $userId)
                ->update(['status' => $status]);
            \Log::info('Membership status updated', ['group_id' => $groupId, 'user_id' => $userId, 'status' => $status]);
            return $data;
        } catch (\Exception $e) {
            \Log::error('Error in updateGroupMemberStatus: ' . $e->getMessage());
            return false;
        }
    }

    // Add or update a member's permission in a group (default true)
    public function updateMemberPermission($groupId, $userId, $isMemberPermission = true)
    {
        try {
            return $this->groupMemberModel
                ->where('group_id', $groupId)
                ->where('user_id', $userId)
                ->update(['is_member_permission' => $isMemberPermission]);
        } catch (\Exception $e) {
            \Log::error('Error in updateMemberPermission: ' . $e->getMessage());
            return false;
        }
    }

    // Update permission for all members in a group
    public function updateAllMembersPermission($groupId, $isMemberPermission = true)
    {
        try {
            return $this->groupMemberModel
                ->where('group_id', $groupId)
                ->update(['is_member_permission' => $isMemberPermission]);
        } catch (\Exception $e) {
            \Log::error('Error in updateAllMembersPermission: ' . $e->getMessage());
            return false;
        }
    }

    public function membersDataUpdate($bywhere, $data)
    {
        try {
            return $this->groupMemberModel
                ->where($bywhere)
                ->update($data);
        } catch (\Exception $e) {
            \Log::error('Error in membersDataUpdate: ' . $e->getMessage());
            return false;
        }
    }

    // Get a member's permission in a group
    public function getMemberPermission($groupId, $userId)
    {
        $member = $this->groupMemberModel
            ->where('group_id', $groupId)
            ->where('user_id', $userId)
            ->first();
        return $member ? ($member->is_member_permission ?? true) : true;
    }

    public function getRequestedGroupsByUser($groupId)
    {
        return $this->groupMemberModel
            ->where('group_id', $groupId)
            ->where('group_status', 'pending')
            ->with('group', 'user')
            ->get();
    }

    // Add: Check if a user has already reported a group
    public function hasReportedGroup($groupId, $userId)
    {
        return $this->groupReportModel
            ->where('group_id', $groupId)
            ->where('reported_by', $userId)
            ->exists();
    }
    
    public function whereData($byWhere=[])
    {
        return $this->groupReportModel->where($byWhere);
    }

    /**
     * Get all pin reports for admin.
     *
     * @return mixed
     */
    public function getPinReport()
    {
        try {
        
            return $this->groupReportModel
            ->with(['group', 'reporter', 'pinmark'])
            ->latest();

        } catch (\Exception $e) {
            Log::error('Get pin report failed: ' . $e->getMessage());
            throw $e;
        }
    }

    // Add: Store a group report
    public function reportGroup($groupId, $userId, $reason)
    {
        return $this->groupReportModel->create([
            'group_id' => $groupId,
            'reported_by' => $userId,
            'reason' => $reason,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function fetchAll($filters = [], $select = ['*'], $returnType = 'get')
    {
        $query = $this->groupMemberModel->select($select);

        foreach ($filters as $column => $condition) {

            // OR WHERE block
            if ($column === 'or') {
                $query->where(function ($q) use ($condition) {
                    foreach ($condition as $or) {
                        foreach ($or as $col => $val) {
                            $q->orWhere($col, $val);
                        }
                    }
                });
                continue;
            }

            // Condition is an ARRAY → complex rules
            if (is_array($condition)) {

                // raw operator → ['age' => ['>', 25]]
                if (isset($condition[0]) && $this->isOperator($condition[0])) {
                    $query->where($column, $condition[0], $condition[1]);
                }

                // whereIn → ['id' => ['in' => [1,2,3]]]
                elseif (isset($condition['in'])) {
                    $query->whereIn($column, $condition['in']);
                }

                // whereNotIn
                elseif (isset($condition['not_in'])) {
                    $query->whereNotIn($column, $condition['not_in']);
                }

                // whereBetween
                elseif (isset($condition['between'])) {
                    $query->whereBetween($column, $condition['between']);
                }

                // LIKE
                elseif (isset($condition['like'])) {
                    $query->where($column, 'LIKE', $condition['like']);
                }

                continue;
            }

            // WHERE NULL
            if ($condition === 'null') {
                $query->whereNull($column);
                continue;
            }

            // WHERE NOT NULL
            if ($condition === 'not_null') {
                $query->whereNotNull($column);
                continue;
            }

            // Default WHERE =
            $query->where($column, $condition);
        }

        // return type handler
        return match ($returnType) {
            'first' => $query->first(),
            'count' => $query->count(),
            default => $query->get(),
        };
    }


    /**
     * Helper to check valid SQL operator
     */
    private function isOperator($value)
    {
        return in_array($value, ['=', '!=', '>', '<', '>=', '<=']);
    }


    public function deleteData($conditions)
    {
        $query = $this->groupMemberModel;

        // Apply simple where conditions
        if (!empty($conditions['where'])) {
            foreach ($conditions['where'] as $col => $value) {
                $query = $query->where($col, $value);
            }
        }

        // Apply whereIn conditions
        if (!empty($conditions['whereIn'])) {
            foreach ($conditions['whereIn'] as $col => $values) {
                $query = $query->whereIn($col, $values);
            }
        }

        return $query->delete();
    }

//reports new function added
/**
* Get all user reports for admin.
*
* @return mixed
*/
public function getUserReports($request = null)
{
    try {

        /*
        * Start query
        */
        $query = $this->groupReportModel
            ->with([
                'reportedUser',
                'reporter'
            ])
            ->whereNotNull('user_id');

        /*
        * Filter by status
        */
        if (!empty($request->status)) {

            $query->where(
                'status',
                $request->status
            );
        }

        /*
        * Filter by reason
        */
        if (!empty($request->reason)) {

            $query->where(
                'reason',
                'LIKE',
                '%' . $request->reason . '%'
            );
        }

        /*
        * Filter by reporter
        */
        if (!empty($request->reporter)) {

            $query->whereHas(
                'reporter',
                function ($q) use ($request) {

                    $q->where(
                        'name',
                        'LIKE',
                        '%' . $request->reporter . '%'
                    )
                    ->orWhere(
                        'email',
                        'LIKE',
                        '%' . $request->reporter . '%'
                    )
                    ->orWhere(
                        'phone_number',
                        'LIKE',
                        '%' . $request->reporter . '%'
                    );
                }
            );
        }

        /*
        * Filter by reported user
        */
        if (!empty($request->user)) {

            $query->whereHas(
                'reportedUser',
                function ($q) use ($request) {

                    $q->where(
                        'name',
                        'LIKE',
                        '%' . $request->user . '%'
                    )
                    ->orWhere(
                        'id',
                        $request->user
                    );
                }
            );
        }

        /*
        * Filter by date
        */
        if (!empty($request->date)) {

            $query->whereDate(
                'created_at',
                $request->date
            );
        }

        /*
        * Return latest reports
        */
        return $query->latest();

    } catch (\Exception $e) {

        /*
        * Log repository error
        */
        Log::error(
            'Get user reports failed: '
            . $e->getMessage()
        );

        throw $e;
    }
}

/**
* Get all group reports for admin.
*
* @return mixed
*/
/**
* Get all group reports for admin.
*
* @param object|null $request
* @return mixed
*/
public function getGroupReports($request = null)
{
    try {

        /*
        * Start query
        */
        $query = $this->groupReportModel
            ->with([
                'group',
                'reporter'
            ])
            ->whereNotNull('group_id');

        /*
        * Filter by status
        */
        if (!empty($request?->status)) {

            $query->where(
                'status',
                $request->status
            );
        }

        /*
        * Filter by reason
        */
        if (!empty($request?->reason)) {

            $query->where(
                'reason',
                'LIKE',
                '%' . $request->reason . '%'
            );
        }

        /*
        * Filter by reporter
        */
        if (!empty($request?->reporter)) {

            $query->whereHas(
                'reporter',
                function ($q) use ($request) {

                    $q->where(
                            'name',
                            'LIKE',
                            '%' . $request->reporter . '%'
                        )
                        ->orWhere(
                            'email',
                            'LIKE',
                            '%' . $request->reporter . '%'
                        )
                        ->orWhere(
                            'phone_number',
                            'LIKE',
                            '%' . $request->reporter . '%'
                        );
                }
            );
        }

        /*
        * Filter by group name or id
        */
        if (!empty($request?->group)) {

            $query->whereHas(
                'group',
                function ($q) use ($request) {

                    $q->where(
                            'name',
                            'LIKE',
                            '%' . $request->group . '%'
                        )
                        ->orWhere(
                            'id',
                            $request->group
                        );
                }
            );
        }

        /*
        * Filter by group owner
        */
        if (!empty($request?->owner)) {

            $query->whereHas(
                'group.creator',
                function ($q) use ($request) {

                    $q->where(
                        'name',
                        'LIKE',
                        '%' . $request->owner . '%'
                    );
                }
            );
        }

        /*
        * Filter by date
        */
        if (!empty($request?->date)) {

            $query->whereDate(
                'created_at',
                $request->date
            );
        }

        /*
        * Return latest reports
        */
        return $query->latest();

    } catch (\Exception $e) {

        /*
        * Log repository error
        */
        Log::error(
            'Get group reports failed: '
            . $e->getMessage()
        );

        throw $e;
    }
}

/**
* Get all pin reports for admin.
*
* @return mixed
*/
public function getPinReports($request = null)
{
    try {

        /*
        * Start query
        */
        $query = $this->groupReportModel
            ->with([
                'pinmark.user',
                'reporter'
            ])
            ->whereNotNull('pin');

        /*
        * Filter by status
        */
        if (!empty($request->status)) {

            $query->where(
                'status',
                $request->status
            );
        }

        /*
        * Filter by report reason
        */
        if (!empty($request->reason)) {

            $query->where(
                'reason',
                'LIKE',
                '%' . $request->reason . '%'
            );
        }

        /*
        * Filter by reporter
        */
        if (!empty($request->reporter)) {

            $query->whereHas(
                'reporter',
                function ($q) use ($request) {

                    $q->where(
                        'name',
                        'LIKE',
                        '%' . $request->reporter . '%'
                    )
                    ->orWhere(
                        'email',
                        'LIKE',
                        '%' . $request->reporter . '%'
                    )
                    ->orWhere(
                        'phone_number',
                        'LIKE',
                        '%' . $request->reporter . '%'
                    );
                }
            );
        }

        /*
        * Filter by pin
        */
        if (!empty($request->pin)) {

            $query->whereHas(
                'pinmark',
                function ($q) use ($request) {

                    $q->where(
                        'id',
                        $request->pin
                    )
                    ->orWhere(
                        'pin_message',
                        'LIKE',
                        '%' . $request->pin . '%'
                    );
                }
            );
        }

        /*
        * Filter by single date
        */
        if (!empty($request->date)) {

            $query->whereDate(
                'created_at',
                $request->date
            );
        }

        /*
        * Return latest reports
        */
        return $query->latest();

    } catch (\Exception $e) {

        /*
        * Log repository error
        */
        Log::error(
            'Get pin reports failed: '
            . $e->getMessage()
        );

        throw $e;
    }
}

public function updateData($byWhere, $data)
{
    try {
        return $this->groupReportModel
            ->where($byWhere)
            ->update($data);
    } catch (\Exception $e) {
        Log::error('Update report data failed: ' . $e->getMessage());
        return false;
    }

}

public function deleteReport($byWhere)
{
    try {
        return $this->groupReportModel
            ->where($byWhere)
            ->delete();
    } catch (\Exception $e) {
        Log::error('Delete report failed: ' . $e->getMessage());
        return false;
    }


}

}