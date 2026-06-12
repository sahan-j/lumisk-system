<?php

namespace Database\Seeders;

use App\Models\SavedItem;
use Illuminate\Database\Seeder;

class SavedItemsSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Website Design', 'description' => 'Custom responsive website UI/UX design', 'unit_price' => 75000, 'unit' => 'project'],
            ['name' => 'Web Development', 'description' => 'Full-stack web application development', 'unit_price' => 5000, 'unit' => 'hour'],
            ['name' => 'Logo & Branding', 'description' => 'Brand identity and logo design package', 'unit_price' => 45000, 'unit' => 'package'],
            ['name' => 'Monthly Maintenance', 'description' => 'Website hosting, updates and support', 'unit_price' => 15000, 'unit' => 'month'],
            ['name' => 'SEO Optimization', 'description' => 'Search engine optimization and audit', 'unit_price' => 30000, 'unit' => 'project'],
        ];

        foreach ($items as $item) {
            SavedItem::updateOrCreate(['name' => $item['name']], $item);
        }
    }
}
