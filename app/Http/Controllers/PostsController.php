<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
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
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'body' => $request->body,
            'post_image' => $filename,
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

    public function editPost() {}

    public function deletePost() {}

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
            'data' => $posts
        ], 200);
    }
}
