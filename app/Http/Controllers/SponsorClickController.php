<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\SponsorCampaign;
use Illuminate\Support\Facades\Redirect;

class SponsorClickController extends Controller
{
    public function __invoke(SponsorCampaign $campaign)
    {
        $campaign->increment('clicks');

        $property = $campaign->property_id ? Property::find($campaign->property_id) : null;

        return $property
            ? Redirect::route('properties.show', $property->slug)
            : Redirect::route('home');
    }
}
