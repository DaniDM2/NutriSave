<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Application\Profile\ShowProfile;
use App\Domain\Shared\ValueObjects\UserId;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Inicio')]
class Dashboard extends Component
{
    public bool $hasProfile = false;

    public function mount(ShowProfile $show): void
    {
        $this->hasProfile = $show->handle(new UserId((int) Auth::id())) !== null;
    }
}
