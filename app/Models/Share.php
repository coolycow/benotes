<?php

namespace App\Models;

use App\Enums\SharePermissionEnum;
use App\Scopes\ShareScope;
use Carbon\Carbon;
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read User $user
 * @property-read Post $post
 * @property-read Collection $collection
 */
class Share extends Authenticatable
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shares';

    protected $casts = [
        'tokens' => 'string',
        'is_active' => 'boolean',
        'permission' => SharePermissionEnum::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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

    /**
     * @return BelongsTo
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * @return BelongsTo
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
