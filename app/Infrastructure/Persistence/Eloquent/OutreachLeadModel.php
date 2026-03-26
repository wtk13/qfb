<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class OutreachLeadModel extends Model
{
    use HasUuids;

    protected $table = 'outreach_leads';

    protected $fillable = [
        'business_name',
        'email',
        'website',
        'phone',
        'rating',
        'reviews',
        'place_id',
        'google_maps_url',
        'category',
        'city',
        'email_status',
        'outreach_status',
        'sent_at',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:1',
            'reviews' => 'integer',
            'landing_clicks' => 'integer',
            'sent_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function scopeSendable($query)
    {
        return $query
            ->where('email_status', 'verified')
            ->where('outreach_status', 'new')
            ->whereNotNull('email');
    }
}
