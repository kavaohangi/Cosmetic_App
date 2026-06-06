<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'sku' => strtoupper(Str::random(8)),
            'description' => fake()->sentence(),
            'category' => fake()->randomElement(['Soin visage', 'Maquillage', 'Parfum', 'Cheveux', 'Corps']),
            'price' => fake()->randomFloat(2, 5, 200),
            'stock' => fake()->numberBetween(0, 100),
            'seuil_alerte' => 10,
            'image_url' => null,
            'is_active' => true,
        ];
    }

    public function enRupture(): static
    {
        return $this->state(fn (): array => [
            'stock' => fake()->numberBetween(0, 5),
            'seuil_alerte' => 10,
        ]);
    }
}
