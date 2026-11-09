<?php

namespace Database\Factories;

use App\Models\ArrivalHeader;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArrivalHeader>
 */
class ArrivalHeaderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'po_number' => 'PO-'.fake()->unique()->numerify('######'),
            'tanggal' => fake()->date(),
            'item' => fake()->randomElement(['Wire Rod', 'Billet', 'Steel Coil']),
            'description' => fake()->sentence(4),
            'supplier_id' => Supplier::factory(),
        ];
    }
}
