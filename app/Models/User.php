<?php

namespace App\Models;

use App\Enums\ThemeEnum;
use App\Enums\UserPermissionEnum;
use App\Scopes\UserScope;
use Carbon\Carbon;
use http\Exception\UnexpectedValueException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
//use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property UserPermissionEnum $permission
 * @property ThemeEnum $theme
 *
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read bool $is_admin
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|Collection[] $collections
 * @property-read \Illuminate\Database\Eloquent\Collection|Post[] $posts
 * @property-read \Illuminate\Database\Eloquent\Collection|Share[] $shares
 * @property-read \Illuminate\Database\Eloquent\Collection|Tag[] $tags
 */
class User extends Authenticatable implements JWTSubject
{

    use /*HasApiTokens,*/ HasFactory, Notifiable;

    protected $rememberTokenName = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'permission',
        'theme'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'theme' => ThemeEnum::class,
        'permission' => UserPermissionEnum::class,
        'is_admin' => 'boolean'
    ];

    protected $appends = ['is_admin'];

    /**
     * @return void
     */
    public static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(new UserScope);
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->permission === UserPermissionEnum::Admin;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ResetPassword($token));
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier(): string
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public static function resetUrl(): string
    {
        return '/reset';
    }

    /**
     * @return UserPermissionEnum
     * @throws UnexpectedValueException
     */
    public static function getAuthenticationType(): UserPermissionEnum
    {
        if (Auth::guard('api')->check()) {
            return UserPermissionEnum::Api;
        } elseif (Auth::guard('share')->check()) {
            return UserPermissionEnum::Share;
        }

        throw new UnexpectedValueException('User not authenticated');
    }

    /**
     * @return bool
     */
    public function getIsAdminAttribute(): bool
    {
        return $this->isAdmin();
    }

    /**
     * @return HasMany
     */
    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }

    /**
     * @return HasMany
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * @return HasMany
     */
    public function shares(): HasMany
    {
        return $this->hasMany(Share::class);
    }

    /**
     * @return HasMany
     */
    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }
}
