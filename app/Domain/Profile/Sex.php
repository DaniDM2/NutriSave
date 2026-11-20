<?php

declare(strict_types=1);

namespace App\Domain\Profile;

enum Sex: string
{
    case Male = 'male';
    case Female = 'female';
}
