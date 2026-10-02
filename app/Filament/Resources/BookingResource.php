<?php

namespace App\Filament\Resources;

use App\Enums\BookingStatus;
use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use App\Models\Doctor;
use App\Models\Service;
use App\Support\Phone;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Klinik';

    protected static ?string $modelLabel = 'Booking';

    protected static ?string $pluralModelLabel = 'Booking';

    public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form
            ->schema([
                Select::make('doctor_id')
                    ->label('Dokter')
                    ->relationship('doctor', 'name')
                    ->preload()
                    ->required(),
                Select::make('service_id')
                    ->label('Layanan')
                    ->options(fn () => Service::active()->orderBy('name')->pluck('name', 'id'))
                    ->required(),
                TextInput::make('patient_name')
                    ->label('Nama Pasien')
                    ->required()
                    ->maxLength(100),
                TextInput::make('patient_phone')
                    ->label('No. HP')
                    ->regex(Phone::VALIDATION_PATTERN)
                    ->required(),
                DatePicker::make('booking_date')
                    ->label('Tanggal')
                    ->minDate(today())
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
                Select::make('status')
                    ->label('Status')
                    ->options(BookingStatus::class)
                    ->default(BookingStatus::Pending->value)
                    ->required(),
                \Filament\Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->maxLength(500)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->copyable()
                    ->fontFamily('mono')
                    ->searchable(),
                TextColumn::make('patient_name')
                    ->label('Pasien')
                    ->searchable(),
                TextColumn::make('patient_phone')
                    ->label('No. HP')
                    ->searchable(),
                TextColumn::make('doctor.name')
                    ->label('Dokter')
                    ->sortable(),
                TextColumn::make('service.name')
                    ->label('Layanan'),
                TextColumn::make('booking_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('start_time')
                    ->label('Jam')
                    ->formatStateUsing(fn ($state, Booking $record) => Carbon::parse($record->start_time)->format('H.i')
                        .' - '.Carbon::parse($record->end_time)->format('H.i')),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(BookingStatus::cases())->mapWithKeys(
                        fn (BookingStatus $status) => [$status->value => $status->getLabel()]
                    )),
                SelectFilter::make('doctor')
                    ->label('Dokter')
                    ->relationship('doctor', 'name')
                    ->preload(),
                Filter::make('hari_ini')
                    ->label('Hari Ini')
                    ->query(fn (Builder $query) => $query->whereDate('booking_date', today()))
                    ->toggle(),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('confirm')
                        ->label('Konfirmasi')
                        ->icon('heroicon-m-check-circle')
                        ->color('info')
                        ->requiresConfirmation()
                        ->visible(fn (Booking $record) => $record->status === BookingStatus::Pending)
                        ->action(fn (Booking $record) => $record->update(['status' => BookingStatus::Confirmed])),
                    Action::make('selesai')
                        ->label('Tandai Selesai')
                        ->icon('heroicon-m-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (Booking $record) => $record->status === BookingStatus::Confirmed)
                        ->action(fn (Booking $record) => $record->update(['status' => BookingStatus::Completed])),
                    Action::make('batalkan')
                        ->label('Batalkan')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn (Booking $record) => in_array($record->status, [BookingStatus::Pending, BookingStatus::Confirmed]))
                        ->action(fn (Booking $record) => $record->update(['status' => BookingStatus::Cancelled])),
                    Action::make('tidak_hadir')
                        ->label('Tidak Hadir')
                        ->icon('heroicon-m-user-minus')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->visible(fn (Booking $record) => $record->status === BookingStatus::Confirmed)
                        ->action(fn (Booking $record) => $record->update(['status' => BookingStatus::NoShow])),
                ]),
            ])
            ->defaultSort('booking_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
