<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageLlmSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $navigationGroup = 'AI';
    protected static ?string $navigationLabel = 'LLM Settings';
    protected static ?string $title = 'LLM Settings';
    protected static string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    protected array $keys = ['llm_chat_provider', 'llm_embedding_provider', 'ai_system_prompt_override'];

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
        $providers = ['nvidia_nim' => 'NVIDIA NIM', 'openai' => 'OpenAI', 'anthropic' => 'Anthropic'];

        return $form
            ->schema([
                Section::make('Provider')
                    ->description('Swap the underlying LLM without touching code. API keys are set in .env.')
                    ->columns(2)
                    ->schema([
                        Select::make('llm_chat_provider')->label('Chat Provider')->options($providers)->native(false),
                        Select::make('llm_embedding_provider')->label('Embedding Provider')->options($providers)->native(false)
                            ->helperText('Anthropic has no embeddings endpoint — pick NVIDIA NIM or OpenAI for this.'),
                    ]),
                Section::make('Behavior')->schema([
                    Textarea::make('ai_system_prompt_override')
                        ->label('Extra system prompt instructions')
                        ->helperText('Appended to the base HomeCyp system prompt for every conversation (e.g. tone, current promotions).')
                        ->rows(6)
                        ->columnSpanFull(),
                ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        foreach ($this->form->getState() as $key => $value) {
            SiteSetting::set($key, $value ?? '');
        }

        Notification::make()->title('LLM settings saved')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')->label('Save Settings')->submit('save'),
        ];
    }
}
