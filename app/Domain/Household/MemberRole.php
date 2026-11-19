<?php

declare(strict_types=1);

namespace App\Domain\Household;

enum MemberRole: string
{
    case Owner = 'owner';
    case Member = 'member';
}
