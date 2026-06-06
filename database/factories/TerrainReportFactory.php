<?php

namespace Database\Factories;

use App\Models\TerrainReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TerrainReport>
 */
class TerrainReportFactory extends Factory
{
    protected $model = TerrainReport::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $rupture = fake()->boolean(30);

        return [
            'user_id' => User::factory(),
            'supervisor_id' => null,
            'date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'nb_ventes' => fake()->numberBetween(0, 25),
            'plaintes_clients' => fake()->boolean(40) ? fake()->sentence() : null,
            'propositions_clients' => fake()->boolean(40) ? fake()->sentence() : null,
            'rupture_stock' => $rupture,
            'produits_rupture' => $rupture ? [] : null,
            'photo_url' => null,
        ];
    }
}
