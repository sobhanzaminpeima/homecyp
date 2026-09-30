<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageBranding extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'Appearance';
    protected static ?string $navigationLabel = 'Logo & Branding';
    protected static ?string $title = 'Logo, Header & Branding';
    protected static ?int $navigationSort = 0;
    protected static string $view = 'filament.pages.manage-branding';

    public ?array $data = [];

    protected array $imageKeys = ['logo_path', 'logo_white_path', 'favicon_path', 'hero_image_path'];
    protected array $textKeys = ['header_phone', 'header_cta_text'];

    public function mount(): void
    {
        $state = [];
        foreach ($this->imageKeys as $k) {
            $state[$k] = SiteSetting::get($k);
        }
        foreach ($this->textKeys as $k) {
            $state[$k] = SiteSetting::get($k);
        }
        $this->form->fill($state);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Logo')->columns(2)->description('Upload your brand logos. Leave blank to use the default HomeCyp logo.')->schema([
                FileUpload::make('logo_path')->label('Main Logo (dark backgrounds use light areas)')->image()
                    ->disk('public')->directory('branding')->visibility('public')->imageEditor(),
                FileUpload::make('logo_white_path')->label('White Logo (for footer / dark header)')->image()
                    ->disk('public')->directory('branding')->visibility('public')->imageEditor(),
                FileUpload::make('favicon_path')->label('Favicon')->image()
                    ->disk('public')->directory('branding')->visibility('public'),
            ]),
            Section::make('Header Hero Image & Text')->columns(2)->schema([
                FileUpload::make('hero_image_path')->label('Homepage Hero Background')->image()
                    ->disk('public')->directory('branding')->visibility('public')->imageEditor()->columnSpanFull(),
                TextInput::make('header_phone')->label('Header Phone')->placeholder('+90 533 845 64 97'),
                TextInput::make('header_cta_text')->label('Header CTA Text')->placeholder('Call us'),
            ]),
        ])->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        foreach (array_merge($this->imageKeys, $this->textKeys) as $k) {
            SiteSetting::set($k, $state[$k] ?? '');
        }
        Notification::make()->title('Branding updated')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [\Filament\Actions\Action::make('save')->label('Save Branding')->submit('save')];
    }
}
