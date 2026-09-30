<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    protected $fillable = ['company_name', 'contact_email', 'contact_phone', 'status'];

    public function campaigns()
    {
        return $this->hasMany(SponsorCampaign::class);
    }
}
