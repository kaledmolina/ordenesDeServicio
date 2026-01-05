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
        
        // Detailed Metrics
        'arrived_on_time',
        'is_friendly',
        'problem_solved',
        'wears_uniform',
        'left_clean',
        
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
        'arrived_on_time' => 'boolean',
        'is_friendly' => 'boolean',
        'problem_solved' => 'boolean',
        'wears_uniform' => 'boolean',
        'left_clean' => 'boolean',
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
