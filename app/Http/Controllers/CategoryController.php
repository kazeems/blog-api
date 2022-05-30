<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Str;

class CategoryController extends Controller
{
    public function createCategory(Request $request) {
        $request->validate([
            'category_name' => ['required', 'min:3', 'max:10', 'unique:categories,category_name']
        ]);

        Category::create([
            'category_name' => $request->category_name
        ]);

    }

    public function getCategories() {  
       $categories = Category::all();
        return response()->json([
            'success' => true,
            'message' => 'Categories returned successfully',
            'data' => CategoryResource::collection($categories)
        ]);
    }
}
