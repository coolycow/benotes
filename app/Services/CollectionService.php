<?php

namespace App\Services;

use App\Exceptions\TransactionException;
use App\Repositories\Contracts\CollectionRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Models\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class CollectionService
{
    /**
     * @param CollectionRepositoryInterface $repository
     */
    public function __construct(
        protected CollectionRepositoryInterface $repository
    )
    {
        //
    }

    /**
     * @param string $name
     * @param int $user_id
     * @param int|null $parent_collection_id
     * @param int|null $icon_id
     * @return Collection
     */
    public function store(string $name, int $user_id, ?int $parent_collection_id = null, ?int $icon_id = null): Collection
    {
        return Collection::query()->create([
            'name' => $name,
            'user_id' => $user_id,
            'parent_id' => $parent_collection_id,
            'icon_id' => $icon_id
        ]);
    }

    /**
     * @param int $id
     * @param string $name
     * @param bool $is_root
     * @param int|null $parent_collection_id
     * @param int|null $icon_id
     * @return Collection
     * @throws ModelNotFoundException
     */
    public function update(int $id, string $name, bool $is_root, ?int $parent_collection_id = null, ?int $icon_id = null): Collection
    {
        $attributes = collect([
            'name' => $name,
            'icon_id' => $icon_id
        ])->filter()->all();

        $attributes['parent_id'] = $is_root ? null : $parent_collection_id;

        $collection = $this->repository->getById($id);

        if (!$collection) {
            throw new ModelNotFoundException(
                'Collection not found',
            );
        }

        $collection->update($attributes);

        return $collection;
    }

    /**
     * @param int $id
     * @param bool $is_nested
     * @param int $user_id
     * @return bool
     * @throws TransactionException
     */
    public function delete(int $id, bool $is_nested, int $user_id): bool
    {
        try {
            return DB::transaction(function () use ($id, $is_nested, $user_id) {
                if ($is_nested) {
                    Collection::query()
                        ->where('user_id', $user_id)
                        ->where('parent_id', $id)->delete();
                }

                return $this->repository->getById($id)->delete();
            });
        } catch (Throwable $e) {
            throw TransactionException::error($e);
        }
    }
}
