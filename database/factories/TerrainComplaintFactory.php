<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\TerrainComplaint;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TerrainComplaint>
 */
class TerrainComplaintFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'type' => $this->faker->randomElement(['complaint', 'proposition']),
            'description' => $this->faker->paragraphs(3, asText: true),
            'status' => $this->faker->randomElement(['pending', 'reviewed', 'resolved']),
            'response' => $this->faker->optional()->paragraphs(2, asText: true),
            'date' => $this->faker->dateTimeBetween('-30 days'),
        ];
    }

    /**
     * Indicate that the complaint is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'response' => null,
        ]);
    }

    /**
     * Indicate that the complaint is a complaint type.
     */
    public function complaint(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'complaint',
        ]);
    }

    /**
     * Indicate that the complaint is a proposition type.
     */
    public function proposition(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'proposition',
        ]);
    }
}
