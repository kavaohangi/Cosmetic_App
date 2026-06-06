<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            ['name' => 'Pharmacie Centrale', 'type' => 'pharmacie', 'ville' => 'Casablanca', 'email' => 'contact@pharma-centrale.test'],
            ['name' => 'Boutique Belle Vie', 'type' => 'boutique', 'ville' => 'Rabat', 'email' => 'hello@bellevie.test'],
            ['name' => 'Grossiste Cosmetic Pro', 'type' => 'grossiste', 'ville' => 'Marrakech', 'email' => 'achat@cosmeticpro.test'],
            ['name' => 'Parfumerie Élégance', 'type' => 'boutique', 'ville' => 'Tanger', 'email' => 'info@elegance.test'],
            ['name' => 'Institut Beauté Plus', 'type' => 'pharmacie', 'ville' => 'Fès', 'email' => 'rdv@beauteplus.test'],
        ];

        foreach ($clients as $data) {
            Client::updateOrCreate(
                ['email' => $data['email']],
                [
                    ...$data,
                    'phone' => '+212 6 00 00 00 00',
                    'address' => 'Adresse '.$data['ville'],
                ]
            );
        }
    }
}
