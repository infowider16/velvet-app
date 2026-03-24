<?php



namespace App\Services\Admin;

use App\Contracts\Services\InterestServiceInterface;
use App\Services\BaseService;
use Illuminate\Http\Request;
use App\Contracts\Repositories\InterestRepositoryInterface;




class InterestService extends BaseService implements InterestServiceInterface

{

    protected InterestRepositoryInterface $interestRepository;



    public function __construct(InterestRepositoryInterface $interestRepository)

    {

        $this->interestRepository = $interestRepository;
    }






    public function createInterest($request)

    {

        try {

            return $this->handleServiceCall(function () use ($request) {

                $data = [

                    'parent_id' => $request->input('parent_id', 0),
                    'name' => $request->input('name_translation.en'),
                    'name_translation' => [
                        'en' => $request->input('name_translation.en'),
                        'ge' => $request->input('name_translation.ge'),
                    ],

                ];
                $interest = $this->interestRepository->create($data);
                return $interest

                    ? ['status' => true, 'message' => 'Interest created successfully']

                    : ['status' => false, 'message' => 'Failed to create Interest'];
            });
        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return ['status' => false, 'message' => 'Something went wrong while creating Interest'];
        }
    }



    public function updateInterest($request, $id)

    {

        try {

            return $this->handleServiceCall(function () use ($request, $id) {

                $interest = $this->interestRepository->find($id);

                if (!$interest) {

                    return ['status' => false, 'message' => 'Interest not found'];
                }



                $data = [

                    'parent_id' => $request->input('parent_id', 0),
                    'name' => $request->input('name_translation.en'),
                    'name_translation' => [
                        'en' => $request->input('name_translation.en'),
                        'ge' => $request->input('name_translation.ge'),
                    ],

                ];



                $updated = $this->interestRepository->update(['id' => $id], $data);



                return $updated

                    ? ['status' => true, 'message' => 'Interest updated successfully']

                    : ['status' => false, 'message' => 'Failed to update Interest'];
            });
        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return ['status' => false, 'message' => 'Something went wrong while updating Interest'];
        }
    }



    public function deleteInterest($id)

    {

        try {

            return $this->handleServiceCall(function () use ($id) {

                $interest = $this->interestRepository->find($id);

                if (!$interest) {

                    return ['status' => false, 'message' => 'Interest not found'];
                }



                $deleted = $this->interestRepository->deleteData(['id' => $id]);



                return $deleted

                    ? ['status' => true, 'message' => 'Interest deleted successfully']

                    : ['status' => false, 'message' => 'Failed to delete Interest'];
            });
        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return ['status' => false, 'message' => 'Something went wrong while deleting Interest'];
        }
    }



    public function getInterestDetail($id)

    {

        try {

            return $this->handleServiceCall(function () use ($id) {

                return $this->interestRepository->find($id);
            }, null); // Return null on error for this method

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return null;
        }
    }

    public function getInterestList()

    {

        try {

            $interests = $this->interestRepository->getParentInterests();

            return $interests ?? collect();
        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return collect();
        }
    }

    // Sub Interest methods
    public function createSubInterest(array $payload)
    {
        try {
            return $this->handleServiceCall(function () use ($payload) {
                $subInterest = $this->interestRepository->create($payload);

                return $subInterest
                    ? ['status' => true, 'message' => 'Sub Interest created successfully']
                    : ['status' => false, 'message' => 'Failed to create Sub Interest'];
            });
        } catch (\Exception $e) {
            $this->logError(__FUNCTION__, $e);
            return ['status' => false, 'message' => 'Something went wrong while creating Sub Interest'];
        }
    }

    public function updateSubInterest(array $payload, $id)
    {
        try {
            return $this->handleServiceCall(function () use ($payload, $id) {
                $subInterest = $this->interestRepository->find($id);

                if (!$subInterest) {
                    return ['status' => false, 'message' => 'Sub Interest not found'];
                }

                $updated = $this->interestRepository->update(['id' => $id], $payload);

                return $updated
                    ? ['status' => true, 'message' => 'Sub Interest updated successfully']
                    : ['status' => false, 'message' => 'Failed to update Sub Interest'];
            });
        } catch (\Exception $e) {
            $this->logError(__FUNCTION__, $e);
            return ['status' => false, 'message' => 'Something went wrong while updating Sub Interest'];
        }
    }

    public function deleteSubInterest($id)
    {
        try {
            return $this->handleServiceCall(function () use ($id) {
                $subInterest = $this->interestRepository->find($id);
                if (!$subInterest) {
                    return ['status' => false, 'message' => 'Sub Interest not found'];
                }

                $deleted = $this->interestRepository->deleteData(['id' => $id]);

                return $deleted
                    ? ['status' => true, 'message' => 'Sub Interest deleted successfully']
                    : ['status' => false, 'message' => 'Failed to delete Sub Interest'];
            });
        } catch (\Exception $e) {
            $this->logError(__FUNCTION__, $e);
            return ['status' => false, 'message' => 'Something went wrong while deleting Sub Interest'];
        }
    }

    public function getSubInterestDetail($id)
    {
        try {
            return $this->handleServiceCall(function () use ($id) {
                return $this->interestRepository->find($id);
            }, null);
        } catch (\Exception $e) {
            $this->logError(__FUNCTION__, $e);
            return null;
        }
    }

    public function getSubInterestList()
    {
        try {
            $subInterests = $this->interestRepository->getSubInterests();
            return $subInterests ?? collect();
        } catch (\Exception $e) {
            $this->logError(__FUNCTION__, $e);
            return collect();
        }
    }

    public function getParentInterests()
    {
        try {
            $parents = $this->interestRepository->getParentInterests();
            return $parents ?? collect();
        } catch (\Exception $e) {
            $this->logError(__FUNCTION__, $e);
            return collect();
        }
    }
}
