<?php

namespace Database\Factories;

use App\Models\ArrivalDetail;
use App\Models\ArrivalHeader;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArrivalDetail>
 */
class ArrivalDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'arrival_header_id' => ArrivalHeader::factory(),
            'manufacturer' => fake()->randomElement(['Nippon Steel', 'Posco', 'KS Mira', 'Gunawan Steel']),
            'grade' => fake()->randomElement(['SWRH82B', 'SWRH62B', 'SWRM6']),
            'heat_no' => fake()->unique()->numerify('########'),
            'coil_no' => fake()->numerify('########'),
            'weight' => fake()->randomFloat(3, 0.5, 5),
            'size' => fake()->randomElement([5.5, 6.5, 8.0, 9.5, 10.0, 12.0]),
            'qr_date' => fake()->date(),
            'source' => 'scan',
            'scanned_at' => fake()->dateTimeBetween('-7 days'),
        ];
    }

    /**
     * Indicate the row was typed in because the QR tag was unreadable.
     */
    public function manual(): static
    {
        return $this->state(fn (array $attributes) => ['source' => 'manual']);
    }
}
