<?php

namespace App\Models;

use App\Enums\AccountStatus;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Eloquent-модель пользователя.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property string $theme
 * @property string|null $locale
 * @property string|null $avatar_path
 * @property AccountStatus $account_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Post> $posts
 * @property-read UserTokenWallet|null $tokenWallet
 */
#[Fillable(['name', 'email', 'password', 'theme', 'locale', 'avatar_path'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, MustVerifyEmailTrait, Notifiable;

    /**
     * casts.
     *
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed', 'account_status' => AccountStatus::class];
    }

    /**
     * Посты автора.
     *
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Кошелёк токенов пользователя.
     *
     * @return HasOne<UserTokenWallet, $this>
     */
    public function tokenWallet(): HasOne
    {
        return $this->hasOne(UserTokenWallet::class);
    }

    /**
     * Проверяет account active.

     *
     * @return bool
     */
    public function isAccountActive(): bool
    {
        return $this->account_status === AccountStatus::Active;
    }

    /**
     * Проверяет account suspended.

     *
     * @return bool
     */
    public function isAccountSuspended(): bool
    {
        return $this->account_status === AccountStatus::Suspended;
    }

    /**
     * Whether the user may create posts (Spatie permission).

     *
     * @return bool
     */
    public function canCreatePosts(): bool
    {
        if ($this->hasRole(['admin', 'owner'])) {
            return true;
        }

        return $this->can('posts.create');
    }

    /**
     * Whether the user may manage any post (change author, publish flags on web).

     *
     * @return bool
     */
    public function canManageAllPosts(): bool
    {
        return $this->hasRole(['admin', 'owner']) || $this->can('posts.manage.all');
    }

    /**
     * Whether the user may access the Filament /admin panel.

     *
     * @return bool
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() !== 'admin') {
            return false;
        }

        return $this->hasAnyRole(['moderator', 'admin',
            'owner']) || $this->can('panel.access.moderator') || $this->can('panel.access.admin');
    }
}
