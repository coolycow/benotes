<?php

namespace App\Enums;

enum UserPermissionEnum: int
{
    case Admin = 255;

    case Api = 1;
    case Share = 2;
    case Unauthorized = 0;
}
