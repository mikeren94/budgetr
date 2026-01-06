<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Http\Requests\ListCategoryRequest;
use App\Http\Resources\CategoryResource;

class CategoryController extends Controller
{
    public function index(ListCategoryRequest $request) 
    {
        $query = Category::where('user_id', $request->user()->id);

        return CategoryResource::collection(
            $query->orderBy('name', 'desc')->get()
        );
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Category created successfully.',
            'data' => $category,
        ], 201);

    }
}
