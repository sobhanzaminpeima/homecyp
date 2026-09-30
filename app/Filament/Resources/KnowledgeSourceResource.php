<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KnowledgeSourceResource\Pages;
use App\Models\KnowledgeSource;
use App\Services\KnowledgeIngestService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KnowledgeSourceResource extends Resource
{
    protected static ?string $model = KnowledgeSource::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'AI';
    protected static ?string $navigationLabel = 'Knowledge Base';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('title')->required()->columnSpanFull(),
                Forms\Components\Select::make('type')
                    ->options(['manual' => 'Manual text', 'url' => 'Web page', 'pdf' => 'PDF (upload)', 'doc' => 'Word (upload)'])
                    ->default('manual')->required()->live(),
                Forms\Components\Select::make('category')
                    ->options([
                        'area' => 'Area info', 'legal' => 'Legal / Residency', 'project' => 'Project',
                        'faq' => 'FAQ', 'article' => 'Article',
                    ]),
            ]),
            Forms\Components\TextInput::make('source_url')->label('URL')->url()
                ->visible(fn (Forms\Get $get) => $get('type') === 'url')
                ->required(fn (Forms\Get $get) => $get('type') === 'url'),
            Forms\Components\Textarea::make('content')->label('Text content')->rows(10)
                ->visible(fn (Forms\Get $get) => $get('type') === 'manual')
                ->required(fn (Forms\Get $get) => $get('type') === 'manual'),
            Forms\Components\FileUpload::make('file_path')->label('File')->disk('public')->directory('knowledge')
                ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                ->maxSize(20 * 1024) // 20MB
                ->visible(fn (Forms\Get $get) => in_array($get('type'), ['pdf', 'doc']))
                ->helperText('PDF/Word text extraction needs a parser package — not yet installed. Use "Manual text" or "Web page" for now.'),
            Forms\Components\Placeholder::make('status_info')
                ->label('Status')
                ->content(fn (?KnowledgeSource $record) => $record ? ucfirst($record->status).($record->error_message ? " — {$record->error_message}" : '') : 'Not yet indexed')
                ->visible(fn (?KnowledgeSource $record) => $record !== null),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('category')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    'indexed' => 'success', 'processing' => 'warning', 'failed' => 'danger', default => 'gray',
                }),
                Tables\Columns\TextColumn::make('chunks_count')->counts('chunks')->label('Chunks'),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->since()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(['pending' => 'Pending', 'processing' => 'Processing', 'indexed' => 'Indexed', 'failed' => 'Failed']),
                Tables\Filters\SelectFilter::make('category')->options(['area' => 'Area info', 'legal' => 'Legal / Residency', 'project' => 'Project', 'faq' => 'FAQ', 'article' => 'Article']),
            ])
            ->actions([
                Tables\Actions\Action::make('process')
                    ->label('Process')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function (KnowledgeSource $record) {
                        app(KnowledgeIngestService::class)->process($record);
                        Notification::make()->title('Processed: '.$record->fresh()->status)->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKnowledgeSources::route('/'),
            'create' => Pages\CreateKnowledgeSource::route('/create'),
            'edit' => Pages\EditKnowledgeSource::route('/{record}/edit'),
        ];
    }
}
