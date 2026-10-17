<?php

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\TenantModel;
use App\Mail\TrialEndingMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTrialEndingReminders extends Command
{
    protected $signature = 'trial:send-reminders';

    protected $description = 'Email verified users whose free trial ends within 3 days and who have not subscribed yet';

    public function handle(): int
    {
        $tenants = TenantModel::whereNull('trial_reminder_sent_at')
            ->whereBetween('trial_ends_at', [now(), now()->addDays(3)])
            ->get();

        $sent = 0;

        foreach ($tenants as $tenant) {
            if ($tenant->subscribed('default')) {
                continue;
            }

            $tenant->users()
                ->whereNotNull('email_verified_at')
                ->each(function (User $user) use ($tenant, &$sent) {
                    Mail::to($user->email)->send(new TrialEndingMail($user, $tenant->trial_ends_at));
                    $sent++;
                });

            $tenant->update(['trial_reminder_sent_at' => now()]);
        }

        $this->info("Sent {$sent} trial ending reminders.");

        return self::SUCCESS;
    }
}
