<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\ResetsUserPasswords;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Nueva contraseña')]
class ResetPassword extends Component
{
    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->string('email')->toString();
    }

    public function resetPassword(ResetsUserPasswords $resetsPasswords): void
    {
        $this->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed'],
        ]);

        $status = Password::reset(
            [
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token' => $this->token,
            ],
            function (User $user) use ($resetsPasswords): void {
                $resetsPasswords->reset($user, [
                    'password' => $this->password,
                    'password_confirmation' => $this->password_confirmation,
                ]);

                Auth::login($user);
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => 'No hemos podido restablecer la contraseña. El enlace puede haber caducado.',
            ]);
        }

        Session::regenerate();

        $this->redirect(config('fortify.home'), navigate: true);
    }
}
