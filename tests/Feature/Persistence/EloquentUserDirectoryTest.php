<?php

declare(strict_types=1);

namespace Tests\Feature\Persistence;

use App\Domain\Shared\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\EloquentUserDirectory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EloquentUserDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_finds_a_user_by_email_case_insensitively(): void
    {
        $user = User::factory()->create([
            'name' => 'Ada Lovelace',
            'email' => 'Ada@Example.com',
        ]);

        $record = (new EloquentUserDirectory)->findByEmail('ada@example.com');

        $this->assertNotNull($record);
        $this->assertSame((int) $user->id, $record->id->value);
        $this->assertSame('Ada Lovelace', $record->name);
        $this->assertSame('Ada@Example.com', $record->email);
    }

    public function test_it_returns_null_for_an_unknown_email(): void
    {
        $this->assertNull((new EloquentUserDirectory)->findByEmail('missing@example.com'));
    }

    public function test_it_finds_users_by_ids(): void
    {
        $ada = User::factory()->create(['name' => 'Ada']);
        $bob = User::factory()->create(['name' => 'Bob']);
        User::factory()->create(['name' => 'Carol']);

        $records = (new EloquentUserDirectory)->findByIds([
            new UserId((int) $ada->id),
            new UserId((int) $bob->id),
        ]);

        $this->assertCount(2, $records);
        $this->assertSame('Ada', $records[(int) $ada->id]->name);
        $this->assertSame('Bob', $records[(int) $bob->id]->name);
    }
}
