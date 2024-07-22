<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostbackLog extends Model
{
    use HasFactory;

    protected $table = 'postback_logs';

    protected $fillable = [
        'notification_id',
        'json_data',
        // 'received_date' - received_date будет автоматически установлен Laravel
    ];

    public function complianceNotification()
    {
        return $this->belongsTo(ComplianceNotification::class, 'notification_id');
    }
}
