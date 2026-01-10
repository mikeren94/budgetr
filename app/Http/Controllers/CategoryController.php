<?php

namespace App\Http\Controllers;

use App\Actions\Categories\ListCategoriesAction;
use App\Http\Requests\StoreCategoryRequest;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Http\Requests\ListCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Actions\Categories\StoreCategoriesAction;

class CategoryController extends Controller
{
    public function index(ListCategoryRequest $request, ListCategoriesAction $action) 
    {
        return CategoryResource::collection(
            $action->execute($request->user())
        );
    }

    public function store(StoreCategoryRequest $request, StoreCategoriesAction $action)
    {
        $category = $action->execute($request->user(), $request->validated());

        return response()->json([
            'message' => 'Category created successfully.',
            'data' => new CategoryResource($category),
        ], 201);

    }
}
