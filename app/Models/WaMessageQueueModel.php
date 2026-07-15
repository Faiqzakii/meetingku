<?php

namespace App\Models;

use CodeIgniter\Model;

class WaMessageQueueModel extends Model
{
    protected $table = 'wa_message_queue';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'api_key_id', 'to_number', 'message', 'meta_kind', 'meta_meeting_id', 'status', 'attempts', 'max_attempts',
        'scheduled_at', 'processing_at', 'sent_at', 'failed_at', 'last_error', 'provider_message_id',
    ];
}
