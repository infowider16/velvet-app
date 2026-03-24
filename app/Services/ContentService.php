<?php



namespace App\Services;



use App\Contracts\Repositories\ContentRepositoryInterface;

use App\Contracts\Repositories\FaqRepositoryInterface;

use Illuminate\Support\Facades\Log;
use App\Contracts\Repositories\InterestRepositoryInterface;



class ContentService

{

    protected $contentRepo;

    protected $faqRepo;
    protected InterestRepositoryInterface $interestRepository;



    public function __construct(ContentRepositoryInterface $contentRepo, FaqRepositoryInterface $faqRepo, InterestRepositoryInterface $interestRepository)

    {

        $this->contentRepo = $contentRepo;

        $this->faqRepo = $faqRepo;
        $this->interestRepository = $interestRepository;


    }



    public function getContentBySlug(string $slug)

    {

        try{

        return $this->contentRepo->getByWhere(['slug' => $slug], [], ['*'], [], [], 'first');
    
        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return null;

        }

    }



    public function getAllActiveFaqs()

    {

        try {

            return $this->faqRepo->all();

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return [];

        }

    }


     public function getAllInterests()
    {
        try {

            return $this->interestRepository->getByWhere([], ['id' => 'asc']);

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return [];

        }

    }

    public function getSubInterestsByParentId($interestId)

    {
        try {

            return $this->interestRepository->getByWhere(['parent_id' => $interestId], ['id' => 'asc']);

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return [];

        }
    }

}

