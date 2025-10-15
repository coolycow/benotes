<?php

namespace App\Models;

use App\Enums\SharePermissionEnum;
use App\Scopes\ShareScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int|null $collection_id
 * @property int|null $post_id
 *
 * @property string $token
 * @property bool $is_active
 * @property SharePermissionEnum $permission
 *
 * @property-read User $user
 */
class Share extends Authenticatable
{
    use HasFactory;

    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shares';

    protected $casts = [
        'is_active' => 'boolean',
        'permission' => SharePermissionEnum::class,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'token',
        'collection_id',
        'is_active',
        'permission',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        '',
    ];

    /**
     * @return void
     */
    public static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(new ShareScope);
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
