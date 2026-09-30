<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PropertyResource\Pages;
use App\Models\Property;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Real Estate';
    protected static ?int $navigationSort = 1;

    /**
     * Agents only see and manage their own listings; admins see everything.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user && $user->hasRole('agent') && !$user->isAdmin()) {
            $query->where('agent_id', $user->id);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Property')->tabs([

                Forms\Components\Tabs\Tab::make('Basic Info')->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Select::make('category')
                            ->options([
                                'project' => 'New Project',
                                'resale' => 'Resale',
                                'daily_rental' => 'Daily Rental',
                                'long_term_rental' => 'Long-Term Rental',
                            ])->required(),
                        Forms\Components\Select::make('type')
                            ->options([
                                'apartment' => 'Apartment',
                                'villa' => 'Villa',
                                'penthouse' => 'Penthouse',
                                'studio' => 'Studio',
                                'commercial' => 'Commercial',
                                'office' => 'Office',
                                'land' => 'Land',
                                'hotel_apartment' => 'Hotel Apartment',
                                'townhouse' => 'Townhouse',
                            ])->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'sold' => 'Sold',
                                'rented' => 'Rented',
                                'pending' => 'Pending',
                            ])->default('active')->required(),
                        Forms\Components\TextInput::make('slug')
                            ->required()->unique(ignoreRecord: true)
                            ->helperText('Auto-generated from title if left blank'),
                        Forms\Components\TextInput::make('price')->numeric()->prefix('£'),
                        Forms\Components\Select::make('currency')
                            ->options(['GBP' => 'GBP £', 'USD' => 'USD $', 'EUR' => 'EUR €', 'TRY' => 'TRY ₺'])
                            ->default('GBP'),
                        Forms\Components\TextInput::make('bedrooms')->numeric(),
                        Forms\Components\TextInput::make('bathrooms')->numeric(),
                        Forms\Components\TextInput::make('area')->numeric()->suffix('sqm'),
                        Forms\Components\TextInput::make('location'),
                        Forms\Components\TextInput::make('region'),
                        Forms\Components\DatePicker::make('completion_date'),
                        Forms\Components\TextInput::make('payment_plan'),
                        Forms\Components\Select::make('agent_id')
                            ->relationship('agent', 'name')->searchable()->preload(),
                    ]),
                    Forms\Components\Grid::make(4)->schema([
                        Forms\Components\Toggle::make('has_parking')->label('Parking'),
                        Forms\Components\Toggle::make('has_pool')->label('Swimming Pool'),
                        Forms\Components\Toggle::make('has_gym')->label('Gym'),
                        Forms\Components\Toggle::make('has_sea_view')->label('Sea View'),
                        Forms\Components\Toggle::make('is_featured')->label('Featured'),
                        Forms\Components\Toggle::make('is_airbnb')->label('Airbnb Listing'),
                    ]),
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
                    Forms\Components\SpatieMediaLibraryFileUpload::make('cover')
                        ->collection('cover')->image()->imageResizeMode('cover')
                        ->label('Cover Image'),
                    Forms\Components\SpatieMediaLibraryFileUpload::make('gallery')
                        ->collection('gallery')->multiple()->reorderable()
                        ->image()->label('Gallery Images'),
                    Forms\Components\SpatieMediaLibraryFileUpload::make('brochure')
                        ->collection('brochure')->acceptedFileTypes(['application/pdf'])
                        ->label('PDF Brochure'),
                    Forms\Components\TextInput::make('video_url')->url()->label('Video URL (YouTube/Vimeo)'),
                    Forms\Components\TextInput::make('virtual_tour_url')->url()->label('Virtual Tour URL'),
                ]),

                Forms\Components\Tabs\Tab::make('Location')->schema([
                    Forms\Components\TextInput::make('latitude')->numeric(),
                    Forms\Components\TextInput::make('longitude')->numeric(),
                ]),

                Forms\Components\Tabs\Tab::make('Airbnb')->schema([
                    Forms\Components\TextInput::make('airbnb_url')->url()->label('Airbnb Listing URL'),
                ])->visible(fn (Forms\Get $get) => $get('is_airbnb')),

            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('cover')
                    ->collection('cover')->conversion('thumb')->circular(false)->width(80)->height(60),
                Tables\Columns\TextColumn::make('title')->searchable()->sortable()
                    ->getStateUsing(fn ($record) => $record->translation()?->title ?? '—'),
                Tables\Columns\BadgeColumn::make('category')
                    ->colors(['primary' => 'project', 'success' => 'resale', 'warning' => 'daily_rental', 'info' => 'long_term_rental']),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['success' => 'active', 'danger' => 'sold', 'warning' => 'rented']),
                Tables\Columns\TextColumn::make('price')->money('GBP')->sortable(),
                Tables\Columns\TextColumn::make('location'),
                Tables\Columns\IconColumn::make('is_featured')->boolean(),
                Tables\Columns\TextColumn::make('views')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(['project' => 'Project', 'resale' => 'Resale', 'daily_rental' => 'Daily Rental', 'long_term_rental' => 'Long-Term Rental']),
                Tables\Filters\SelectFilter::make('status')
                    ->options(['active' => 'Active', 'sold' => 'Sold', 'rented' => 'Rented']),
                Tables\Filters\TernaryFilter::make('is_featured')->label('Featured Only'),
                Tables\Filters\TernaryFilter::make('is_airbnb')->label('Airbnb Only'),
                Tables\Filters\SelectFilter::make('pending')->label('Approval')
                    ->options(['pending' => 'Pending approval'])
                    ->query(fn ($query, $data) => ($data['value'] ?? null) === 'pending' ? $query->where('status', 'pending') : $query),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')->icon('heroicon-o-check-badge')->color('success')
                    ->visible(fn ($record) => $record->status === 'pending' && auth()->user()?->isAdmin())
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['status' => 'active'])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve')->label('Approve selected')->icon('heroicon-o-check-badge')->color('success')
                        ->visible(fn () => auth()->user()?->isAdmin())
                        ->action(fn ($records) => $records->each->update(['status' => 'active']))->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('feature')->label('Mark as featured')->icon('heroicon-o-star')
                        ->action(fn ($records) => $records->each->update(['is_featured' => true]))->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProperties::route('/'),
            'create' => Pages\CreateProperty::route('/create'),
            'edit' => Pages\EditProperty::route('/{record}/edit'),
        ];
    }
}
