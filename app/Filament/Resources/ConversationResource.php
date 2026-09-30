<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConversationResource\Pages;
use App\Models\Conversation;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';
    protected static ?string $navigationGroup = 'AI';
    protected static ?string $navigationLabel = 'Conversations';
    protected static ?int $navigationSort = 4;
    protected static bool $canCreate = false;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#'),
                Tables\Columns\TextColumn::make('title')->default('(untitled)')->wrap(),
                Tables\Columns\TextColumn::make('lead.name')->label('Lead')->default('—'),
                Tables\Columns\TextColumn::make('locale')->badge(),
                Tables\Columns\TextColumn::make('messages_count')->counts('messages')->label('Messages'),
                Tables\Columns\TextColumn::make('negative_feedback_count')
                    ->label('👎')
                    ->getStateUsing(fn (Conversation $record) => $record->messages()->where('feedback', -1)->count())
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'gray'),
                Tables\Columns\TextColumn::make('last_message_at')->dateTime()->since()->sortable(),
            ])
            ->defaultSort('last_message_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('has_lead')->query(fn ($query) => $query->whereNotNull('lead_id'))->label('Converted to lead'),
                Tables\Filters\Filter::make('has_negative_feedback')
                    ->query(fn ($query) => $query->whereHas('messages', fn ($q) => $q->where('feedback', -1)))
                    ->label('Has 👎 feedback'),
            ])
            ->actions([Tables\Actions\ViewAction::make()])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConversations::route('/'),
            'view' => Pages\ViewConversation::route('/{record}'),
        ];
    }
}
