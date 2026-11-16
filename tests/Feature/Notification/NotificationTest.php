<?php

namespace Tests\Feature\Notification;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ログイン済みユーザーが通知一覧を表示できる
     */
    public function test_user_can_view_notifications(): void
    {
        $user = User::factory()->create();

        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'test',
            'data' => [
                'title' => 'テスト通知',
                'body' => '通知内容',
                'timing' => 'on_due_date',
            ],
        ]);

        $response = $this->actingAs($user)
            ->get(route('notifications.index'));

        $response->assertStatus(200);
        $response->assertSee('テスト通知');
    }

    /**
     * 他のユーザーの通知が表示されない
     */
    public function test_user_cannot_view_other_users_notifications(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $otherUser->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'test',
            'data' => [
                'title' => '他人の通知',
                'body' => '他人の通知内容',
            ],
        ]);

        $response = $this->actingAs($user)
            ->get(route('notifications.index'));

        $response->assertStatus(200);
        $response->assertDontSee('他人の通知');
    }

    /**
     * 未ログインユーザーは通知一覧を表示できない
     */
    public function test_guest_cannot_view_notifications(): void
    {
        $response = $this->get(route('notifications.index'));

        $response->assertRedirect('/login');
    }

    /**
     * 自分の通知を既読にできる
     */
    public function test_user_can_mark_own_notification_as_read(): void
    {
        $user = User::factory()->create();

        $notification = $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'test',
            'data' => [
                'title' => 'テスト通知',
                'body' => '通知内容',
            ],
        ]);

        $response = $this->actingAs($user)
            ->post(route('notifications.read', $notification->id));

        $response->assertRedirect(route('notifications.index'));

        $this->assertNotNull(
            $notification->fresh()->read_at
        );
    }

    /**
     * 他のユーザーの通知を既読にできない
     */
    public function test_user_cannot_mark_other_users_notification_as_read(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $notification = $otherUser->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'test',
            'data' => [
                'title' => '他人の通知',
                'body' => '通知内容',
            ],
        ]);

        $response = $this->actingAs($user)
            ->post(route('notifications.read', $notification->id));

        $response->assertNotFound();

        $this->assertNull(
            $notification->fresh()->read_at
        );
    }

    /**
     * 未ログインユーザーは通知を既読にできない
     */
    public function test_guest_cannot_mark_notification_as_read(): void
    {
        $user = User::factory()->create();

        $notification = $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'test',
            'data' => [
                'title' => 'テスト通知',
                'body' => '通知内容',
            ],
        ]);

        $response = $this->post(
            route('notifications.read', $notification->id)
        );

        $response->assertRedirect('/login');
    }
}
