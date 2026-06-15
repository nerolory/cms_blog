<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Сидер БД category seeder.
 */
class CategorySeeder extends Seeder
{
    /**
     * run.
     */
    public function run(): void
    {
        Category::query()->firstOrCreate(['slug' => 'general'], ['name' => 'General',
            'description' => 'Default category', 'sort_order' => 0]);
        $categories = [['name' => 'News', 'slug' => 'news', 'sort_order' => 1], ['name' => 'Tutorials',
            'slug' => 'tutorials', 'sort_order' => 2], ['name' => 'Reviews', 'slug' => 'reviews', 'sort_order' => 3]];
        foreach ($categories as $category) {
            Category::query()->firstOrCreate(['slug' => $category['slug']], ['name' => $category['name'],
                'description' => null, 'sort_order' => $category['sort_order']]);
        }
    }
}
