<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecommendationRuleResource\Pages;
use App\Models\RecommendationRule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RecommendationRuleResource extends Resource
{
    protected static ?string $model = RecommendationRule::class;
    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?string $navigationGroup = 'AI';
    protected static ?string $navigationLabel = 'Recommendation Rules';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('label')->required()->columnSpanFull(),
            Forms\Components\TagsInput::make('trigger_keywords')->required()->helperText('Words/phrases that trigger this rule, e.g. "kids", "children"'),
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('intent')->helperText('Optional: only apply when this intent is detected, e.g. investment'),
                Forms\Components\TextInput::make('boosted_attribute')->required()->helperText('e.g. near_school, high_roi, near_hospital, remote_work_ready'),
                Forms\Components\TextInput::make('weight')->numeric()->default(1.0)->step(0.1),
            ]),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')->searchable(),
                Tables\Columns\TextColumn::make('trigger_keywords')->badge()->separator(','),
                Tables\Columns\TextColumn::make('boosted_attribute')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('weight'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecommendationRules::route('/'),
            'create' => Pages\CreateRecommendationRule::route('/create'),
            'edit' => Pages\EditRecommendationRule::route('/{record}/edit'),
        ];
    }
}
