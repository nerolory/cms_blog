<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Фабрика модели Tag.
 *
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    /**
     * Возвращает определение фабрики.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->word();

        return ['name' => ucfirst($name), 'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(100,
            99999)];
    }
}
