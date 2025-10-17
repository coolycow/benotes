<?php

namespace App\Enums;

enum UserPermissionEnum: string
{
    case Admin = 'admin';
    case Api = 'api';
    case Share = 'share';
}
