<?php

namespace Database\Factories;

use App\Enums\PostTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Post;
use App\Models\Collection;

class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->randomNumber(),
            'title' => $this->faker->title(),
            'content' => $this->faker->sentence(),
            'collection_id' => Collection::query()->count() > 0
                ? Collection::query()->first()->getKey()
                : null,
            'type' => PostTypeEnum::Text,
            'user_id' => User::query()->first()->getKey(),
            'order' => Post::query()->where('collection_id', null)->count()
        ];
    }
}
