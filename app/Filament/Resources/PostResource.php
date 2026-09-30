<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $navigationGroup = 'Content';
    protected static ?int $navigationSort = 0;
    protected static ?string $navigationLabel = 'Blog Posts';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('slug')->unique(ignoreRecord: true)->helperText('Auto from title if blank'),
                Forms\Components\Select::make('category')->default('news')
                    ->options(fn () => \App\Models\Category::forType('blog')->pluck('name', 'slug')->toArray() ?: ['news' => 'News', 'guide' => 'Guide', 'investment' => 'Investment'])
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')->required()->live(onBlur: true)->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                        Forms\Components\TextInput::make('slug')->required(),
                    ])
                    ->createOptionUsing(fn (array $data) => \App\Models\Category::create($data + ['type' => 'blog'])->slug)
                    ->searchable(),
                Forms\Components\DateTimePicker::make('published_at')->default(now()),
                Forms\Components\Toggle::make('is_published')->default(true),
                Forms\Components\Toggle::make('is_featured'),
            ]),
            Forms\Components\SpatieMediaLibraryFileUpload::make('cover')->collection('cover')->image()->label('Cover Image'),
            Forms\Components\Tabs::make('Content')->tabs([
                Forms\Components\Tabs\Tab::make('English')->schema([
                    Forms\Components\TextInput::make('en_title')->label('Title (EN)')->required(),
                    Forms\Components\Textarea::make('en_excerpt')->label('Excerpt (EN)')->rows(2),
                    Forms\Components\RichEditor::make('en_body')->label('Body (EN)')->columnSpanFull(),
                    Forms\Components\TextInput::make('en_meta_title')->label('Meta Title (EN)'),
                    Forms\Components\Textarea::make('en_meta_description')->label('Meta Description (EN)')->rows(2),
                ]),
                Forms\Components\Tabs\Tab::make('Turkish')->schema([
                    Forms\Components\TextInput::make('tr_title')->label('Title (TR)'),
                    Forms\Components\Textarea::make('tr_excerpt')->label('Excerpt (TR)')->rows(2),
                    Forms\Components\RichEditor::make('tr_body')->label('Body (TR)')->columnSpanFull(),
                ]),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('cover')->collection('cover')->conversion('thumb')->width(70)->height(50),
                Tables\Columns\TextColumn::make('title')->getStateUsing(fn ($record) => $record->translation('en')?->title ?? '—'),
                Tables\Columns\TextColumn::make('category')->badge(),
                Tables\Columns\IconColumn::make('is_published')->boolean(),
                Tables\Columns\IconColumn::make('is_featured')->boolean(),
                Tables\Columns\TextColumn::make('published_at')->date()->sortable(),
                Tables\Columns\TextColumn::make('views')->sortable(),
            ])
            ->filters([Tables\Filters\TernaryFilter::make('is_published')])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
