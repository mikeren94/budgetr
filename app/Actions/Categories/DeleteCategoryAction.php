<?php
namespace App\Actions\Categories;

use App\Models\Category;

class DeleteCategoryAction
{
    public function execute(Category $category)
    {
        // Delete the category
        $category->delete();

        return response()->noContent(); // 204
    }
}