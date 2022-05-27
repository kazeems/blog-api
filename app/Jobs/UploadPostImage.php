<?php

namespace App\Jobs;

use App\Models\Post;
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

class UploadPostImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $disk = $this->post->disk;
        Log::info("Disk: " . $disk);
        $imageName = $this->post->post_image;
        $original_file = storage_path() . '/uploads/original/' . $imageName;

        try {
            // create the large Image and save to tmp disk
            Image::make($original_file)->fit(800, 600, function ($constraint) {
                $constraint->aspectRatio();
            })->save($featured = storage_path('/uploads/featured/' . $imageName));


            // store images to permanent disk

            // Original
            if (Storage::disk($disk)->put('/uploads/post_images/original/' . $imageName, fopen($original_file, 'r+'))) {
                File::delete($original_file);
            }

            // Large
            if (Storage::disk($disk)->put('/uploads/post_images/featured/' . $imageName, fopen($featured, 'r+'))) {
                File::delete($featured);
            }

            // // Thumbnail
            // if (Storage::disk($disk)->put('/uploads/properties/thumbnail/' . $imageName, fopen($thumbnail, 'r+'))) {
            //     File::delete($thumbnail);
            // }

            // update database record with success flag
            $this->post->update([
                'upload_successful' => true
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}