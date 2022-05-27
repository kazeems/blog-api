<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Http\Resources\CommentResource;
use App\Jobs\UploadPostImage;
use App\Models\Post;
use Illuminate\Http\Request;
use Str;

class PostsController extends Controller
{
    public function createPost(Request $request) {
        $request->validate([
            'title' => ['required', 'min:5', 'unique:posts,title'],
            'body' => ['required', 'min:10'],
            'post_image' => ['required', 'mimes:png,jpg', 'max:2048']
        ]);

        //get the image
        $post_image = $request->file('post_image');
 
        // get original file name and replace any spaces with _
        // example: ofiice card.png = timestamp()_office_card.pnp
        $filename = time()."_".preg_replace('/\s+/', '_', strtolower($post_image->getClientOriginalName()));

        // move image to temp location (tmp disk)
        $tmp = $post_image->storeAs('uploads/original', $filename, 'tmp');

        $newPost = Post::create([
            'user_id' => auth("sanctum")->user()->id,
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'body' => $request->body,
            'post_image' => $filename,
            'disk' => config('site.upload_disk'),
        ]);

        //dispacth job to handle image manipulation
        $this->dispatch(new UploadPostImage($newPost));

        // return succcess response
        return response()->json([
            'success' => true,
            'message' => 'New property created successfully',
            'data' => new PostResource($newPost)
        ]);
    }

    public function showPosts() {
        $posts = Post::all();

        if($posts->count() < 1) {
            return response()->json([
                'success' => false,
                'message' => 'No post found.'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Posts found sucessfully',
            'data' => PostResource::collection($posts)
        ], 200);
    }

    public function showPost(Request $request, Post $post) {
        return response()->json([
            'success' => true,
            'message' => 'Post found successfully',
            'data' => new PostResource($post)
        ]);
    }

    public function updatePost(Request $request, Post $post) {
        $request->validate([
            'title' => ['required', 'min:5', 'unique:posts,title'],
            'body' => ['required', 'min:10']
        ]);

        $this->authorize('update', $post);

        $post->title = $request->title;
        $post->slug = Str::slug($request->title);
        $post->body = $request->body;
        $post->save();

        return response()->json([
            'success' => true,
            'messge' => 'Post updated successfully'
        ]);
    }

    public function deletePost(Post $post) {
        
        $this->authorize('delete', $post);
        //Deleting the images associated with the products
        foreach (['original', 'featured'] as $size) {
            //check if file exist
            if (Storage::disk($post->disk)->exists("uploads/post_images/{$size}/" . $post->post_image)) {
                Storage::disk($post->disk)->delete("uploads/post_images/{$size}/" . $post->post_image);
            }
        }

        
        // delete property
            $post->delete();

            // return succcess response
            return response()->json([
                'success' => true,
                'message' => 'Post deleted successfully'
            ]);
    }
   


}
