<?php

namespace App\Filament\Resources\Games\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class GameForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('game_code')
                    ->required(),
                Select::make('creator_id')
                    ->relationship('creator', 'name')
                    ->required(),
                Select::make('opponent_id')
                    ->relationship('opponent', 'name'),
                TextInput::make('status')
                    ->required()
                    ->default('waiting'),
                TextInput::make('bet_amount')
                    ->required()
                    ->numeric(),
                TextInput::make('total_pot')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('admin_commission')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('prize_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('creator_color')
                    ->required(),
                TextInput::make('opponent_color'),
                TextInput::make('current_turn'),
                Select::make('winner_id')
                    ->relationship('winner', 'name'),
                TextInput::make('result'),
                Textarea::make('board_state')
                    ->columnSpanFull(),
                DateTimePicker::make('started_at'),
                DateTimePicker::make('completed_at'),
            ]);
    }
}
