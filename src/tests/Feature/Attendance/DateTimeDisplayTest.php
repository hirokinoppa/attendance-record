<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DateTimeDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_現在の日時情報が画面に表示される()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        Carbon::setTestNow(Carbon::create(2026, 4, 13, 10, 30, 0));

        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'general',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);

        $response->assertSee('2026年4月13日');
        $response->assertSee('10:30');

        Carbon::setTestNow();
    }
}