<?php

namespace App\Services;

use App\Exceptions\TransactionException;
use App\Models\Tag;
use App\Repositories\Contracts\TagRepositoryInterface;
use DB;
use Illuminate\Database\Eloquent\Model;
use Throwable;

readonly class TagService
{
    public function __construct(
        protected TagRepositoryInterface $repository,
    )
    {
        //
    }

    /**
     * @param string $name
     * @param int $user_id
     * @return Tag|Model|null
     */
    public function create(string $name, int $user_id): Tag|Model|null
    {
        return Tag::query()->firstOrCreate([
            'name' => $name,
            'user_id' => $user_id
        ], []);
    }

    /**
     * @param array $tags
     * @param int $user_id
     * @return array
     * @throws TransactionException
     */
    public function createMany(array $tags, int $user_id): array
    {
        try {
            return DB::transaction(function () use ($tags, $user_id) {
                $result = [];

                foreach ($tags as $tag_request_object) {
                    $tag = $this->create($tag_request_object['name'], $user_id);

                    if ($tag) {
                        $result[] = $tag;
                    }
                }

                return $result;
            });
        } catch (Throwable $e) {
            throw TransactionException::error($e);
        }
    }
}
