<?php

namespace App\Actions\Categories;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class ListCategoriesAction
{
    public function execute(User $user): Collection
    {
        return $this->getCategories($user);
    }

    private function getCategories(User $user)
    {
        return Category::where('user_id', $user->id)
            ->orderBy('name', 'desc')
            ->get();
    }
}