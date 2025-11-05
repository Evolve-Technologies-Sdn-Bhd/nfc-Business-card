<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NfcTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nfc_id',
        'nfc_card_id',
        'name',
        'status',
        'tap_count',
        'last_tapped_at',
    ];

    protected $casts = [
        'tap_count' => 'integer',
        'last_tapped_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function analytics()
    {
        return $this->morphMany(Analytics::class, 'trackable');
    }

    public function nfcCard()
    {
        return $this->belongsTo(NfcCard::class, 'nfc_card_id', 'nfc_card_id');
    }
}
