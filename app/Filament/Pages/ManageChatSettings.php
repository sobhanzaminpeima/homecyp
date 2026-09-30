<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageChatSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'AI';
    protected static ?string $navigationLabel = 'Chat & Voice Settings';
    protected static ?string $title = 'Chat & Voice Settings';
    protected static string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'chat_welcome_title' => SiteSetting::get('chat_welcome_title', 'Your AI Real Estate Expert for North Cyprus'),
            'chat_welcome_subtitle' => SiteSetting::get('chat_welcome_subtitle', 'Ask me anything about buying, investing, or renting in North Cyprus.'),
            'chat_suggestion_cards' => SiteSetting::get('chat_suggestion_cards') ? json_decode(SiteSetting::get('chat_suggestion_cards'), true) : [
                ['emoji' => '🏡', 'label' => 'Find Property', 'prompt' => 'I want to find a property to buy'],
                ['emoji' => '💰', 'label' => 'Investment Opportunities', 'prompt' => 'I am interested in investment opportunities'],
                ['emoji' => '🏖', 'label' => 'Daily Rentals', 'prompt' => 'Show me daily rentals / Airbnb options'],
                ['emoji' => '📈', 'label' => 'ROI Calculator', 'prompt' => 'Help me calculate ROI for a property'],
            ],
            'voice_enabled' => SiteSetting::get('voice_enabled', '1') === '1',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Welcome Message')->columns(2)->schema([
                    TextInput::make('chat_welcome_title')->columnSpanFull(),
                    Textarea::make('chat_welcome_subtitle')->columnSpanFull(),
                ]),
                Section::make('Suggested Starter Cards')
                    ->description('Shown on the empty chat screen. Clicking a card sends its prompt automatically.')
                    ->schema([
                        Repeater::make('chat_suggestion_cards')
                            ->schema([
                                TextInput::make('emoji')->label('Emoji')->maxLength(4),
                                TextInput::make('label')->label('Button label')->required(),
                                TextInput::make('prompt')->label('Prompt sent on click')->required(),
                            ])
                            ->columns(3)
                            ->maxItems(6)
                            ->label(''),
                    ]),
                Section::make('Voice')->schema([
                    Toggle::make('voice_enabled')->label('Enable microphone / voice input')->helperText('Uses the browser\'s built-in Web Speech API — no external service required.'),
                ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        SiteSetting::set('chat_welcome_title', $state['chat_welcome_title'] ?? '');
        SiteSetting::set('chat_welcome_subtitle', $state['chat_welcome_subtitle'] ?? '');
        SiteSetting::set('chat_suggestion_cards', json_encode($state['chat_suggestion_cards'] ?? []));
        SiteSetting::set('voice_enabled', ($state['voice_enabled'] ?? false) ? '1' : '0');

        Notification::make()->title('Chat settings saved')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')->label('Save Settings')->submit('save'),
        ];
    }
}
