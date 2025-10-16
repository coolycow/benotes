<?php

namespace App\Models;

use App\Enums\PostTypeEnum;
use App\Scopes\PostScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $collection_id
 *
 * @property PostTypeEnum $type
 * @property string $content
 *
 * @property string|null $url
 * @property string|null $title
 * @property string|null $description
 *
 * @property string|null $color
 * @property string|null $image_path
 * @property string|null $base_url
 *
 * @property int $order
 *
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property-read string|null $human_created_at
 * @property-read string|null $human_updated_at
 * @property-read string|null $human_deleted_at
 *
 * @property-read User $user
 * @property Collection|Tag[] $tags // Важно делать именно так, чтобы правильно работал трансфер!!!
 */
class Post extends Model
{
    use SoftDeletes, HasFactory;

    /**
     * @return void
     */
    public static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(new PostScope);
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'order' => 'integer',
        'content' => 'string',
        'type' => PostTypeEnum::class,
        'url' => 'string',
        'base_url' => 'string',
        'title' => 'string',
        'description' => 'string',
        'color' => 'string',
        'image_path' => 'string',
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
        'content',
        'type',
        'url',
        'base_url',
        'title',
        'description',
        'color',
        'image_path',
        'user_id',
        'collection_id',
        'order'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'user_id',
        'deleted_at'
    ];

    protected $appends = [
        'human_created_at',
        'human_updated_at',
        'human_deleted_at',
    ];

    /**
     * @param $value
     * @return string|null
     */
    public function getImagePathAttribute($value): ?string
    {
        if (Str::startsWith($value, 'thumbnail_')) {
            return Storage::url('thumbnails/' . $value);
        }

        return $value;
    }

    /**
     * @param $value
     * @return void
     */
    public function setImagePathAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['image_path'] = null;
            return;
        }

        $this->attributes['image_path'] = (strlen($value) > 512) ? null : $value;
    }

    /**
     * @return BelongsToMany
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return string|null
     */
    public function getHumanCreatedAtAttribute(): ?string
    {
        return $this->created_at?->diffForHumans();
    }

    /**
     * @return string|null
     */
    public function getHumanUpdatedAtAttribute(): ?string
    {
        return $this->updated_at?->diffForHumans();
    }

    /**
     * @return string|null
     */
    public function getHumanDeletedAtAttribute(): ?string
    {
        return $this->deleted_at?->diffForHumans();
    }
}
