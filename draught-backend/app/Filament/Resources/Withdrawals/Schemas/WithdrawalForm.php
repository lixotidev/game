<?php

namespace App\Filament\Resources\Withdrawals\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class WithdrawalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('account_number')
                    ->required(),
                TextInput::make('bank_code')
                    ->required(),
                TextInput::make('account_name')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('paystack_transfer_code'),
                TextInput::make('paystack_recipient_code'),
                Textarea::make('admin_notes')
                    ->columnSpanFull(),
                TextInput::make('processed_by')
                    ->numeric(),
                DateTimePicker::make('processed_at'),
            ]);
    }
}
