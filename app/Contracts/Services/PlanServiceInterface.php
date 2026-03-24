<?php



namespace App\Contracts\Services;



interface PlanServiceInterface

{

    public function getAll();

    public function getById($id);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);

    // Boost Plan methods
    public function createBoost(array $data);
    public function updateBoost($id, array $data);
    public function deleteBoost($id);
    public function getAllBoosts();
    public function getBoostById($id);

    // Pin Plan methods
    public function createPin(array $data); 
    public function updatePin($id, array $data);
    public function deletePin($id);
    public function getAllPins();
    public function getPinById($id);



}