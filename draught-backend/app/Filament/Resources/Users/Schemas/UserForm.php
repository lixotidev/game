<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('avatar'),
                TextInput::make('wallet_balance')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_games_played')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_wins')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_losses')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_ties')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_earnings')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
