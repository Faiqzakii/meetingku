<?php

namespace App\Models;

use CodeIgniter\Model;

class WaApiKeyModel extends Model
{
    protected $table = 'wa_api_keys';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['name', 'plain_key', 'key_hash', 'prefix', 'is_active', 'last_used_at', 'revoked_at'];
    protected array $casts = ['is_active' => 'boolean'];
}
