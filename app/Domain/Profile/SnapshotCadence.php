<?php

declare(strict_types=1);

namespace App\Domain\Profile;

enum SnapshotCadence: int
{
    case Weekly = 7;
    case Biweekly = 14;
    case Monthly = 30;
}
