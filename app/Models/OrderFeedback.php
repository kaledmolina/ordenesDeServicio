<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderFeedback extends Model
{
    use HasFactory;
    
    protected $table = 'order_feedback';

    protected $fillable = [
        'orden_id',
        'technician_id',
        'rating',
        'improvements',
        'comment',
    ];

    protected $casts = [
        'improvements' => 'array',
        'rating' => 'integer',
    ];

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
