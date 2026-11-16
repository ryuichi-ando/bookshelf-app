<?php

namespace App\Enums;

enum ReadingPlanStatus: int
{
    case Unread = 0;
    case Reading = 1;
    case Completed = 2;

    /**
     * ステータスの表示名
     */
    public function label(): string
    {
        return match ($this) {
            self::Unread => '未読',
            self::Reading => '読書中',
            self::Completed => '読了',
        };
    }

    /**
     * ステータスに応じたバッジのCSSクラス
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Unread => 'bg-gray-100 text-gray-800',
            self::Reading => 'bg-blue-100 text-blue-800',
            self::Completed => 'bg-green-100 text-green-800',
        };
    }
}