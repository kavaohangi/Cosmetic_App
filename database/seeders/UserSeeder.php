<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Direction
        $admin = $this->createUser('Admin Premidis', 'admin@premidis.test', Role::Admin);
        $directeur = $this->createUser('Directeur Général', 'directeur@premidis.test', Role::Directeur, $admin);

        // Support
        $this->createUser('Commercial Premidis', 'commercial@premidis.test', Role::Commercial, $directeur);
        $this->createUser('Magasinier Premidis', 'magasinier@premidis.test', Role::Magasinier, $directeur);

        // Hiérarchie marketing
        $chef = $this->createUser('Chef Marketing', 'chef@premidis.test', Role::ChefMarketing, $directeur);

        $agent1 = $this->createUser('Agent Marketeur 1', 'agent1@premidis.test', Role::AgentMarketeur, $chef);
        $agent2 = $this->createUser('Agent Marketeur 2', 'agent2@premidis.test', Role::AgentMarketeur, $chef);

        // Marketeurs terrain (collègues = même supervisor pour tester le chat)
        $this->createUser('Marketeur Terrain 1', 'terrain1@premidis.test', Role::MarketeurTerrain, $agent1);
        $this->createUser('Marketeur Terrain 2', 'terrain2@premidis.test', Role::MarketeurTerrain, $agent1);
        $this->createUser('Marketeur Terrain 3', 'terrain3@premidis.test', Role::MarketeurTerrain, $agent2);
        $this->createUser('Marketeur Terrain 4', 'terrain4@premidis.test', Role::MarketeurTerrain, $agent2);
    }

    private function createUser(string $name, string $email, Role $role, ?User $supervisor = null): User
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'role' => $role,
                'supervisor_id' => $supervisor?->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles([$role->value]);

        return $user;
    }
}
