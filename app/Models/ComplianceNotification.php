<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplianceNotification extends Model
{
    use HasFactory;

    protected $table = 'compliance_notifications';

    protected $fillable = [
        'scan_id',
        'intended_domain',
        'detected_domain',
        'full_path',
        // 'timestamp' - timestamp будет автоматически установлен Laravel
    ];
}
