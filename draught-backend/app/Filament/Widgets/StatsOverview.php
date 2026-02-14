<?php

namespace App\Filament\Widgets;

use App\Models\Game;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdrawal;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('Active players on platform')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            Stat::make('Total Revenue', 'KES ' . number_format(Transaction::where('type', 'commission')->sum('amount'), 2))
                ->description('Admin earnings from games')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
            Stat::make('Active Games', Game::where('status', 'in_progress')->count())
                ->description('Live matches currently playing')
                ->descriptionIcon('heroicon-m-play')
                ->color('warning'),
            Stat::make('Pending Withdrawals', Withdrawal::where('status', 'pending')->count())
                ->description('Requests awaiting approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),
        ];
    }
}
