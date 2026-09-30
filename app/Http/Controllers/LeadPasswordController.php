<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LeadPasswordController extends Controller
{
    public function request(Request $request)
    {
        $data = $request->validate(['email' => 'required|email']);
        $key = 'lead-password:'.hash('sha256', strtolower($data['email']).'|'.$request->ip());
        abort_if(RateLimiter::tooManyAttempts($key, 3), 429);
        RateLimiter::hit($key, 300);

        if ($lead = Lead::where('email', $data['email'])->first()) {
            $token = Str::random(64);
            DB::table('lead_password_reset_tokens')->updateOrInsert(['email' => $lead->email], [
                'token' => Hash::make($token), 'created_at' => now(),
            ]);
            $url = route('lead.password.edit', ['token' => $token, 'email' => $lead->email]);
            Mail::raw(__('Reset your HomeCyp conversation password: :url', ['url' => $url]),
                fn ($mail) => $mail->to($lead->email)->subject(__('Reset your HomeCyp password')));
        }

        return back()->with('status', __('If that email exists, a password reset link has been sent.'));
    }

    public function edit(Request $request, string $token)
    {
        return view('auth.lead-reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function update(Request $request)
    {
        $data = $request->validate(['email' => 'required|email', 'token' => 'required', 'password' => 'required|confirmed|min:8']);
        $record = DB::table('lead_password_reset_tokens')->where('email', $data['email'])->first();
        if (!$record || !Hash::check($data['token'], $record->token) || now()->diffInMinutes($record->created_at) > 30) {
            return back()->withErrors(['email' => __('This password reset link is invalid or expired.')]);
        }
        Lead::where('email', $data['email'])->update(['password' => Hash::make($data['password'])]);
        DB::table('lead_password_reset_tokens')->where('email', $data['email'])->delete();
        return redirect('/')->with('status', __('Your password has been reset. You can now sign in.'));
    }
}
