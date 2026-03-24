<?php



namespace App\Contracts\Repositories;



interface UserRepositoryInterface

{

    public function all($columns = ['*'], $orderBY = ['id' => 'desc']);

    public function getOneData(array $byWhere, array $withRelations = []);

    public function create($allData);

    public function update($byWhere, $update);

    public function deleteData(array $modelData);

    public function clearAllCache();

    public function find($id, $columns = ['*']);

    public function findOrFail($id, $columns = ['*']);

    public function firstOrCreate(array $attributes, array $values = []);

    public function updateOrCreate($byWhere, $allData);

    public function pluck($column, $key = null);

    public function paginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null);

    public function getByWhere(

        $byWhere = [],

        $orderBy = ['id' => 'desc'],

        $columns = ['*'],

        $relations = [],

        $relationFilters = [],

        $method = 'get'

    );


    

    public function createMobileNotification($sender_user_id, $receiver_user_id, $title, $body, $other = []);

    public function getMobileNotification($byWhere);

    public function getUnreadNotificationCount($user_id);

    public function readNotifications($byWhere, $updateData);

    public function getNotificationsWithPagination($byWhere, $perPage, $page);

    public function markNotificationsAsRead($user_id);

}

