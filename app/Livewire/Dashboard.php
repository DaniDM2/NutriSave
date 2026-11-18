<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Inicio')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.dashboard');
    }
}
