<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    protected $fillable = [
        'user_id',
        'email_alerts',
        'push_notifications',
        'portal_submission_alerts',
        'sync_error_alerts',
        'auto_sync_on_reconnect',
        'sync_interval_minutes',
        'portal_api_endpoint',
        'default_export_format',
    ];

    protected $casts = [
        'email_alerts' => 'boolean',
        'push_notifications' => 'boolean',
        'portal_submission_alerts' => 'boolean',
        'sync_error_alerts' => 'boolean',
        'auto_sync_on_reconnect' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
