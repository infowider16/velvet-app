<?php



namespace App\Traits;



use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Log;



trait UploadImageTrait

{

    public function uploadImage($image, $path)

    {

        try {

            $name = time() . rand(99, 1000) . '.' . $image->getClientOriginalExtension();

            $imageData = $image->storeAs($path, $name, 'public');

            return $imageData;

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            throw $e;

        }

    }

    public function uploadMediaFiles($files, $folder = 'user_images')

    {

        try {

            $paths = [];



            foreach ($files as $file) {



                $ext = time() . rand(99, 1000) . '.' . $file->getClientOriginalExtension();

                // $filename = uniqid() . '.' . $ext;

                $file->storeAs($folder, $ext, 'public');

                $paths[] = "{$folder}/{$ext}";

            }



            return $paths;

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            throw $e;

        }

    }



    public function deleteMediaFiles($files)

    {

        try {

            foreach ($files as $file) {

                // delete from the same 'public' disk where files are stored

                Storage::disk('public')->delete($file);

            }

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            throw $e;

        }

    }

}

