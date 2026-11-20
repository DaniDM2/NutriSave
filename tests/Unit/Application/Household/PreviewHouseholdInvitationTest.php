<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Household;

use App\Application\Household\PreviewHouseholdInvitation;
use App\Domain\Household\Exceptions\InvitationNotFound;
use App\Domain\Household\ValueObjects\Household;
use App\Domain\Household\ValueObjects\HouseholdInvitation;
use App\Domain\Household\ValueObjects\HouseholdName;
use App\Domain\Shared\ValueObjects\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\InMemoryHouseholdInvitationRepository;
use Tests\Fakes\InMemoryHouseholdRepository;

class PreviewHouseholdInvitationTest extends TestCase
{
    public function test_it_previews_a_valid_invitation(): void
    {
        [$preview] = $this->pendingInvitation();

        $view = $preview->handle('plain-token');

        $this->assertSame('Casa Ada', $view->householdName);
        $this->assertSame('carol@example.com', $view->email);
        $this->assertFalse($view->expired);
    }

    public function test_it_marks_an_expired_invitation(): void
    {
        [$preview] = $this->pendingInvitation(expired: true);

        $view = $preview->handle('plain-token');

        $this->assertTrue($view->expired);
    }

    public function test_it_rejects_an_unknown_token(): void
    {
        [$preview] = $this->pendingInvitation();

        $this->expectException(InvitationNotFound::class);

        $preview->handle('missing');
    }

    /**
     * @return array{0: PreviewHouseholdInvitation}
     */
    private function pendingInvitation(bool $expired = false): array
    {
        $households = new InMemoryHouseholdRepository;
        $invitations = new InMemoryHouseholdInvitationRepository;
        $ownerId = new UserId(1);

        $household = $households->save(Household::create(new HouseholdName('Casa Ada'), $ownerId));
        $this->assertNotNull($household->id);

        $invitations->save(new HouseholdInvitation(
            $household->id,
            'carol@example.com',
            $ownerId,
            HouseholdInvitation::hashToken('plain-token'),
            $expired ? new DateTimeImmutable('-1 hour') : new DateTimeImmutable('+7 days'),
        ));

        return [new PreviewHouseholdInvitation($households, $invitations)];
    }
}
