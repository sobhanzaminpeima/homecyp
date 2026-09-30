<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Models\Lead;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'CRM';
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'new')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('email')->email(),
                Forms\Components\TextInput::make('phone'),
                Forms\Components\Select::make('type')
                    ->options([
                        'contact' => 'Contact',
                        'viewing' => 'Viewing Request',
                        'investment' => 'Investment Consultation',
                        'facebook' => 'Facebook Lead',
                        'instagram' => 'Instagram Lead',
                        'meta' => 'Meta Lead',
                    ])->default('contact'),
                Forms\Components\Select::make('status')
                    ->options([
                        'new' => 'New',
                        'contacted' => 'Contacted',
                        'qualified' => 'Qualified',
                        'follow_up' => 'Follow Up',
                        'closed' => 'Closed',
                    ])->default('new'),
                Forms\Components\Select::make('source')
                    ->options(['website' => 'Website', 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'google' => 'Google']),
                Forms\Components\Select::make('preferred_language')
                    ->options(['en' => 'English', 'tr' => 'Turkish'])->default('en'),
                Forms\Components\Select::make('agent_id')
                    ->relationship('agent', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->user?->name ?? 'Unknown')
                    ->searchable()->preload()->label('Assigned Agent'),
            ]),
            Forms\Components\Textarea::make('message')->rows(3)->columnSpanFull(),
            Forms\Components\Repeater::make('notes')
                ->relationship()
                ->schema([
                    Forms\Components\Textarea::make('note')->required()->rows(2),
                ])
                ->columnSpanFull()
                ->label('Notes & Follow-ups'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors(['primary' => 'contact', 'warning' => 'viewing', 'success' => 'investment', 'info' => 'facebook']),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['info' => 'new', 'warning' => 'contacted', 'success' => 'qualified', 'primary' => 'follow_up', 'gray' => 'closed']),
                Tables\Columns\TextColumn::make('source'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['new' => 'New', 'contacted' => 'Contacted', 'qualified' => 'Qualified', 'follow_up' => 'Follow Up', 'closed' => 'Closed']),
                Tables\Filters\SelectFilter::make('type')
                    ->options(['contact' => 'Contact', 'viewing' => 'Viewing', 'investment' => 'Investment']),
                Tables\Filters\SelectFilter::make('source')
                    ->options(['website' => 'Website', 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'google' => 'Google']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }
}
