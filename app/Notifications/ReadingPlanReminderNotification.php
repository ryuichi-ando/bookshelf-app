<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingPlanReminderNotification extends Notification
{
    use Queueable;

    protected ReadingPlan $plan;
    protected string $timing;
    /**
     * Create a new notification instance.
     */
    public function __construct(ReadingPlan $plan, string $timing)
    {
        $this->plan = $plan;
        $this->timing = $timing;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    /** * 通知の保存先 */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** * データベースに保存する通知内容 */
    public function toDatabase(object $notifiable): array
    {
        $messages = [
            'three_days_before' => ['title' => '読書計画の期日が近づいています', 'body' => '「' . $this->plan->book->title . '」の期日まであと3日です。',],
            'on_due_date' => ['title' => '読書計画の期日です', 'body' => '「' . $this->plan->book->title . '」の期日が本日です。',],
            'three_days_after' => ['title' => '読書計画の期日を過ぎています', 'body' => '「' . $this->plan->book->title . '」の期日を3日過ぎています。',],
        ];
        return [
            'reading_plan_id' => $this->plan->id,
            'timing' => $this->timing,
            'title' => $messages[$this->timing]['title'],
            'body' => $messages[$this->timing]['body'],
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
