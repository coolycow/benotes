<?php

namespace App\Models;

use App\Scopes\CollectionScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $parent_id
 * @property int|null $icon_id
 * @property string $name
 *
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|Collection[] $nested
 */
class Collection extends Model
{
    use SoftDeletes, HasFactory;

    const string IMPORTED_COLLECTION_NAME = 'Imported Bookmarks';

    /**
     * @return void
     */
    public static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(new CollectionScope);
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'icon_id' => 'integer',
        'parent_id' => 'integer',
        'name' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'user_id',
        'icon_id',
        'parent_id',
        'root_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'user_id',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    /**
     * @param int|null $id
     * @param bool $is_uncategorized
     * @return int|null
     */
    public static function getCollectionId(?int $id, bool $is_uncategorized = false): ?int
    {
        return $is_uncategorized || is_null($id) ? null : $id;
    }

    /**
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return HasMany
     */
    public function nested(): HasMany
    {
        return $this->children()->with('nested')->orderBy('name');
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param $value
     * @return void
     */
    protected function setTitleAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['title'] = null;
            return;
        }

        $this->attributes['title'] = Str::limit($value, 255);
    }
}
