<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateDefaultCategories
{
    public $categories = [
        "Salario",
        "Arriendo",
        "Alimentación",
        "Transporte",
        "Ocio",
        "Otros",
    ];

    /**
     * Handle the event.
     *
     * @param  $event
     * @return void
     */
    public function handle(Registered $event)
    {
        $user = $event->user;

        collect($this->categories)->each(function($category) use ($user) {
            $user->categories()->create([
                'name' => $category
            ]);
        });
    }
}
