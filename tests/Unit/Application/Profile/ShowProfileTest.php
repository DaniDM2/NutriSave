<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Profile;

use App\Application\Profile\CreateProfile;
use App\Application\Profile\ShowProfile;
use App\Domain\Shared\ValueObjects\UserId;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\InMemoryProfileRepository;

class ShowProfileTest extends TestCase
{
    public function test_it_returns_null_when_the_user_has_no_profile(): void
    {
        $view = (new ShowProfile(new InMemoryProfileRepository))->handle(new UserId(1));

        $this->assertNull($view);
    }

    public function test_it_returns_a_view_when_the_user_has_a_profile(): void
    {
        $profiles = new InMemoryProfileRepository;
        $userId = new UserId(1);
        $created = (new CreateProfile($profiles))->handle(
            $userId,
            'female',
            '1990-05-15',
            165,
            'active',
            ['Swimming', 'Yoga'],
            'lose',
            14,
        );

        $view = (new ShowProfile($profiles))->handle($userId);

        $this->assertNotNull($view);
        $this->assertSame($created->id?->value, $view->id->value);
        $this->assertSame('female', $view->sex);
        $this->assertSame('1990-05-15', $view->birthDate);
        $this->assertSame(165, $view->heightCm);
        $this->assertSame('active', $view->activityLevel);
        $this->assertSame(['Swimming', 'Yoga'], $view->sports);
        $this->assertSame('lose', $view->goal);
        $this->assertSame(14, $view->cadence);
    }
}
