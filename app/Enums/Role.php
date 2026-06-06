<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Directeur = 'directeur';
    case ChefMarketing = 'chef_marketing';
    case AgentMarketeur = 'agent_marketeur';
    case MarketeurTerrain = 'marketeur_terrain';
    case Commercial = 'commercial';
    case Magasinier = 'magasinier';

    /**
     * Human readable label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrateur',
            self::Directeur => 'Directeur Général',
            self::ChefMarketing => 'Chef Marketing',
            self::AgentMarketeur => 'Agent Marketeur',
            self::MarketeurTerrain => 'Marketeur Terrain',
            self::Commercial => 'Commercial',
            self::Magasinier => 'Magasinier',
        };
    }

    /**
     * All role values as a flat array (useful for migrations / validation).
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $role): string => $role->value, self::cases());
    }
}
