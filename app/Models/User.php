<?php

namespace App\Models;

use App\Scopes\UserScope;
use Carbon\Carbon;
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
 * @property int $permission
 *
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read bool $is_admin
 */
class User extends Authenticatable implements JWTSubject
{

    use /*HasApiTokens,*/ HasFactory, Notifiable;

    protected $rememberTokenName = false;

    const int ADMIN = 255;

    const int API_USER = 1;
    const int SHARE_USER = 2;
    const int UNAUTHORIZED_USER = 0;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'permission'
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
        'permission' => 'integer',
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
        return $this->permission === self::ADMIN;
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

    public static function resetUrl(): string
    {
        return '/reset';
    }

    public static function getAuthenticationType(): int
    {
        if (Auth::guard('api')->check()) {
            return self::API_USER;
        } else if (Auth::guard('share')->check()) {
            return self::SHARE_USER;
        }
        return self::UNAUTHORIZED_USER;
    }

    /**
     * @return bool
     */
    public function getIsAdminAttribute(): bool
    {
        return $this->isAdmin();
    }
}
