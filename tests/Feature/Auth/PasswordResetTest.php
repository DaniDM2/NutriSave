<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\ResetPassword;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_the_forgot_password_page(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Restablecer contraseña');
    }

    public function test_it_sends_a_password_reset_link(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        Livewire::test(ForgotPassword::class)
            ->set('email', $user->email)
            ->call('sendResetLink')
            ->assertHasNoErrors()
            ->assertSet('status', 'Si el correo existe, te enviaremos un enlace para restablecer la contraseña.');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_it_does_not_reveal_whether_the_email_exists(): void
    {
        Notification::fake();

        Livewire::test(ForgotPassword::class)
            ->set('email', 'missing@example.com')
            ->call('sendResetLink')
            ->assertHasNoErrors()
            ->assertSet('status', 'Si el correo existe, te enviaremos un enlace para restablecer la contraseña.');

        Notification::assertNothingSent();
    }

    public function test_it_renders_the_reset_password_page(): void
    {
        $this->get(route('password.reset', [
            'token' => 'fake-token',
            'email' => 'ada@example.com',
        ]))
            ->assertOk()
            ->assertSee('Nueva contraseña')
            ->assertSee('ada@example.com');
    }

    public function test_it_resets_the_password_with_a_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        Livewire::test(ForgotPassword::class)
            ->set('email', $user->email)
            ->call('sendResetLink');

        Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notification) use ($user): bool {
            Livewire::test(ResetPassword::class, ['token' => $notification->token])
                ->set('email', $user->email)
                ->set('password', 'new-password')
                ->set('password_confirmation', 'new-password')
                ->call('resetPassword')
                ->assertHasNoErrors()
                ->assertRedirect(config('fortify.home'));

            $this->assertAuthenticatedAs($user);

            return true;
        });
    }
}
