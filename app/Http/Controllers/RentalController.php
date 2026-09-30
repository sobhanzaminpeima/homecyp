<?php

namespace App\Http\Controllers;

use App\Models\Property;

class RentalController extends Controller
{
    public function daily()
    {
        $rentals = Property::active()->category('daily_rental')
            ->with('translations')->latest()->paginate(12);

        return view('rentals.daily', compact('rentals'));
    }

    public function showDaily(string $slug)
    {
        $rental = Property::where('slug', $slug)
            ->where('category', 'daily_rental')->active()
            ->with(['translations', 'agent'])->firstOrFail();

        $rental->increment('views');

        $related = Property::active()->category('daily_rental')
            ->where('id', '!=', $rental->id)->with('translations')->take(4)->get();

        $seo = [
            'title' => $rental->translation()?->meta_title ?: $rental->translation()?->title,
            'description' => $rental->translation()?->meta_description,
        ];

        return view('properties.show', ['property' => $rental, 'related' => $related, 'seo' => $seo]);
    }

    public function longTerm()
    {
        $rentals = Property::active()->category('long_term_rental')
            ->with('translations')->latest()->paginate(12);

        return view('rentals.long-term', compact('rentals'));
    }

    public function showLongTerm(string $slug)
    {
        $rental = Property::where('slug', $slug)
            ->where('category', 'long_term_rental')->active()
            ->with(['translations', 'agent'])->firstOrFail();

        $rental->increment('views');

        $related = Property::active()->category('long_term_rental')
            ->where('id', '!=', $rental->id)->with('translations')->take(4)->get();

        $seo = [
            'title' => $rental->translation()?->meta_title ?: $rental->translation()?->title,
            'description' => $rental->translation()?->meta_description,
        ];

        return view('properties.show', ['property' => $rental, 'related' => $related, 'seo' => $seo]);
    }
}
