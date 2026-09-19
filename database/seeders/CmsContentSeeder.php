<?php

namespace Database\Seeders;

use App\Services\CmsService;
use Illuminate\Database\Seeder;

class CmsContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $created = app(CmsService::class)->seed();

        $this->command?->info("Seeded {$created} CMS content block(s).");
    }
}
