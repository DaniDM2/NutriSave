<?php

declare(strict_types=1);

namespace App\Livewire\Household;

use App\Application\Household\AcceptHouseholdInvitation;
use App\Application\Household\PreviewHouseholdInvitation;
use App\Domain\Household\Exceptions\DuplicateHouseholdMember;
use App\Domain\Household\Exceptions\InvitationEmailMismatch;
use App\Domain\Household\Exceptions\InvitationExpired;
use App\Domain\Household\Exceptions\InvitationNotFound;
use App\Domain\Household\Exceptions\UserAlreadyInHousehold;
use App\Domain\Shared\ValueObjects\UserId;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Invitación al hogar')]
class AcceptInvitation extends Component
{
    public string $token = '';

    public string $householdName = '';

    public string $email = '';

    public bool $expired = false;

    public bool $found = false;

    public string $status = '';

    public function mount(string $token, PreviewHouseholdInvitation $preview): void
    {
        $this->token = $token;

        if (Auth::guest()) {
            Session::put('url.intended', route('household.invitations.accept', $token));
        }

        try {
            $invitation = $preview->handle($token);
            $this->found = true;
            $this->householdName = $invitation->householdName;
            $this->email = $invitation->email;
            $this->expired = $invitation->expired;

            if ($this->expired) {
                $this->status = 'Esta invitación ha caducado.';
            }
        } catch (InvitationNotFound) {
            $this->found = false;
            $this->status = 'Esta invitación no es válida o ha caducado.';
        }
    }

    public function join(AcceptHouseholdInvitation $accept): void
    {
        if (Auth::guest()) {
            $this->redirect(route('login', ['email' => $this->email]), navigate: true);

            return;
        }

        try {
            $accept->handle(new UserId((int) Auth::id()), $this->token);
            $this->redirect(route('household'), navigate: true);
        } catch (InvitationNotFound) {
            $this->found = false;
            $this->status = 'Esta invitación no es válida o ha caducado.';
        } catch (InvitationExpired) {
            $this->expired = true;
            $this->status = 'Esta invitación ha caducado.';
        } catch (InvitationEmailMismatch) {
            $this->status = "Debes entrar con el correo {$this->email}.";
        } catch (UserAlreadyInHousehold) {
            $this->status = 'Ya perteneces a un hogar.';
        } catch (DuplicateHouseholdMember) {
            $this->status = 'Ya formas parte de este hogar.';
        }
    }
}
