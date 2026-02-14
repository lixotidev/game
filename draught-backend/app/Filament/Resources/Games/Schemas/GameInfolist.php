<?php

namespace App\Filament\Resources\Games\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class GameInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('game_code'),
                TextEntry::make('creator.name')
                    ->label('Creator'),
                TextEntry::make('opponent.name')
                    ->label('Opponent')
                    ->placeholder('-'),
                TextEntry::make('status'),
                TextEntry::make('bet_amount')
                    ->numeric(),
                TextEntry::make('total_pot')
                    ->numeric(),
                TextEntry::make('admin_commission')
                    ->numeric(),
                TextEntry::make('prize_amount')
                    ->numeric(),
                TextEntry::make('creator_color'),
                TextEntry::make('opponent_color')
                    ->placeholder('-'),
                TextEntry::make('current_turn')
                    ->placeholder('-'),
                TextEntry::make('winner.name')
                    ->label('Winner')
                    ->placeholder('-'),
                TextEntry::make('result')
                    ->placeholder('-'),
                TextEntry::make('board_state')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('started_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('completed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
