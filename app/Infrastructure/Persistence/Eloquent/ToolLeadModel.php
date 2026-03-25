<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ToolLeadModel extends Model
{
    use HasUuids;

    protected $table = 'tool_leads';

    protected $fillable = ['email', 'place_id', 'source'];
}
