<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(): User
    {
        return User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'role' => 'general',
            'email_verified_at' => now(),
        ]);
    }

    public function test_自分が行った勤怠情報が全て表示されている()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        Carbon::setTestNow(Carbon::create(2026, 4, 15));

        $user = $this->createUser();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-10',
            'clock_in_at' => '2026-04-10 09:00:00',
            'clock_out_at' => '2026-04-10 18:00:00',
            'note' => '通常勤務',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-11',
            'clock_in_at' => '2026-04-11 10:00:00',
            'clock_out_at' => '2026-04-11 19:00:00',
            'note' => '通常勤務',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('04/10');
        $response->assertSee('04/11');

        Carbon::setTestNow();
    }

    public function test_勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        Carbon::setTestNow(Carbon::create(2026, 4, 15));

        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('2026-04');

        Carbon::setTestNow();
    }

    public function test_前月を押下した時に表示月の前月の情報が表示される()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        $user = $this->createUser();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-15',
            'clock_in_at' => '2026-03-15 09:00:00',
            'clock_out_at' => '2026-03-15 18:00:00',
            'note' => '通常勤務',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-03');

        $response->assertStatus(200);
        $response->assertSee('2026-03');
        $response->assertSee('03/15');
    }

    public function test_翌月を押下した時に表示月の翌月の情報が表示される()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        $user = $this->createUser();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-05-10',
            'clock_in_at' => '2026-05-10 09:00:00',
            'clock_out_at' => '2026-05-10 18:00:00',
            'note' => '通常勤務',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-05');

        $response->assertStatus(200);
        $response->assertSee('2026-05');
        $response->assertSee('05/10');
    }

    public function test_詳細ボタンを押下するとその日の勤怠詳細画面に遷移する()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-15',
            'clock_in_at' => '2026-04-15 09:00:00',
            'clock_out_at' => '2026-04-15 18:00:00',
            'note' => '通常勤務',
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee('2026年');
        $response->assertSee('4月15日');
    }
}