<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SponsorCampaignResource\Pages;
use App\Models\Property;
use App\Models\SponsorCampaign;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SponsorCampaignResource extends Resource
{
    protected static ?string $model = SponsorCampaign::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = 'AI';
    protected static ?string $navigationLabel = 'Sponsor Campaigns';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('sponsor_id')->relationship('sponsor', 'company_name')->required(),
                Forms\Components\Select::make('property_id')->label('Property')
                    ->options(fn () => Property::query()->limit(200)->get()->mapWithKeys(fn ($p) => [$p->id => $p->title ?: $p->slug]))
                    ->searchable(),
                Forms\Components\TextInput::make('budget')->numeric()->prefix('£'),
                Forms\Components\TextInput::make('priority')->numeric()->default(0)->helperText('Higher shows first among sponsored matches'),
                Forms\Components\DatePicker::make('starts_at'),
                Forms\Components\DatePicker::make('ends_at'),
                Forms\Components\Select::make('status')->options(['active' => 'Active', 'paused' => 'Paused', 'ended' => 'Ended'])->default('active'),
            ]),
            Forms\Components\Section::make('Targeting')->columns(3)->schema([
                Forms\Components\TextInput::make('target_region')->placeholder('e.g. Kyrenia'),
                Forms\Components\TextInput::make('target_property_type')->placeholder('e.g. apartment'),
                Forms\Components\TextInput::make('target_budget_min')->numeric()->prefix('£'),
                Forms\Components\TextInput::make('target_budget_max')->numeric()->prefix('£'),
                Forms\Components\TagsInput::make('target_languages')->placeholder('en, tr')->columnSpan(2),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sponsor.company_name')->label('Sponsor')->searchable(),
                Tables\Columns\TextColumn::make('property.title')->label('Property'),
                Tables\Columns\TextColumn::make('target_region')->label('Region'),
                Tables\Columns\TextColumn::make('priority')->sortable(),
                Tables\Columns\TextColumn::make('impressions')->sortable(),
                Tables\Columns\TextColumn::make('clicks')->sortable(),
                Tables\Columns\TextColumn::make('conversions')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'active' => 'success', 'paused' => 'warning', default => 'gray',
                }),
            ])
            ->filters([Tables\Filters\SelectFilter::make('status')->options(['active' => 'Active', 'paused' => 'Paused', 'ended' => 'Ended'])])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSponsorCampaigns::route('/'),
            'create' => Pages\CreateSponsorCampaign::route('/create'),
            'edit' => Pages\EditSponsorCampaign::route('/{record}/edit'),
        ];
    }
}
