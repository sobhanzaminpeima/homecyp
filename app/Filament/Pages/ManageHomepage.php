<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageHomepage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';
    protected static ?string $navigationGroup = 'Appearance';
    protected static ?string $navigationLabel = 'Homepage & Theme';
    protected static ?string $title = 'Homepage & Theme Editor';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.manage-homepage';

    public ?array $data = [];

    public static array $sections = [
        'section_tabs' => 'Browse by Category (tabs)',
        'section_projects' => 'Featured Projects',
        'section_why' => 'Why Invest in North Cyprus',
        'section_properties' => 'Featured Properties',
        'section_rentals' => 'Daily Rentals showcase',
        'section_testimonials' => 'Testimonials',
        'section_lead' => 'Lead Capture Form',
        'section_blog' => 'Blog (From Our Blog)',
        'section_faq' => 'FAQ',
    ];

    protected array $textKeys = ['hero_title', 'hero_subtitle', 'theme_accent'];

    public function mount(): void
    {
        $state = [];
        foreach (array_keys(static::$sections) as $key) {
            $state[$key] = SiteSetting::get($key, '1') === '1';
        }
        $state['show_airbnb'] = SiteSetting::get('show_airbnb', '1') === '1';
        $state['hero_title'] = SiteSetting::get('hero_title');
        $state['hero_subtitle'] = SiteSetting::get('hero_subtitle');
        $state['theme_accent'] = SiteSetting::get('theme_accent', '#C9A84C');
        $this->form->fill($state);
    }

    public function form(Form $form): Form
    {
        $toggles = [];
        foreach (static::$sections as $key => $label) {
            $toggles[] = Toggle::make($key)->label($label)->default(true)->inline(false);
        }

        return $form->schema([
            Section::make('Hero Section')->columns(1)->schema([
                TextInput::make('hero_title')->label('Hero Title (leave blank for default)')->placeholder('Luxury Real Estate & Daily Rentals'),
                Textarea::make('hero_subtitle')->label('Hero Subtitle')->rows(2)->placeholder('Premium investment opportunities...'),
            ]),
            Section::make('Theme')->schema([
                ColorPicker::make('theme_accent')->label('Accent / Gold Color')->default('#C9A84C'),
            ]),
            Section::make('Airbnb Listings')
                ->description('Turn this off to hide every Airbnb listing from the homepage (Daily Rentals showcase, category tabs and featured properties). The listings stay published on their own pages.')
                ->schema([
                    Toggle::make('show_airbnb')->label('Show Airbnb listings on the homepage')->default(true)->inline(false),
                ]),
            Section::make('Visible Sections')->description('Toggle which homepage sections are shown.')->columns(3)->schema($toggles),
        ])->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        foreach (array_keys(static::$sections) as $key) {
            SiteSetting::set($key, !empty($state[$key]) ? '1' : '0');
        }
        SiteSetting::set('show_airbnb', !empty($state['show_airbnb']) ? '1' : '0');
        SiteSetting::set('hero_title', $state['hero_title'] ?? '');
        SiteSetting::set('hero_subtitle', $state['hero_subtitle'] ?? '');
        SiteSetting::set('theme_accent', $state['theme_accent'] ?: '#C9A84C');

        Notification::make()->title('Homepage updated')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [\Filament\Actions\Action::make('save')->label('Save Changes')->submit('save')];
    }
}
