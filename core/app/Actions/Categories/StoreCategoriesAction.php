<?php

namespace App\Actions\Categories;

use App\Models\Category;
use App\Models\User;

class StoreCategoriesAction
{
    public function execute(User $user, array $data): Category
    {
       $category = Category::create([
            ...$data,
            'user_id' => $user->id,
        ]);

        return $category;
    }
}