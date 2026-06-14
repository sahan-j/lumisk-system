<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Office & Supplies', 'color' => '#6d5cff', 'icon' => 'building'],
            ['name' => 'Software & Tools', 'color' => '#00d4ff', 'icon' => 'device-laptop'],
            ['name' => 'Marketing', 'color' => '#f59e0b', 'icon' => 'speakerphone'],
            ['name' => 'Travel', 'color' => '#10b981', 'icon' => 'car'],
            ['name' => 'Utilities', 'color' => '#ef4444', 'icon' => 'bolt'],
            ['name' => 'Hardware', 'color' => '#8b5cf6', 'icon' => 'cpu'],
            ['name' => 'Salaries', 'color' => '#06b6d4', 'icon' => 'users'],
            ['name' => 'Other', 'color' => '#94a3b8', 'icon' => 'dots'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::updateOrCreate(['name' => $category['name']], $category);
        }
    }
}
