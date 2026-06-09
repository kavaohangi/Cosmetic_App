<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role as SpatieRole;

class ClientService
{
    /**
     * Create a client together with its login account (role: client).
     *
     * @param  array<string, mixed>  $attributes  Validated client attributes.
     * @return array{client: Client, login_email: string, plain_password: string}
     */
    public function createWithAccount(array $attributes, User $creator, ?int $agentId): array
    {
        return DB::transaction(function () use ($attributes, $creator, $agentId): array {
            $loginEmail = $this->resolveLoginEmail($attributes['email'] ?? null, $attributes['name']);
            $plainPassword = Str::password(10, symbols: false);

            $account = User::create([
                'name' => $attributes['name'],
                'email' => $loginEmail,
                'phone' => $attributes['phone'] ?? null,
                'password' => Hash::make($plainPassword),
                'role' => Role::Client,
                'is_active' => true,
            ]);

            SpatieRole::findOrCreate(Role::Client->value, 'web');
            $account->syncRoles([Role::Client->value]);

            $client = Client::create([
                ...$attributes,
                'created_by' => $creator->id,
                'agent_id' => $agentId,
                'user_id' => $account->id,
            ]);

            return [
                'client' => $client,
                'login_email' => $loginEmail,
                'plain_password' => $plainPassword,
            ];
        });
    }

    /**
     * Use the provided email if free, otherwise generate a unique login.
     */
    private function resolveLoginEmail(?string $email, string $name): string
    {
        if ($email !== null && ! User::query()->where('email', $email)->exists()) {
            return $email;
        }

        $base = Str::slug($name, '.') ?: 'client';

        do {
            $candidate = sprintf('%s.%s@client.premidis.local', $base, Str::lower(Str::random(5)));
        } while (User::query()->where('email', $candidate)->exists());

        return $candidate;
    }
}
