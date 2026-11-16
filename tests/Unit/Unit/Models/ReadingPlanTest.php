<?php

namespace Tests\Unit\Unit\Models;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 読書ステータスがEnumに変換されること
     */
    public function test_reading_plan_status_is_cast_to_enum(): void
    {
        $plan = new ReadingPlan([
            'status' => ReadingPlanStatus::Reading,
        ]);

        $this->assertSame(
            ReadingPlanStatus::Reading,
            $plan->status
        );
    }

    /**
     * 予定日が日付型に変換されること
     */
    public function test_due_date_is_cast_to_date(): void
    {
        $plan = new ReadingPlan([
            'due_date' => '2026-12-15',
        ]);

        $this->assertInstanceOf(Carbon::class, $plan->due_date);
        $this->assertEquals('2026-12-15', $plan->due_date->format('Y-m-d'));
    }

    /**
     * 読了日時が日時型に変換されること
     */
    public function test_completed_at_is_cast_to_datetime(): void
    {
        $plan = new ReadingPlan([
            'completed_at' => '2026-12-15 10:30:00',
        ]);

        $this->assertInstanceOf(Carbon::class, $plan->completed_at);
    }

    /**
     * target_dateから予定日を取得できること
     */
    public function test_target_date_returns_due_date(): void
    {
        $plan = new ReadingPlan([
            'due_date' => '2026-12-15',
        ]);

        $this->assertInstanceOf(Carbon::class, $plan->target_date);

        $this->assertEquals(
            '2026-12-15',
            $plan->target_date->format('Y-m-d')
        );
    }

    /**
     * 予定日がnullの場合、target_dateもnullになること
     */
    public function test_target_date_returns_null_when_due_date_is_null(): void
    {
        $plan = new ReadingPlan([
            'due_date' => null,
        ]);

        $this->assertNull($plan->target_date);
    }

    /**
     * 読書予定がユーザーに紐づいていること
     */
    public function test_reading_plan_belongs_to_user(): void
    {
        $plan = new ReadingPlan();

        $this->assertInstanceOf(
            BelongsTo::class,
            $plan->user()
        );

        $this->assertEquals(
            'user_id',
            $plan->user()->getForeignKeyName()
        );
    }

    /**
     * 読書予定が書籍に紐づいていること
     */
    public function test_reading_plan_belongs_to_book(): void
    {
        $plan = new ReadingPlan();

        $this->assertInstanceOf(
            BelongsTo::class,
            $plan->book()
        );

        $this->assertEquals(
            'book_id',
            $plan->book()->getForeignKeyName()
        );
    }
}
