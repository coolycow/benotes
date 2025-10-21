<?php

namespace App\Enums;

enum SharePermissionEnum: int
{
    case Read = 1;
    case ReadAndWrite = 2;
    case ReadAndWriteAndDelete = 3;
}
