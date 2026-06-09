<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'supervisor_id',
        'phone',
        'magasin',
        'avatar',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * The supervisor (N+1) this user reports to.
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supervisor_id');
    }

    /**
     * The team members (N-1) supervised by this user.
     */
    public function terrains(): HasMany
    {
        return $this->hasMany(self::class, 'supervisor_id');
    }

    /**
     * Terrain reports authored by this user.
     */
    public function terrainReports(): HasMany
    {
        return $this->hasMany(TerrainReport::class, 'user_id');
    }

    /**
     * Terrain reports addressed to this user as supervisor.
     */
    public function receivedReports(): HasMany
    {
        return $this->hasMany(TerrainReport::class, 'supervisor_id');
    }

    /**
     * Orders created by this user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    /**
     * Messages sent by this user.
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Messages received by this user.
     */
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /**
     * Terrain complaints created by this user.
     */
    public function terrainComplaints(): HasMany
    {
        return $this->hasMany(TerrainComplaint::class, 'user_id');
    }

    /**
     * Clients managed by this user as their single Agent Marketeur.
     */
    public function managedClients(): HasMany
    {
        return $this->hasMany(Client::class, 'agent_id');
    }

    /**
     * The client profile linked to this login account (role: client).
     */
    public function clientProfile(): HasOne
    {
        return $this->hasOne(Client::class, 'user_id');
    }

    /**
     * Invoices issued by this user (Agent Marketeur).
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'agent_id');
    }

    /**
     * Colleagues = users sharing the same supervisor, excluding the user itself.
     *
     * @return Builder<self>
     */
    public function colleagues(): Builder
    {
        return self::query()
            ->where('supervisor_id', $this->supervisor_id)
            ->whereNotNull('supervisor_id')
            ->where('id', '!=', $this->id);
    }

    /**
     * Whether the user holds the given role.
     */
    public function hasRoleEnum(Role $role): bool
    {
        return $this->role === $role;
    }

    public function isMarketeurTerrain(): bool
    {
        return $this->role === Role::MarketeurTerrain;
    }

    /**
     * Get the user's avatar URL.
     */
    public function avatarUrl(): string
    {
        if ($this->avatar) {
            return asset('storage/'.$this->avatar);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=6366F1&color=fff&size=128';
    }

    /**
     * Scope to a given role enum (the `role` column, not the Spatie relation).
     *
     * @param  Builder<self>  $query
     */
    public function scopeWithRole(Builder $query, Role $role): Builder
    {
        return $query->where('role', $role->value);
    }
}
