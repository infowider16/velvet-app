<?php



namespace App\Contracts\Services;



use Illuminate\Http\Request;



interface InterestServiceInterface

{

    public function createInterest(Request $request);

    public function updateInterest(Request $request, $id);

    public function deleteInterest($id);

    public function getInterestDetail($id);

    public function getInterestList();

    

    // Sub Interest methods

   public function createSubInterest(array $payload);
   
    public function updateSubInterest(array $payload, $id);

    public function deleteSubInterest($id);

    public function getSubInterestDetail($id);

    public function getSubInterestList();

    public function getParentInterests();

}

