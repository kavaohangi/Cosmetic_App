<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'type' => fake()->randomElement(['pharmacie', 'boutique', 'grossiste', 'particulier']),
            'address' => fake()->streetAddress(),
            'ville' => fake()->city(),
            'created_by' => null,
        ];
    }
}
