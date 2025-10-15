<?php

namespace App\Models;

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
 * @property int $type
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
 * @property-read Collection|Tag[] $tags
 */
class Post extends Model
{
    use SoftDeletes, HasFactory;

    const int POST_TYPE_TEXT = 1;
    const int POST_TYPE_LINK = 2;


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
        'type' => 'string',
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
     * @return string
     */
    public function getTypeAttribute($value): string
    {
        return intval($value) === self::POST_TYPE_LINK ? 'link' : 'text';
    }

    /**
     * @param $value
     * @return int
     */
    public static function getTypeFromString($value): int
    {
        return $value === 'text' ? self::POST_TYPE_TEXT : self::POST_TYPE_LINK;
    }

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

    /**
     * @param $user_id
     * @param $collection_id
     * @return void
     */
    public static function seedIntroData($user_id, $collection_id = null): void
    {
        $i = 1;

        Post::create([
            'title'         => 'GitHub - fr0tt/benotes: An open source self hosted web app for your notes and bookmarks.',
            'content'       => 'https://github.com/fr0tt/benotes',
            'type'          => self::POST_TYPE_LINK,
            'url'           => 'https://github.com/fr0tt/benotes',
            'color'         => '#1e2327',
            'image_path'    => 'https://opengraph.githubassets.com/9c1b74a8cc5eeee5c5c9f62701c42e1356595422d840d2e209bceb836deb5ffb/fr0tt/benotes',
            'base_url'      => 'https://github.com',
            'collection_id' => $collection_id,
            'user_id'       => $user_id,
            'order'         => $i++,
        ]);

        Post::create([
            'title'         => 'Also...',
            'content'       => '<p>you can save (or paste, if your browser allows it) bookmarks ! ➡️</p>',
            'type'          => self::POST_TYPE_TEXT,
            'description'   => null,
            'collection_id' => $collection_id,
            'user_id'       => $user_id,
            'order'         => $i++
        ]);

        Post::create([
            'title'         => 'Text Post 📝',
            'content'       => '<p>This post demonstrates different features of Benotes. <br>' .
            'You can write <strong>bold</strong>, ' .
            '<em>italic</em> or <strong>combine <em>them</em></strong><em>.</em></p>' .
            '<blockquote><p>Blockquotes are also a thing</p></blockquote>' .
            '<hr> <p>You can also use Markdown in order to type faster. <br>' .
            'If you are not familiar with the syntax have a look at</p>' .
            '<unfurling-link href="https://www.markdownguide.org/cheat-sheet/" ' .
            'data-title="Markdown Cheat Sheet | Markdown Guide"></unfurling-link>',
            'type'          => self::POST_TYPE_TEXT,
            'description'   => null,
            'collection_id' => $collection_id,
            'user_id'       => $user_id,
            'order'         => $i++
        ]);
    }
}
