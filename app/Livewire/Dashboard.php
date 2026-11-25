<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Application\Profile\ShowSnapshotStatus;
use App\Domain\Shared\ValueObjects\UserId;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Inicio')]
class Dashboard extends Component
{
    public bool $hasProfile = false;

    public bool $hasSnapshot = false;

    public bool $isStale = false;

    public ?int $cadenceDays = null;

    public ?int $daysSinceRecorded = null;

    public function mount(ShowSnapshotStatus $status): void
    {
        $view = $status->handle(new UserId((int) Auth::id()));
        $this->hasProfile = $view->hasProfile;
        $this->hasSnapshot = $view->hasSnapshot;
        $this->isStale = $view->isStale;
        $this->cadenceDays = $view->cadenceDays;
        $this->daysSinceRecorded = $view->daysSinceRecorded;
    }
}
