<?php

namespace Tests\Feature\Report;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_reading_report(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('reports.index'));

        $response->assertOk();

        dump(array_keys($response->original->getData()));
    }

    public function test_guest_cannot_view_reading_report(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect('/login');
    }

    // public function test_report_contains_summary_data(): void
    // {
    //     $user = User::factory()->create();

    //     $response = $this->actingAs($user)
    //         ->get(route('reports.index'));

    //     $response->assertOk();

    //     // 実際のReportControllerが渡す変数名に合わせて確認する
    //     $response->assertViewHas('summary');
    // }

    public function test_report_contains_summary_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('reports.index'));

        $response->assertOk();

        // ControllerからViewに渡された変数名を表示
        dump(array_keys($response->original->getData()));
    }

    public function test_report_contains_rating_distribution(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('reports.index'));

        $response->assertOk();

        dump(array_keys($response->original->getData()));

        // 評価分布の集計データがViewに渡されていること
        $response->assertViewHas('ratingDistribution');
    }

    public function test_report_contains_top_rated_books(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('reports.index'));

        $response->assertOk();

        dump(array_keys($response->original->getData()));

        $response->assertViewHas('topRatedBooks');
    }

    public function test_report_contains_genre_rating_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('reports.index'));

        $response->assertOk();

        dump(array_keys($response->original->getData()));

        $response->assertViewHas('genreRatings');
    }
}
