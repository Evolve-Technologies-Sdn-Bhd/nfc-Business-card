<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Company-wide settings of a Business tenant (one row per owner).
 *
 * @property array|null $shared_fields  landing-page column => value
 * @property array|null $locked_fields  landing-page columns members may not edit
 */
class BusinessSetting extends Model
{
    protected $fillable = [
        'owner_id',
        'shared_fields',
        'locked_fields',
        'card_template_id',
    ];

    protected $casts = [
        'shared_fields' => 'array',
        'locked_fields' => 'array',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function cardTemplate()
    {
        return $this->belongsTo(CardTemplate::class);
    }
}
