<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DomainToScan extends Model
{
    use HasFactory;

    protected $table = 'domains_to_scan';

    protected $fillable = [
        'domain',
        'geo',
        'status',
        // 'created_date' - created_date будет автоматически установлен Laravel
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
