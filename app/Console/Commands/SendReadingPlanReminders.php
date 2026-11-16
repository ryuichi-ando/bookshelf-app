<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Console\Command;

class SendReadingPlanReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reading-plans:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '読書計画のリマインダー通知を送信する';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = today();
        // 通知対象となる読書計画を取得 
        $plans = ReadingPlan::with(['user', 'book'])->whereIn('status', [ReadingPlanStatus::Unread->value, ReadingPlanStatus::Reading->value,])->whereIn('due_date', [$today->copy()->addDays(3)->toDateString(), $today->toDateString(), $today->copy()->subDays(3)->toDateString(),])->get();
        foreach ($plans as $plan) {
            $dueDate = $plan->due_date->toDateString();
            // 通知タイミングを判定 
            if ($dueDate === $today->copy()->addDays(3)->toDateString()) {
                $timing = 'three_days_before';
            } elseif ($dueDate === $today->toDateString()) {
                $timing = 'on_due_date';
            } elseif ($dueDate === $today->copy()->subDays(3)->toDateString()) {
                $timing = 'three_days_after';
            } else {
                continue;
            }
            // 同じ計画・同じタイミングの通知が存在するか確認 
            $alreadySent = $plan->user->notifications()->where('type', ReadingPlanReminderNotification::class)->where('data->reading_plan_id', $plan->id)->where('data->timing', $timing)->exists();
            // 送信済みならスキップ 
            if ($alreadySent) {
                continue;
            }
            // 通知を送信 
            $plan->user->notify(new ReadingPlanReminderNotification($plan, $timing));
            $this->info('通知を送信しました：' . $plan->book->title . ' / ' . $timing);
        }
        $this->info('読書計画のリマインダー処理が完了しました。');
        return self::SUCCESS;
    }
}
