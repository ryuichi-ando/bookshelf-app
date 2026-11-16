<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\ReadingPlanStatus;

class ReadingPlan extends Model
{
    use HasFactory;

    /**
     * 読書ステータス
     */
    public const STATUS_UNREAD = 0;

    public const STATUS_READING = 1;

    public const STATUS_COMPLETED = 2;

    /**
     * 一括代入を許可するカラム
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'due_date',
        'status',
        'completed_at',
    ];

    /**
     * 属性の型変換
     */
    // protected function casts(): array
    // {
    //     return [
    //         'due_date' => 'date',
    //         'status' => 'integer',
    //         'completed_at' => 'datetime',
    //     ];
    // }

    // protected function casts(): array
    // {
    //     return [
    //         'due_date' => 'date',
    //         'status' => ReadingPlanStatus::class,
    //         'completed_at' => 'datetime',
    //     ];
    // }

    /**
     * 属性の型変換
     */
    protected $casts = [
        'due_date' => 'date',
        'status' => ReadingPlanStatus::class,
        'completed_at' => 'datetime',
    ];

    public function getTargetDateAttribute()
    {
        if ($this->due_date === null) {
            return null;
        }

        return \Illuminate\Support\Carbon::parse($this->due_date);
    }

    /**
     * 読書予定を登録したユーザー
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 読書予定の対象書籍
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
