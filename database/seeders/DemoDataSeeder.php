<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Party;
use Illuminate\Database\Seeder;

/**
 * Demo master data: 15 parties and 30 items.
 *
 * Posted invoices and payments are seeded in a later step, once the document
 * and payment engines exist — they must go through those services, not raw
 * inserts, so numbering and status stay correct.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (Party::query()->exists() || Item::query()->exists()) {
            return;
        }

        Party::factory()->count(9)->customer()->create();
        Party::factory()->count(4)->supplier()->create();
        Party::factory()->count(2)->both()->create();

        Item::factory()->count(24)->product()->create();
        Item::factory()->count(6)->service()->create();
    }
}
