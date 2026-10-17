New signup on QuickFeedback.

Name: {{ $user->name }}
Email: {{ $user->email }}
Signed up with: {{ $user->google_id ? 'Google' : 'email and password' }}
Email verified: {{ $user->hasVerifiedEmail() ? 'yes' : 'not yet' }}
When: {{ $user->created_at?->toDayDateTimeString() }} UTC

Reach out while they're still looking around: reply to them personally and offer to set it up.
