<?php

namespace App\Filament\Resources\Withdrawals\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class WithdrawalInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('amount')
                    ->numeric(),
                TextEntry::make('account_number'),
                TextEntry::make('bank_code'),
                TextEntry::make('account_name'),
                TextEntry::make('status'),
                TextEntry::make('paystack_transfer_code')
                    ->placeholder('-'),
                TextEntry::make('paystack_recipient_code')
                    ->placeholder('-'),
                TextEntry::make('admin_notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('processed_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('processed_at')
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
