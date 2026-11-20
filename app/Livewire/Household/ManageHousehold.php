<?php

declare(strict_types=1);

namespace App\Livewire\Household;

use App\Application\Household\CreateHousehold;
use App\Application\Household\HouseholdMemberView;
use App\Application\Household\InviteHouseholdMember;
use App\Application\Household\PendingInvitationView;
use App\Application\Household\ShowHousehold;
use App\Domain\Household\Exceptions\CannotInviteSelf;
use App\Domain\Household\Exceptions\DuplicateHouseholdMember;
use App\Domain\Household\Exceptions\InvalidHouseholdName;
use App\Domain\Household\Exceptions\InvalidInvitationEmail;
use App\Domain\Household\Exceptions\NotHouseholdOwner;
use App\Domain\Household\Exceptions\UserAlreadyInHousehold;
use App\Domain\Shared\ValueObjects\UserId;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Hogar')]
class ManageHousehold extends Component
{
    public string $name = '';

    public string $inviteEmail = '';

    public string $status = '';

    public bool $hasHousehold = false;

    public bool $isOwner = false;

    public string $householdName = '';

    /** @var list<array{name: string, email: string, role: string}> */
    public array $members = [];

    /** @var list<string> */
    public array $pendingEmails = [];

    public function mount(ShowHousehold $show): void
    {
        $this->hydrateFrom($show);
    }

    public function create(CreateHousehold $create, ShowHousehold $show): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        try {
            $create->handle($this->userId(), $this->name);
            $this->status = 'Hogar creado.';
            $this->hydrateFrom($show);
        } catch (InvalidHouseholdName) {
            $this->addError('name', 'El nombre no es válido.');
        } catch (UserAlreadyInHousehold) {
            $this->status = 'Ya perteneces a un hogar.';
            $this->hydrateFrom($show);
        }
    }

    public function invite(InviteHouseholdMember $invite, ShowHousehold $show): void
    {
        $this->validate([
            'inviteEmail' => ['required', 'string', 'email'],
        ]);

        try {
            $invite->handle($this->userId(), $this->inviteEmail);
            $this->inviteEmail = '';
            $this->status = 'Invitación enviada.';
            $this->hydrateFrom($show);
        } catch (NotHouseholdOwner) {
            $this->status = 'Solo el titular puede invitar.';
        } catch (CannotInviteSelf) {
            $this->addError('inviteEmail', 'No puedes invitarte a ti mismo.');
        } catch (DuplicateHouseholdMember) {
            $this->addError('inviteEmail', 'Esa persona ya está en el hogar.');
        } catch (UserAlreadyInHousehold) {
            $this->addError('inviteEmail', 'Esa persona ya pertenece a otro hogar.');
        } catch (InvalidInvitationEmail) {
            $this->addError('inviteEmail', 'El correo no es válido.');
        }
    }

    private function hydrateFrom(ShowHousehold $show): void
    {
        $view = $show->handle($this->userId());
        $this->hasHousehold = $view !== null;

        if ($view === null) {
            $this->isOwner = false;
            $this->householdName = '';
            $this->members = [];
            $this->pendingEmails = [];

            return;
        }

        $this->isOwner = $view->isOwner;
        $this->householdName = $view->name;
        $this->name = $view->name;
        $this->members = array_map(
            fn (HouseholdMemberView $member): array => [
                'name' => $member->name,
                'email' => $member->email,
                'role' => $member->role->value,
            ],
            $view->members,
        );
        $this->pendingEmails = array_map(
            fn (PendingInvitationView $invitation): string => $invitation->email,
            $view->pendingInvitations,
        );
    }

    private function userId(): UserId
    {
        return new UserId((int) Auth::id());
    }
}
