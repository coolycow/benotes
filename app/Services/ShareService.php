<?php

namespace App\Services;

use App\Enums\SharePermissionEnum;
use App\Models\Share;

class ShareService
{
    public function create(
        int $userId,
        int $collectionId,
        string $token,
        bool $isActive = false,
        SharePermissionEnum $permission = SharePermissionEnum::Read
    ): Share
    {
        return Share::query()->create([
            'user_id' => $userId,
            'collection_id' => $collectionId,
            'token' => $token,
            'is_active' => $isActive,
            'permission' => $permission
        ]);
    }
}
