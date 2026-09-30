<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuItemResource\Pages;
use App\Models\MenuItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;
    protected static ?string $navigationIcon = 'heroicon-o-bars-3';
    protected static ?string $navigationGroup = 'Appearance';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Menus';

    public static array $locations = [
        'header' => 'Header Menu',
        'footer_col1' => 'Footer — Properties Column',
        'footer_col2' => 'Footer — Company Column',
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('location')->options(static::$locations)->required()->default('footer_col1'),
            Forms\Components\TextInput::make('label')->required(),
            Forms\Components\TextInput::make('url')->required()->default('#')->helperText('Use full URL or path like /about'),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('location')->badge()->formatStateUsing(fn ($state) => static::$locations[$state] ?? $state),
                Tables\Columns\TextColumn::make('label'),
                Tables\Columns\TextColumn::make('url')->color('gray'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
            ])
            ->groups([Tables\Grouping\Group::make('location')->getTitleFromRecordUsing(fn ($record) => static::$locations[$record->location] ?? $record->location)])
            ->defaultGroup('location')
            ->reorderable('sort_order')
            ->filters([Tables\Filters\SelectFilter::make('location')->options(static::$locations)])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenuItems::route('/'),
            'create' => Pages\CreateMenuItem::route('/create'),
            'edit' => Pages\EditMenuItem::route('/{record}/edit'),
        ];
    }
}
