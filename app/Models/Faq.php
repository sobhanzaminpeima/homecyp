<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = ['category', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    public function translations()
    {
        return $this->hasMany(FaqTranslation::class);
    }

    public function translation($locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        return $this->translations()->where('locale', $locale)->first()
            ?? $this->translations()->where('locale', 'en')->first();
    }

    public function getQuestionAttribute(): string
    {
        return $this->translation()?->question ?? '';
    }

    public function getAnswerAttribute(): string
    {
        return $this->translation()?->answer ?? '';
    }
}
