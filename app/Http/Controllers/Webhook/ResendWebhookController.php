<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\OutreachCampaignModel;
use App\Infrastructure\Persistence\Eloquent\OutreachLeadModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Svix\Exception\WebhookVerificationException;
use Svix\Webhook;

class ResendWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $this->verifySignature($request);

        $type = $request->input('type');
        $email = $request->input('data.to.0')
            ?? $request->input('data.email_address')
            ?? $request->input('data.to');

        if (!$email || !$type) {
            return response()->json(['status' => 'ignored']);
        }

        Log::info('Resend webhook received', ['type' => $type, 'email' => $email]);

        $lead = OutreachLeadModel::where('email', $email)
            ->where('outreach_status', 'sent')
            ->first();

        if (!$lead) {
            return response()->json(['status' => 'no_match']);
        }

        $updated = match ($type) {
            'email.bounced', 'email.complained' => 'bounced',
            default => null,
        };

        if ($updated) {
            $lead->update(['outreach_status' => $updated]);
            OutreachCampaignModel::refreshStats($lead->category, $lead->city);
            Log::info('Lead status updated via webhook', ['email' => $email, 'status' => $updated]);
        }

        return response()->json(['status' => 'ok']);
    }

    private function verifySignature(Request $request): void
    {
        $secret = config('services.resend.webhook_secret');

        if (!$secret) {
            abort(500, 'Webhook secret not configured.');
        }

        try {
            $wh = new Webhook($secret);
            $wh->verify($request->getContent(), [
                'svix-id' => $request->header('svix-id'),
                'svix-timestamp' => $request->header('svix-timestamp'),
                'svix-signature' => $request->header('svix-signature'),
            ]);
        } catch (WebhookVerificationException) {
            abort(403, 'Invalid webhook signature.');
        }
    }
}
