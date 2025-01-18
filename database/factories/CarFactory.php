<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Car>
 */
class CarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'brand' => $this->faker->randomElement(['Toyota', 'Honda', 'Nissan', 'Ford', 'BMW']),
            'model' => $this->faker->word(),
            'license_plate' => strtoupper($this->faker->bothify('??-####')),
            'rental_price_per_day' => $this->faker->randomFloat(2, 50, 300), // Tarif sewa per hari
            'is_available' => $this->faker->boolean(80), // 80% chance of being true
        ];
    }
}
