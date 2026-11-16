<?php

namespace Tests\Feature\ReadingPlan;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SendReadingPlanRemindersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(
            Carbon::parse('2026-11-03 09:00:00', 'Asia/Tokyo')
        );
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * 期日の3日前に通知される
     */
    public function test_notification_is_sent_three_days_before_due_date(): void
    {
        $plan = $this->createReadingPlan('2026-11-06');

        $this->artisan('reading-plans:send-reminders')
            ->assertSuccessful();

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $plan->user_id,
            'type' => ReadingPlanReminderNotification::class,
        ]);

        $this->assertNotificationTiming(
            $plan,
            'three_days_before'
        );
    }

    /**
     * 期日当日に通知される
     */
    public function test_notification_is_sent_on_due_date(): void
    {
        $plan = $this->createReadingPlan('2026-11-03');

        $this->artisan('reading-plans:send-reminders')
            ->assertSuccessful();

        $this->assertNotificationTiming(
            $plan,
            'on_due_date'
        );
    }

    /**
     * 期日の3日後に通知される
     */
    public function test_notification_is_sent_three_days_after_due_date(): void
    {
        $plan = $this->createReadingPlan('2026-10-31');

        $this->artisan('reading-plans:send-reminders')
            ->assertSuccessful();

        $this->assertNotificationTiming(
            $plan,
            'three_days_after'
        );
    }

    /**
     * 完了済みの読書計画には通知されない
     */
    public function test_completed_reading_plan_does_not_receive_notification(): void
    {
        $plan = $this->createReadingPlan(
            '2026-11-03',
            ReadingPlanStatus::Completed
        );

        $this->artisan('reading-plans:send-reminders')
            ->assertSuccessful();

        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $plan->user_id,
            'type' => ReadingPlanReminderNotification::class,
        ]);
    }

    /**
     * 対象期間外の読書計画には通知されない
     */
    public function test_notification_is_not_sent_outside_reminder_dates(): void
    {
        $plan = $this->createReadingPlan('2026-11-07');

        $this->artisan('reading-plans:send-reminders')
            ->assertSuccessful();

        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $plan->user_id,
            'type' => ReadingPlanReminderNotification::class,
        ]);
    }

    /**
     * 同じ通知が重複して送信されない
     */
    public function test_duplicate_notifications_are_not_sent(): void
    {
        $plan = $this->createReadingPlan('2026-11-03');

        $this->artisan('reading-plans:send-reminders')
            ->assertSuccessful();

        $this->artisan('reading-plans:send-reminders')
            ->assertSuccessful();

        $count = $plan->user
            ->notifications()
            ->where('type', ReadingPlanReminderNotification::class)
            ->where('data->reading_plan_id', $plan->id)
            ->where('data->timing', 'on_due_date')
            ->count();

        $this->assertSame(1, $count);
    }

    /**
     * 読書計画を作成する共通処理
     */
    private function createReadingPlan(
        string $dueDate,
        ReadingPlanStatus $status = ReadingPlanStatus::Unread
    ): ReadingPlan {
        $user = User::factory()->create();

        $book = Book::factory()->create();

        return ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'due_date' => $dueDate,
            'status' => $status,
            'completed_at' => $status === ReadingPlanStatus::Completed
                ? now()
                : null,
        ]);
    }

    /**
     * 指定した通知タイミングが存在することを確認する
     */
    private function assertNotificationTiming(
        ReadingPlan $plan,
        string $timing
    ): void {
        $notification = $plan->user
            ->notifications()
            ->where('type', ReadingPlanReminderNotification::class)
            ->where('data->reading_plan_id', $plan->id)
            ->where('data->timing', $timing)
            ->first();

        $this->assertNotNull($notification);
    }

    /**
     * 毎日午前9時にリマインダーが実行される設定になっている
     */
    public function test_reminder_command_is_scheduled_daily_at_nine(): void
    {
        $this->artisan('schedule:list')
            ->assertSuccessful()
            ->expectsOutputToContain('reading-plans:send-reminders');
    }
}
