<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgentResource\Pages;
use App\Models\Agent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AgentResource extends Resource
{
    protected static ?string $model = Agent::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = 'Real Estate';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('user_id')->relationship('user', 'name')->searchable()->preload()->required()->label('User Account'),
                Forms\Components\TextInput::make('title')->label('Job Title'),
                Forms\Components\TextInput::make('phone'),
                Forms\Components\TextInput::make('whatsapp'),
                Forms\Components\TextInput::make('facebook')->url(),
                Forms\Components\TextInput::make('instagram')->url(),
                Forms\Components\TextInput::make('linkedin')->url(),
                Forms\Components\Select::make('status')->options(['pending' => 'Pending', 'active' => 'Active', 'inactive' => 'Inactive'])->default('pending'),
                Forms\Components\Toggle::make('is_featured')->label('Featured Agent'),
            ]),
            Forms\Components\Textarea::make('bio')->rows(4)->columnSpanFull(),
            Forms\Components\SpatieMediaLibraryFileUpload::make('photo')->collection('photo')->image()->avatar()->label('Agent Photo'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('photo')->collection('photo')->circular(),
                Tables\Columns\TextColumn::make('user.name')->label('Name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\BadgeColumn::make('status')->colors(['warning' => 'pending', 'success' => 'active', 'gray' => 'inactive']),
                Tables\Columns\IconColumn::make('is_featured')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(['pending' => 'Pending', 'active' => 'Active', 'inactive' => 'Inactive']),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAgents::route('/'),
            'create' => Pages\CreateAgent::route('/create'),
            'edit' => Pages\EditAgent::route('/{record}/edit'),
        ];
    }
}
