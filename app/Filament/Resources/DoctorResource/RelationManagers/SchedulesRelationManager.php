<?php

namespace App\Filament\Resources\DoctorResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'schedules';

    protected static ?string $title = 'Jadwal Praktik';

    public static function dayOptions(): array
    {
        return [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('day_of_week')
                    ->label('Hari')
                    ->options(self::dayOptions())
                    ->required(),
                TimePicker::make('start_time')
                    ->label('Jam Mulai')
                    ->seconds(false)
                    ->required(),
                TimePicker::make('end_time')
                    ->label('Jam Selesai')
                    ->seconds(false)
                    ->after('start_time')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('day_of_week')
            ->columns([
                TextColumn::make('day_of_week')
                    ->label('Hari')
                    ->formatStateUsing(fn ($state) => self::dayOptions()[$state] ?? $state)
                    ->badge(),
                TextColumn::make('start_time')
                    ->label('Jam Mulai'),
                TextColumn::make('end_time')
                    ->label('Jam Selesai'),
            ])
            ->defaultSort('day_of_week')
            ->headerActions([
                \Filament\Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
