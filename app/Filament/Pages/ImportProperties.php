<?php

namespace App\Filament\Pages;

use App\Services\PropertyImportService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ImportProperties extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static ?string $navigationGroup = 'Tools';
    protected static ?string $navigationLabel = 'Import from URL';
    protected static ?string $title = 'Import Property / Project from URL';
    protected static string $view = 'filament.pages.import-properties';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'import_as' => 'project',
            'category' => 'project',
            'type' => 'apartment',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('url')
                    ->label('Source URL')
                    ->url()
                    ->required()
                    ->placeholder('https://northernland.com/en/...')
                    ->helperText('Works with Northernland project pages. The system fetches images, detects amenities and generates unique SEO content automatically.'),
                Select::make('import_as')
                    ->label('Import As')
                    ->options(['project' => 'Project', 'property' => 'Property'])
                    ->default('project')
                    ->live()
                    ->required(),
                Select::make('category')
                    ->label('Category')
                    ->options([
                        'project' => 'New Project',
                        'resale' => 'Resale',
                        'daily_rental' => 'Daily Rental',
                        'long_term_rental' => 'Long-Term Rental',
                    ])
                    ->default('project')
                    ->visible(fn ($get) => $get('import_as') === 'property'),
                Select::make('type')
                    ->label('Property Type')
                    ->options([
                        'apartment' => 'Apartment', 'villa' => 'Villa', 'penthouse' => 'Penthouse',
                        'studio' => 'Studio', 'townhouse' => 'Townhouse',
                    ])
                    ->default('apartment')
                    ->visible(fn ($get) => $get('import_as') === 'property'),
            ])
            ->statePath('data');
    }

    public function import(PropertyImportService $service): void
    {
        $data = $this->form->getState();

        try {
            if (($data['import_as'] ?? 'project') === 'project') {
                $record = $service->importProject($data['url']);
                $label = 'Project';
            } else {
                $record = $service->importProperty($data['url'], $data['category'] ?? 'resale', $data['type'] ?? 'apartment');
                $label = 'Property';
            }

            Notification::make()
                ->title("{$label} imported successfully")
                ->body("Created: " . ($record->translation('en')?->title ?? $record->slug) . ". Review and publish it from the resource list.")
                ->success()
                ->send();

            $this->form->fill(['import_as' => 'project', 'category' => 'project', 'type' => 'apartment']);
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Import failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
