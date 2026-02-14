<?php

namespace App\Filament\Resources\Games\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GamesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('game_code')
                    ->searchable(),
                TextColumn::make('creator.name')
                    ->searchable(),
                TextColumn::make('opponent.name')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('bet_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_pot')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('admin_commission')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('prize_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('creator_color')
                    ->searchable(),
                TextColumn::make('opponent_color')
                    ->searchable(),
                TextColumn::make('current_turn')
                    ->searchable(),
                TextColumn::make('winner.name')
                    ->searchable(),
                TextColumn::make('result')
                    ->searchable(),
                TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
