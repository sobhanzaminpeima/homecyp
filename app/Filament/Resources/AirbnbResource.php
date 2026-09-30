<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AirbnbResource\Pages;
use App\Models\Property;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AirbnbResource extends Resource
{
    protected static ?string $model = Property::class;
    protected static ?string $slug = 'airbnb-listings';
    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $navigationGroup = 'Real Estate';
    protected static ?string $navigationLabel = 'Airbnb Listings';
    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_airbnb', true);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Property::where('is_airbnb', true)->where('status', 'pending')->count() ?: null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Listing')->columns(2)->schema([
                Forms\Components\TextInput::make('en_title')->label('Title')->required()->columnSpanFull(),
                Forms\Components\TextInput::make('airbnb_url')->label('Airbnb URL')->url()->required()->columnSpanFull()
                    ->helperText('The "Book on Airbnb" button links here.'),
                Forms\Components\Select::make('status')->options([
                    'pending' => 'Draft (hidden)', 'active' => 'Published',
                ])->default('pending')->required(),
                Forms\Components\Select::make('type')->options([
                    'apartment' => 'Apartment', 'villa' => 'Villa', 'penthouse' => 'Penthouse', 'studio' => 'Studio',
                ])->default('apartment'),
                Forms\Components\TextInput::make('price')->numeric()->prefix('£')->label('Price / night'),
                Forms\Components\TextInput::make('location'),
                Forms\Components\TextInput::make('bedrooms')->numeric(),
                Forms\Components\TextInput::make('bathrooms')->numeric(),
                Forms\Components\Toggle::make('has_pool')->label('Pool'),
                Forms\Components\Toggle::make('has_sea_view')->label('Sea View'),
                Forms\Components\Toggle::make('is_featured')->label('Featured'),
            ]),
            Forms\Components\Textarea::make('en_short_description')->label('Short description')->rows(3),
            Forms\Components\SpatieMediaLibraryFileUpload::make('gallery')->collection('gallery')->multiple()->reorderable()->image()->label('Photos'),
            Forms\Components\SpatieMediaLibraryFileUpload::make('cover')->collection('cover')->image()->label('Cover'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('cover')->collection('cover')->conversion('thumb')->width(70)->height(50),
                Tables\Columns\TextColumn::make('title')->getStateUsing(fn ($record) => $record->translation('en')?->title ?? '—'),
                Tables\Columns\BadgeColumn::make('status')->colors(['success' => 'active', 'warning' => 'pending']),
                Tables\Columns\TextColumn::make('price')->money('GBP')->label('Per night'),
                Tables\Columns\TextColumn::make('location'),
                Tables\Columns\IconColumn::make('is_featured')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(['pending' => 'Draft', 'active' => 'Published']),
            ])
            ->actions([
                Tables\Actions\Action::make('open')->label('Open Airbnb')->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => $record->airbnb_url)->openUrlInNewTab()->color('gray'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('publish')->label('Publish selected')->icon('heroicon-o-check')
                        ->action(fn ($records) => $records->each->update(['status' => 'active']))->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('unpublish')->label('Set to draft')->icon('heroicon-o-eye-slash')
                        ->action(fn ($records) => $records->each->update(['status' => 'pending']))->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAirbnbs::route('/'),
            'edit' => Pages\EditAirbnb::route('/{record}/edit'),
        ];
    }
}
