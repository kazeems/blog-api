<?php

namespace App\Jobs;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Bus\Queueable;
use Image;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadUserAvatar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $disk = $this->user->disk;
        Log::info("Disk: " . $disk);
        $pictureName = $this->user->profile_picture;
        $original_file = storage_path() . '/uploads/avatars/original/' . $pictureName;

        try {
            // create the large Image and save to tmp disk
            Image::make($original_file)->fit(100, 100, function ($constraint) {
                $constraint->aspectRatio();
            })->save($profile_pic = storage_path('/uploads/avatars/profile_pic/' . $pictureName));


            // store images to permanent disk

            // Original
            if (Storage::disk($disk)->put('/uploads/avatars/original/' . $pictureName, fopen($original_file, 'r+'))) {
                File::delete($original_file);
            }

            // Large
            if (Storage::disk($disk)->put('/uploads/avatars/profile_pic/' . $pictureName, fopen($profile_pic, 'r+'))) {
                File::delete($profile_pic);
            }

            // // Thumbnail
            // if (Storage::disk($disk)->put('/uploads/properties/thumbnail/' . $imageName, fopen($thumbnail, 'r+'))) {
            //     File::delete($thumbnail);
            // }

            // update database record with success flag
            $this->user->update([
                'upload_successful' => true
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}