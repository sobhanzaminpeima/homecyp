<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Services\RecaptchaService;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'required|string|max:30',
            'message' => 'nullable|string|max:2000',
            'type' => 'nullable|string',
            'property_id' => 'nullable|exists:properties,id',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        // Spam protection via Google reCAPTCHA (skipped automatically if not configured).
        if (!RecaptchaService::verify($request->input('g-recaptcha-response'), $request->ip())) {
            $message = __('Spam verification failed. Please try again.');
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return back()->withInput()->withErrors(['captcha' => $message]);
        }

        $validated['source'] = 'website';
        $validated['status'] = 'new';
        $validated['preferred_language'] = app()->getLocale();
        $validated['meta_data'] = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'page' => $request->headers->get('referer'),
        ];

        Lead::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => __('Thank you! We will contact you soon.')]);
        }

        return back()->with('success', __('Thank you! We will contact you soon.'));
    }

    public function contact(Request $request)
    {
        return $this->store($request->merge(['type' => 'contact']));
    }
}
