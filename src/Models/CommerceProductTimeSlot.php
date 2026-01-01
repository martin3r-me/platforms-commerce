<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommerceProductTimeSlot extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'commerce_product_time_slots';

    protected $fillable = [
        'start',
        'end',
        'day_of_week',
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
        'day_of_week' => 'integer',
    ];

    public function timeSlotable()
    {
        return $this->morphTo();
    }
}

