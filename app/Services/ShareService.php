<?php

namespace App\Services;

use App\Enums\SharePermissionEnum;
use App\Exceptions\TransactionException;
use App\Models\Collection;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class ShareService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
    )
    {
        //
    }

    /**
     * @param Collection $collection
     * @param array $guests
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws TransactionException
     */
    public function updateOrCreateMany(Collection $collection, array $guests): \Illuminate\Database\Eloquent\Collection
    {
        try {
            return DB::transaction(function () use ($collection, $guests) {
                $guestIds = array_column($guests, 'guest_id');

                if (!$guestIds) {
                    $collection->shares()->delete();
                } else {
                    $collection->shares()->whereNotIn('id', $guestIds)->delete();

                    foreach ($guests as $guest) {
                        $user = $this->userRepository->getById($guest['guest_id']);
                        $permission = SharePermissionEnum::tryFrom($guest['permission']);

                        if ($user && $permission) {
                            $collection->shares()->updateOrCreate(
                                [
                                    'user_id' => $collection->user_id,
                                    'guest_id' => $user->getKey(),
                                ],
                                [
                                    'permission' => $permission,
                                ]
                            );
                        }
                    }
                }

                return $collection->shares()->get();
            });
        } catch (Throwable $e) {
            throw TransactionException::error($e);
        }
    }
}
