<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Auth\Events\Registered;
use App\Models\User;

class CreateDefaultCategories
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(User $user): void
    {
        $defaults = json_decode(
            file_get_contents(resource_path('data/default_categories.json')),
            true
        );

        foreach ($defaults as $category) {
            $user->categories()->create($category);
        }

    }
}
