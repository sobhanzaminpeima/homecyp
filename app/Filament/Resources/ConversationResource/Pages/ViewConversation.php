<?php

namespace App\Filament\Resources\ConversationResource\Pages;

use App\Filament\Resources\ConversationResource;
use Filament\Resources\Pages\ViewRecord;

class ViewConversation extends ViewRecord
{
    protected static string $resource = ConversationResource::class;
    protected static string $view = 'filament.resources.conversation-resource.pages.view-conversation';

    public function getMessages()
    {
        return $this->record->messages()->orderBy('id')->get();
    }
}
