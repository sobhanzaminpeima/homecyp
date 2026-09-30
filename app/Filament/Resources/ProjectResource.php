<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationGroup = 'Real Estate';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Project')->tabs([
                Forms\Components\Tabs\Tab::make('Basic Info')->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('developer'),
                        Forms\Components\TextInput::make('location'),
                        Forms\Components\TextInput::make('region'),
                        Forms\Components\TextInput::make('price_from')->numeric()->prefix('£'),
                        Forms\Components\Select::make('currency')->options(['GBP' => 'GBP £', 'USD' => 'USD $', 'EUR' => 'EUR €', 'TRY' => 'TRY ₺'])->default('GBP'),
                        Forms\Components\DatePicker::make('completion_date'),
                        Forms\Components\Select::make('status')->options(['active' => 'Active', 'completed' => 'Completed', 'upcoming' => 'Upcoming'])->default('active'),
                        Forms\Components\Toggle::make('is_featured')->label('Featured'),
                    ]),
                    Forms\Components\TagsInput::make('amenities')->placeholder('Add amenity'),
                ]),
                Forms\Components\Tabs\Tab::make('English Content')->schema([
                    Forms\Components\TextInput::make('en_title')->label('Title (EN)'),
                    Forms\Components\Textarea::make('en_short_description')->label('Short Description (EN)')->rows(2),
                    Forms\Components\RichEditor::make('en_description')->label('Description (EN)'),
                    Forms\Components\TextInput::make('en_meta_title')->label('Meta Title (EN)'),
                    Forms\Components\Textarea::make('en_meta_description')->label('Meta Description (EN)')->rows(2),
                ]),
                Forms\Components\Tabs\Tab::make('Turkish Content')->schema([
                    Forms\Components\TextInput::make('tr_title')->label('Title (TR)'),
                    Forms\Components\Textarea::make('tr_short_description')->label('Short Description (TR)')->rows(2),
                    Forms\Components\RichEditor::make('tr_description')->label('Description (TR)'),
                    Forms\Components\TextInput::make('tr_meta_title')->label('Meta Title (TR)'),
                    Forms\Components\Textarea::make('tr_meta_description')->label('Meta Description (TR)')->rows(2),
                ]),
                Forms\Components\Tabs\Tab::make('Media')->schema([
                    Forms\Components\SpatieMediaLibraryFileUpload::make('cover')->collection('cover')->image()->label('Cover Image'),
                    Forms\Components\SpatieMediaLibraryFileUpload::make('gallery')->collection('gallery')->multiple()->reorderable()->image()->label('Gallery'),
                    Forms\Components\SpatieMediaLibraryFileUpload::make('brochure')->collection('brochure')->acceptedFileTypes(['application/pdf'])->label('Brochure PDF'),
                ]),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('cover')->collection('cover')->conversion('thumb')->width(80)->height(60),
                Tables\Columns\TextColumn::make('slug')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('location'),
                Tables\Columns\TextColumn::make('price_from')->money('GBP')->sortable(),
                Tables\Columns\BadgeColumn::make('status')->colors(['success' => 'active', 'gray' => 'completed', 'info' => 'upcoming']),
                Tables\Columns\IconColumn::make('is_featured')->boolean(),
                Tables\Columns\TextColumn::make('views')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(['active' => 'Active', 'completed' => 'Completed', 'upcoming' => 'Upcoming']),
                Tables\Filters\TernaryFilter::make('is_featured')->label('Featured'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
