<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; color: #1a1a1a; max-width: 560px; margin: 0 auto; padding: 24px;">
    <h2 style="color: #C9A84C;">Thanks, {{ $lead->name }} — we've got your viewing request.</h2>

    <p>Your request to view <strong>{{ $property?->title ?? 'a HomeCyp property' }}</strong> has been received.
    One of our agents will contact you shortly to confirm a time.</p>

    @if($property)
        <table style="width: 100%; margin: 16px 0; border-collapse: collapse;">
            <tr><td style="padding: 4px 0; color: #6b7280;">Property</td><td>{{ $property->title }}</td></tr>
            <tr><td style="padding: 4px 0; color: #6b7280;">Location</td><td>{{ $property->region ?? $property->location }}</td></tr>
            <tr><td style="padding: 4px 0; color: #6b7280;">Price</td><td>{{ $property->currency }} {{ number_format($property->price) }}</td></tr>
        </table>
    @endif

    <p style="color: #6b7280; font-size: 14px;">Request reference: #{{ $viewingRequest->id }}</p>

    <p>If you have any questions in the meantime, just reply to this email or continue your conversation
    on the HomeCyp AI chat.</p>

    <p style="margin-top: 32px; color: #6b7280; font-size: 13px;">— The HomeCyp Team</p>
</body>
</html>
