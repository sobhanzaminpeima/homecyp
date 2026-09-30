<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbTestResource\Pages;
use App\Models\AbTest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AbTestResource extends Resource
{
    protected static ?string $model = AbTest::class;
    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationGroup = 'AI';
    protected static ?string $navigationLabel = 'A/B Tests';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\Select::make('subject')
                    ->options(['welcome_message' => 'Welcome message', 'suggested_questions' => 'Suggested questions', 'cta' => 'CTA'])
                    ->required(),
                Forms\Components\Select::make('status')->options(['active' => 'Active', 'paused' => 'Paused', 'ended' => 'Ended'])->default('active'),
            ]),
            Forms\Components\Repeater::make('variants')
                ->schema([
                    Forms\Components\TextInput::make('key')->required()->helperText('e.g. A, B, control'),
                    Forms\Components\TextInput::make('weight')->numeric()->default(1)->helperText('Relative chance vs other variants'),
                    Forms\Components\KeyValue::make('content')->label('Content')->helperText('e.g. title / subtitle for welcome_message'),
                ])
                ->columns(3)
                ->minItems(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('subject')->badge(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => $state === 'active' ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('results')
                    ->label('Results')
                    ->getStateUsing(function (AbTest $record) {
                        return collect($record->results ?? [])->map(fn ($r, $key) => "{$key}: ".($r['impressions'] ?? 0)." impr / ".($r['conversions'] ?? 0)." conv")->implode(' · ');
                    })
                    ->wrap(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAbTests::route('/'),
            'create' => Pages\CreateAbTest::route('/create'),
            'edit' => Pages\EditAbTest::route('/{record}/edit'),
        ];
    }
}
