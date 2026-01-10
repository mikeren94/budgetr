<?php

namespace App\Http\Controllers;

use App\Actions\Categories\ListCategoriesAction;
use App\Http\Requests\StoreCategoryRequest;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Http\Requests\ListCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Actions\Categories\StoreCategoriesAction;
use App\Http\Requests\UpdateCategoryRequest;
use App\Actions\Categories\UpdateCategoryAction;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Actions\Categories\DeleteCategoryAction;

class CategoryController extends Controller
{
    use AuthorizesRequests;

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

    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    public function update(UpdateCategoryRequest $request, Category $category, UpdateCategoryAction $action)
    {
        $this->authorize('update', $category);
        
        $updated = $action->execute($category, $request->validated());

        return response()->json([
            'message' => 'Category updated successfully.',
            'data' => new CategoryResource($updated),
        ]);
    }

     public function destroy(Category $category, DeleteCategoryAction $action)
    {
        $this->authorize('update', $category);

        $action->execute($category);

        return response()->noContent();
    }
}
