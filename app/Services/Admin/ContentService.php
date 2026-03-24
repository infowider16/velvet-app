<?php



namespace App\Services\Admin;



use App\Contracts\Services\AdminContentServiceInterface;
use App\Services\BaseService;

use App\Contracts\Repositories\ContentRepositoryInterface;

use Illuminate\Http\Request;



class ContentService extends BaseService implements AdminContentServiceInterface

{

    protected ContentRepositoryInterface $contentRepository;


    public function __construct(ContentRepositoryInterface $contentRepository)

    {

        $this->contentRepository = $contentRepository;
    }





    public function getAllContents()

    {

        try {

            return $this->contentRepository->getByWhere([], ['id' => 'asc']);
        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return collect(); // Return empty collection on error

        }
    }



    public function updateContent($request, $id)
    {
        try {
            return $this->handleServiceCall(function () use ($request, $id) {
                $content = $this->contentRepository->find($id);

                if (!$content) {
                    return ['status' => false, 'message' => 'Content not found'];
                }

                $data = [
                    'title' => $request->title,
                    'title_translation' => [
                        'en' => $request->title ?? null,
                        'ge' => $request->title_translation ?? null,
                    ],
                    'description' => $request->description,
                    'description_translation' => [
                        'en' => $request->description ?? null,
                        'ge' => $request->description_translation ?? null,
                    ],
                ];

                $updated = $this->contentRepository->update(['id' => $id], $data);

                return $updated
                    ? ['status' => true, 'message' => 'Content updated successfully']
                    : ['status' => false, 'message' => 'Failed to update content'];
            });
        } catch (\Exception $e) {
            $this->logError(__FUNCTION__, $e);
            return ['status' => false, 'message' => 'Something went wrong while updating content'];
        }
    }


    public function getContentDetail($id)

    {

        try {

            return $this->handleServiceCall(function () use ($id) {

                return $this->contentRepository->find($id);
            }, null); // Return null on error

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return $this->errorResponse('Something went wrong while fetching content details');
        }
    }
}
