<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

class RedditStrategyReportModel extends Model
{
    protected $table = 'reddit_strategy_reports';

    protected $fillable = [
        'period_start',
        'period_end',
        'report_json',
        'recommendations_json',
        'content_ratio_json',
        'top_performing_json',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'report_json' => 'array',
            'recommendations_json' => 'array',
            'content_ratio_json' => 'array',
            'top_performing_json' => 'array',
        ];
    }
}
