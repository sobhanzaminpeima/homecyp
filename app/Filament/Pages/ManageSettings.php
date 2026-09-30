<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Tools';
    protected static ?string $navigationLabel = 'Site Settings';
    protected static ?string $title = 'Site Settings';
    protected static string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    protected array $keys = [
        'site_name', 'site_tagline', 'site_email', 'site_phone', 'site_whatsapp', 'site_address',
        'facebook_url', 'instagram_url', 'google_maps_embed',
        'stat_happy_clients', 'stat_years_experience',
        'google_analytics_id', 'meta_pixel_id', 'recaptcha_site_key', 'recaptcha_secret_key',
    ];

    public function mount(): void
    {
        $state = [];
        foreach ($this->keys as $key) {
            $state[$key] = SiteSetting::get($key);
        }
        $this->form->fill($state);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('General')->columns(2)->schema([
                    TextInput::make('site_name'),
                    TextInput::make('site_tagline'),
                    TextInput::make('stat_happy_clients')->label('Stat: Happy Clients'),
                    TextInput::make('stat_years_experience')->label('Stat: Years Experience'),
                ]),
                Section::make('Contact')->columns(2)->schema([
                    TextInput::make('site_email')->email(),
                    TextInput::make('site_phone'),
                    TextInput::make('site_whatsapp')->helperText('Digits only, e.g. 905338456497'),
                    TextInput::make('site_address'),
                    Textarea::make('google_maps_embed')->label('Google Maps Embed URL')->columnSpanFull(),
                ]),
                Section::make('Social Media')->columns(2)->schema([
                    TextInput::make('facebook_url')->url(),
                    TextInput::make('instagram_url')->url(),
                ]),
                Section::make('Analytics & Tracking')->columns(2)->schema([
                    TextInput::make('google_analytics_id')->label('Google Analytics ID')->placeholder('G-XXXXXXXXXX'),
                    TextInput::make('meta_pixel_id')->label('Meta Pixel ID'),
                ]),
                Section::make('Security — Google reCAPTCHA v3')->columns(2)->schema([
                    TextInput::make('recaptcha_site_key')->label('reCAPTCHA Site Key'),
                    TextInput::make('recaptcha_secret_key')->label('reCAPTCHA Secret Key')->password()->revealable(),
                ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        foreach ($this->form->getState() as $key => $value) {
            SiteSetting::set($key, $value ?? '');
        }

        Notification::make()->title('Settings saved')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')->label('Save Settings')->submit('save'),
        ];
    }
}
