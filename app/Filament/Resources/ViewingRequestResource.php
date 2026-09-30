<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ViewingRequestResource\Pages;
use App\Models\ViewingRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ViewingRequestResource extends Resource
{
    protected static ?string $model = ViewingRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'AI';
    protected static ?string $navigationLabel = 'Viewing Requests';
    protected static ?int $navigationSort = 8;
    protected static bool $canCreate = false;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('status')
                ->options(['pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'])
                ->required()
                ->live()
                ->afterStateUpdated(function ($state, callable $set) {
                    if ($state === 'confirmed') $set('confirmed_at', now());
                    if ($state === 'completed') $set('completed_at', now());
                }),
            Forms\Components\Select::make('agent_id')->relationship('agent', 'name')->label('Assigned Agent'),
            Forms\Components\Textarea::make('notes')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('lead.name')->label('Lead')->searchable(),
                Tables\Columns\TextColumn::make('property.title')->label('Property')->default('—'),
                Tables\Columns\TextColumn::make('agent.name')->label('Agent')->default('Unassigned'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'pending' => 'warning', 'confirmed' => 'info', 'completed' => 'success', 'cancelled' => 'danger', default => 'gray',
                }),
                Tables\Columns\TextColumn::make('requested_at')->dateTime()->since()->sortable(),
            ])
            ->defaultSort('requested_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(['pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled']),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListViewingRequests::route('/'),
            'edit' => Pages\EditViewingRequest::route('/{record}/edit'),
        ];
    }
}
