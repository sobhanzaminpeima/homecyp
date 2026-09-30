<?php
namespace App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource;
use App\Models\PostTranslation;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditPost extends EditRecord {
    protected static string $resource = PostResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
    protected function mutateFormDataBeforeFill(array $data): array {
        foreach (['en','tr'] as $l) {
            $t=$this->record->translations()->where('locale',$l)->first();
            if ($t) { $data["{$l}_title"]=$t->title; $data["{$l}_excerpt"]=$t->excerpt; $data["{$l}_body"]=$t->body; $data["{$l}_meta_title"]=$t->meta_title; $data["{$l}_meta_description"]=$t->meta_description; }
        }
        return $data;
    }
    protected function afterSave(): void {
        $d=$this->form->getRawState();
        foreach (['en','tr'] as $l) {
            if (!empty($d["{$l}_title"])) {
                PostTranslation::updateOrCreate(['post_id'=>$this->record->id,'locale'=>$l],[
                    'title'=>$d["{$l}_title"]??'','excerpt'=>$d["{$l}_excerpt"]??null,'body'=>$d["{$l}_body"]??null,
                    'meta_title'=>$d["{$l}_meta_title"]??null,'meta_description'=>$d["{$l}_meta_description"]??null,
                ]);
            }
        }
    }
}
