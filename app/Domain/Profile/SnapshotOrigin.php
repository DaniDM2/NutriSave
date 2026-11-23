<?php

declare(strict_types=1);

namespace App\Domain\Profile;

enum SnapshotOrigin: string
{
    case Manual = 'manual';
    case ScaleAi = 'scale_ai';
}
