<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('type')
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('paystack_reference'),
                TextInput::make('paystack_access_code'),
                Select::make('game_id')
                    ->relationship('game', 'id'),
                Textarea::make('description')
                    ->columnSpanFull(),
                Textarea::make('metadata')
                    ->columnSpanFull(),
                DateTimePicker::make('completed_at'),
            ]);
    }
}
