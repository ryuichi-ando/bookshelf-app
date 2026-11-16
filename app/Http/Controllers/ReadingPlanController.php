<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Http\Requests\StoreReadingPlanRequest;
use App\Http\Requests\UpdateReadingPlanRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReadingPlanController extends Controller
{
    /**
     * 読書計画一覧
     */
    public function index(Request $request)
    {
        $query = ReadingPlan::with('book')
            ->where('user_id', $request->user()->id);

        $currentStatus = $request->query('status', '');

        // 状態による絞り込み
        if ($currentStatus !== '') {
            $status = filter_var(
                $currentStatus,
                FILTER_VALIDATE_INT
            );

            if (
                $status === false
                || ReadingPlanStatus::tryFrom($status) === null
            ) {
                abort(404);
            }

            $query->where('status', $status);
        }

        $readingPlans = $query
            ->orderBy('due_date')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('reading-plans.index', compact(
            'readingPlans',
            'currentStatus'
        ));
    }

    /**
     * 新規読書計画作成画面
     */
    public function create()
    {
        $books = Book::orderBy('title')->get();

        return view('reading-plans.create', compact('books'));
    }

    /**
     * 読書計画登録
     */
    public function store(StoreReadingPlanRequest $request)
    {
        $validated = $request->validated();

        ReadingPlan::create([
            'user_id' => $request->user()->id,
            'book_id' => $validated['book_id'],
            'due_date' => $validated['target_date'],
            'status' => ReadingPlanStatus::Unread,
            'completed_at' => null,
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を登録しました。');
    }

    /**
     * 読書計画編集画面
     */
    public function edit(Request $request, ReadingPlan $plan)
    {
        $this->authorizePlan($request, $plan);

        $readingPlan = $plan->load('book');

        return view('reading-plans.edit', compact('readingPlan'));
    }

    /**
     * 読書計画更新
     */
    public function update(
        UpdateReadingPlanRequest $request,
        ReadingPlan $plan
    ) {
        $this->authorizePlan($request, $plan);

        if ($plan->status === ReadingPlanStatus::Completed) {
            abort(403, '読了済みの読書計画は編集できません。');
        }

        $validated = $request->validated();

        $plan->update([
            'due_date' => $validated['target_date'],
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を更新しました。');
    }

    /**
     * 読了処理
     */
    public function complete(Request $request, ReadingPlan $plan)
    {
        $this->authorizePlan($request, $plan);

        if ($plan->status === ReadingPlanStatus::Completed) {
            return redirect()
                ->route('reading-plans.index')
                ->with('success', 'この読書計画はすでに読了済みです。');
        }

        DB::transaction(function () use ($plan) {
            $plan->update([
                'status' => ReadingPlanStatus::Completed,
                'completed_at' => now(),
            ]);
        });

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読了として登録しました。');
    }

    /**
     * 読書計画削除
     */
    public function destroy(Request $request, ReadingPlan $plan)
    {
        $this->authorizePlan($request, $plan);

        $plan->delete();

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を削除しました。');
    }

    /**
     * 読書計画の所有者確認
     */
    private function authorizePlan(
        Request $request,
        ReadingPlan $plan
    ): void {
        abort_unless(
            $plan->user_id === $request->user()->id,
            403
        );
    }

    // private function authorizePlan(Request $request, ReadingPlan $plan): void
    // {
    //     dump([
    //         'plan_user_id' => $plan->user_id,
    //         'plan_user_id_type' => gettype($plan->user_id),
    //         'login_user_id' => $request->user()->id,
    //         'login_user_id_type' => gettype($request->user()->id),
    //     ]);

    //     abort_unless($plan->user_id === $request->user()->id, 403);
    // }
}
