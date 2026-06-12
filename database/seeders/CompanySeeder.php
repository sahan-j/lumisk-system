<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'Lumisk Technology',
                'email' => 'hello@lumisktechnology.com',
                'phone' => '+94 70 000 0000',
                'address' => "No. 1, Galle Road,\nColombo, Sri Lanka",
                'website' => 'https://lumisktechnology.com',
                'invoice_prefix' => 'INV',
                'estimate_prefix' => 'EST',
                'invoice_next_number' => 1,
                'estimate_next_number' => 1,
                'estimate_expiry_days' => 30,
                'default_tax_rate' => 0,
                'currency' => 'LKR',
                'default_terms' => 'Payment is due within 14 days of the invoice date. Late payments may incur additional charges.',
                'default_notes' => 'Thank you for your business with Lumisk Technology.',
            ]
        );
    }
}
