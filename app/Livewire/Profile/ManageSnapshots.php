<?php

declare(strict_types=1);

namespace App\Livewire\Profile;

use App\Application\Profile\BodySnapshotView;
use App\Application\Profile\ListBodySnapshots;
use App\Application\Profile\RecordBodySnapshot;
use App\Application\Profile\ShowSnapshotStatus;
use App\Domain\Profile\Exceptions\InvalidCompositionPercent;
use App\Domain\Profile\Exceptions\InvalidWeight;
use App\Domain\Profile\Exceptions\ProfileNotFound;
use App\Domain\Shared\ValueObjects\UserId;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Peso')]
class ManageSnapshots extends Component
{
    public ?float $weightKg = null;

    public ?float $bodyFat = null;

    public ?float $muscle = null;

    public ?float $water = null;

    public string $status = '';

    public bool $hasProfile = false;

    public bool $isStale = false;

    public ?int $cadenceDays = null;

    public ?int $daysSinceRecorded = null;

    /**
     * @var list<array{id: int, weightKg: float, bmi: float, bodyFat: ?float, muscle: ?float, water: ?float, recordedAt: string}>
     */
    public array $snapshots = [];

    public function mount(ShowSnapshotStatus $status, ListBodySnapshots $list): void
    {
        $this->hydrateFrom($status, $list);
    }

    public function save(RecordBodySnapshot $record, ShowSnapshotStatus $status, ListBodySnapshots $list): void
    {
        $this->validate([
            'weightKg' => ['required', 'numeric', 'min:20', 'max:400'],
            'bodyFat' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'muscle' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'water' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        try {
            $record->handle(
                $this->userId(),
                (float) $this->weightKg,
                $this->bodyFat,
                $this->muscle,
                $this->water,
            );
            $this->status = 'Peso registrado.';
            $this->weightKg = null;
            $this->bodyFat = null;
            $this->muscle = null;
            $this->water = null;
            $this->hydrateFrom($status, $list);
        } catch (ProfileNotFound) {
            $this->status = 'Completa tu perfil para registrar el peso.';
            $this->hydrateFrom($status, $list);
        } catch (InvalidWeight) {
            $this->addError('weightKg', 'El peso no es válido.');
        } catch (InvalidCompositionPercent) {
            $this->addError('bodyFat', 'El porcentaje no es válido.');
        }
    }

    private function hydrateFrom(ShowSnapshotStatus $status, ListBodySnapshots $list): void
    {
        $userId = $this->userId();
        $view = $status->handle($userId);
        $this->hasProfile = $view->hasProfile;
        $this->isStale = $view->isStale;
        $this->cadenceDays = $view->cadenceDays;
        $this->daysSinceRecorded = $view->daysSinceRecorded;
        $this->snapshots = array_map(
            fn (BodySnapshotView $snapshot): array => [
                'id' => $snapshot->id->value,
                'weightKg' => $snapshot->weightKg,
                'bmi' => $snapshot->bmi,
                'bodyFat' => $snapshot->bodyFat,
                'muscle' => $snapshot->muscle,
                'water' => $snapshot->water,
                'recordedAt' => $snapshot->recordedAt->format('d/m/Y H:i'),
            ],
            $list->handle($userId),
        );
    }

    private function userId(): UserId
    {
        return new UserId((int) Auth::id());
    }
}
