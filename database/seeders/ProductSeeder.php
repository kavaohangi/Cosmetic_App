<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Crème hydratante visage', 'category' => 'Soin visage', 'price' => 24.90, 'stock' => 120, 'seuil_alerte' => 20],
            ['name' => 'Sérum anti-âge', 'category' => 'Soin visage', 'price' => 39.50, 'stock' => 60, 'seuil_alerte' => 15],
            ['name' => 'Rouge à lèvres mat', 'category' => 'Maquillage', 'price' => 14.00, 'stock' => 8, 'seuil_alerte' => 10],
            ['name' => 'Fond de teint fluide', 'category' => 'Maquillage', 'price' => 29.90, 'stock' => 45, 'seuil_alerte' => 15],
            ['name' => 'Mascara volume', 'category' => 'Maquillage', 'price' => 18.50, 'stock' => 5, 'seuil_alerte' => 12],
            ['name' => 'Parfum floral 50ml', 'category' => 'Parfum', 'price' => 59.00, 'stock' => 30, 'seuil_alerte' => 10],
            ['name' => 'Shampoing réparateur', 'category' => 'Cheveux', 'price' => 12.90, 'stock' => 200, 'seuil_alerte' => 30],
            ['name' => 'Après-shampoing nourrissant', 'category' => 'Cheveux', 'price' => 13.50, 'stock' => 18, 'seuil_alerte' => 20],
            ['name' => 'Gel douche apaisant', 'category' => 'Corps', 'price' => 9.90, 'stock' => 150, 'seuil_alerte' => 25],
            ['name' => 'Lait corporel hydratant', 'category' => 'Corps', 'price' => 16.90, 'stock' => 9, 'seuil_alerte' => 15],
        ];

        foreach ($products as $index => $data) {
            Product::updateOrCreate(
                ['sku' => 'PRD-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT)],
                [
                    ...$data,
                    'description' => $data['name'].' - gamme Premidis.',
                    'is_active' => true,
                ]
            );
        }
    }
}
