<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

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
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Get the feeds that belong to the user.
     */
    public function feeds(): HasMany
    {
        return $this->hasMany(Feed::class);
    }

    /**
     * Get the settings that belong to the user.
     */
    public function settings(): HasMany
    {
        return $this->hasMany(UserSetting::class);
    }

    /**
     * Get a user setting value by key.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        $value = $this->settings()
            ->where('key', $key)
            ->value('value');

        return $value ?? $default;
    }

    /**
     * Set a user setting value by key.
     */
    public function setSetting(string $key, mixed $value): void
    {
        $this->settings()->updateOrCreate(
            ['key' => $key],
            ['value' => $value],
        );
    }
}
