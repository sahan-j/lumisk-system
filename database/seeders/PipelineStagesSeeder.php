<?php

namespace Database\Seeders;

use App\Models\PipelineStage;
use Illuminate\Database\Seeder;

class PipelineStagesSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            ['name' => 'New Lead', 'color' => '#94a3b8', 'sort_order' => 1, 'is_won' => false, 'is_lost' => false],
            ['name' => 'Contacted', 'color' => '#00d4ff', 'sort_order' => 2, 'is_won' => false, 'is_lost' => false],
            ['name' => 'Qualified', 'color' => '#6d5cff', 'sort_order' => 3, 'is_won' => false, 'is_lost' => false],
            ['name' => 'Proposal Sent', 'color' => '#f59e0b', 'sort_order' => 4, 'is_won' => false, 'is_lost' => false],
            ['name' => 'Negotiation', 'color' => '#8b5cf6', 'sort_order' => 5, 'is_won' => false, 'is_lost' => false],
            ['name' => 'Won', 'color' => '#10b981', 'sort_order' => 6, 'is_won' => true, 'is_lost' => false],
            ['name' => 'Lost', 'color' => '#ef4444', 'sort_order' => 7, 'is_won' => false, 'is_lost' => true],
        ];

        foreach ($stages as $stage) {
            PipelineStage::firstOrCreate(['name' => $stage['name']], $stage);
        }
    }
}
