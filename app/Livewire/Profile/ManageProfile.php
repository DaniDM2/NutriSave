<?php

declare(strict_types=1);

namespace App\Livewire\Profile;

use App\Application\Profile\CreateProfile;
use App\Application\Profile\ShowProfile;
use App\Application\Profile\UpdateProfile;
use App\Domain\Profile\Exceptions\DuplicateSport;
use App\Domain\Profile\Exceptions\InvalidBirthDate;
use App\Domain\Profile\Exceptions\InvalidHeight;
use App\Domain\Profile\Exceptions\InvalidSportName;
use App\Domain\Profile\Exceptions\ProfileNotFound;
use App\Domain\Profile\Exceptions\UserAlreadyHasProfile;
use App\Domain\Shared\ValueObjects\UserId;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Perfil')]
class ManageProfile extends Component
{
    public string $sex = '';

    public string $birthDate = '';

    public ?int $heightCm = null;

    public string $activityLevel = '';

    /** @var list<string> */
    public array $sports = [];

    public string $sportDraft = '';

    public string $goal = '';

    public ?int $cadence = null;

    public string $status = '';

    public bool $hasProfile = false;

    public function mount(ShowProfile $show): void
    {
        $this->hydrateFrom($show);
    }

    public function addSport(): void
    {
        $name = trim($this->sportDraft);

        if ($name === '') {
            return;
        }

        $this->validate([
            'sportDraft' => ['required', 'string', 'max:50'],
        ]);

        $this->sports[] = $name;
        $this->sportDraft = '';
    }

    public function removeSport(int $index): void
    {
        unset($this->sports[$index]);
        $this->sports = array_values($this->sports);
    }

    public function save(CreateProfile $create, UpdateProfile $update, ShowProfile $show): void
    {
        $this->validate([
            'sex' => ['required', 'in:male,female'],
            'birthDate' => ['required', 'date', 'after_or_equal:1900-01-01', 'before:today'],
            'heightCm' => ['required', 'integer', 'min:50', 'max:250'],
            'activityLevel' => ['required', 'in:sedentary,light,moderate,active,very_active'],
            'sports' => ['array'],
            'sports.*' => ['string', 'max:50'],
            'goal' => ['required', 'in:lose,maintain,gain'],
            'cadence' => ['required', 'integer', 'in:7,14,30'],
        ]);

        $userId = $this->userId();
        $sports = array_values($this->sports);
        $heightCm = (int) $this->heightCm;
        $cadence = (int) $this->cadence;

        try {
            if ($this->hasProfile) {
                $update->handle(
                    $userId,
                    $this->sex,
                    $this->birthDate,
                    $heightCm,
                    $this->activityLevel,
                    $sports,
                    $this->goal,
                    $cadence,
                );
                $this->status = 'Perfil actualizado.';
            } else {
                $create->handle(
                    $userId,
                    $this->sex,
                    $this->birthDate,
                    $heightCm,
                    $this->activityLevel,
                    $sports,
                    $this->goal,
                    $cadence,
                );
                $this->status = 'Perfil guardado.';
            }

            $this->hydrateFrom($show);
        } catch (UserAlreadyHasProfile) {
            $this->status = 'Ya tienes un perfil.';
            $this->hydrateFrom($show);
        } catch (ProfileNotFound) {
            $this->status = 'No encontramos tu perfil.';
        } catch (InvalidHeight) {
            $this->addError('heightCm', 'La altura no es válida.');
        } catch (InvalidBirthDate) {
            $this->addError('birthDate', 'La fecha de nacimiento no es válida.');
        } catch (InvalidSportName) {
            $this->addError('sportDraft', 'El nombre del deporte no es válido.');
        } catch (DuplicateSport) {
            $this->addError('sports', 'No puedes repetir un deporte.');
        }
    }

    private function hydrateFrom(ShowProfile $show): void
    {
        $view = $show->handle($this->userId());
        $this->hasProfile = $view !== null;

        if ($view === null) {
            return;
        }

        $this->sex = $view->sex;
        $this->birthDate = $view->birthDate;
        $this->heightCm = $view->heightCm;
        $this->activityLevel = $view->activityLevel;
        $this->sports = $view->sports;
        $this->goal = $view->goal;
        $this->cadence = $view->cadence;
    }

    private function userId(): UserId
    {
        return new UserId((int) Auth::id());
    }
}
