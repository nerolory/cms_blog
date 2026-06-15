<?php

namespace Database\Factories;

use App\Models\Category;
use App\Support\TypeCast;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Фабрика модели Category.
 *
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Возвращает определение фабрики.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = TypeCast::string($this->faker->unique()->words(2, true));

        return ['name' => ucfirst($name), 'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(100,
            99999), 'description' => $this->faker->optional()->sentence(),
            'sort_order' => $this->faker->numberBetween(0, 100)];
    }
}
