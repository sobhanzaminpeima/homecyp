<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Content';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
                Forms\Components\Toggle::make('is_active')->default(true),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            ]),
            Forms\Components\Tabs::make('Content')->tabs([
                Forms\Components\Tabs\Tab::make('English')->schema([
                    Forms\Components\TextInput::make('en_title')->label('Title (EN)')->required(),
                    Forms\Components\RichEditor::make('en_content')->label('Content (EN)')->columnSpanFull(),
                    Forms\Components\TextInput::make('en_meta_title')->label('Meta Title (EN)'),
                    Forms\Components\Textarea::make('en_meta_description')->label('Meta Description (EN)')->rows(2),
                ]),
                Forms\Components\Tabs\Tab::make('Turkish')->schema([
                    Forms\Components\TextInput::make('tr_title')->label('Title (TR)'),
                    Forms\Components\RichEditor::make('tr_content')->label('Content (TR)')->columnSpanFull(),
                    Forms\Components\TextInput::make('tr_meta_title')->label('Meta Title (TR)'),
                    Forms\Components\Textarea::make('tr_meta_description')->label('Meta Description (TR)')->rows(2),
                ]),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->getStateUsing(fn ($record) => $record->translation('en')?->title ?? $record->slug)->searchable(false),
                Tables\Columns\TextColumn::make('slug')->badge(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
            ])
            ->reorderable('sort_order')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
