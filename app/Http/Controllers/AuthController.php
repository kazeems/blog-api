<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Jobs\UploadUserAvatar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function register(Request $request) {
        // validation
        $request->validate([
            'name' => ['required'],
            'email' => ['required','email', 'unique:users,email'],
            'password' => ['required', 'min:6', 'confirmed'],
            'profile_picture' => ['required', 'mimes:png,jpg', 'max:2048']
        ]);

        //get the image
        $profile_picture = $request->file('profile_picture');
 
        // get original file name and replace any spaces with _
        // example: ofiice card.png = timestamp()_office_card.pnp
        $filename = time()."_".preg_replace('/\s+/', '_', strtolower($profile_picture->getClientOriginalName()));

        // move image to temp location (tmp disk)
        $tmp = $profile_picture->storeAs('uploads/avatars/original', $filename, 'tmp');

        // creating user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'profile_picture' => $filename,
            'disk' => config('site.upload_disk')
        ]);
         //dispacth job to handle image manipulation
         $this->dispatch(new UploadUserAvatar($user));
 
        // create token
        $token = $user->createToken('default')->plainTextToken;

        // return response
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => [
                'token' => $token,
                'user' => $user
            ]
        ]);
    }

    public function login(Request $request) {
        $request->validate([
            'email' => ['required','email'],
            'password' => ['required']
        ]);

        // find user with email
        $user = User::where('email', $request->email)->first();

        if(!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect email or password'
            ]);
        }

        // delete any existing token for the user
        $user->tokens()->delete();

        // create a new token for the user
        $token = $user->createToken("login")->plainTextToken;

        // return token
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token
            ]
        ]);
    }

    public function logout(Request $request) {
        auth("sanctum")->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'User logged out successfully'
        ]);
    }

    public function getUsers() {
        $users = User::all();

        if($users->count() < 1) {
            return response()->json([
                'success' => false,
                'message' => 'No user found.'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Users found sucessfully',
            'data' => UserResource::collection($users)
        ], 200);
    }
}
