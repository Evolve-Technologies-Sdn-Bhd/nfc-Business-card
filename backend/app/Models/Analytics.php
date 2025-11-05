<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Analytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'trackable_type',
        'trackable_id',
        'action',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'platform',
        'country',
        'city',
        'referrer',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function trackable()
    {
        return $this->morphTo();
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeByTrackable($query, $trackableType, $trackableId)
    {
        return $query->where('trackable_type', $trackableType)
                    ->where('trackable_id', $trackableId);
    }
}
