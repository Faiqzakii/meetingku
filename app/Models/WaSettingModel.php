<?php

namespace App\Models;

use CodeIgniter\Model;

class WaSettingModel extends Model
{
    protected $table = 'wa_settings';
    protected $primaryKey = 'key';
    protected $returnType = 'array';
    protected $useAutoIncrement = false;
    protected $useTimestamps = true;
    protected $allowedFields = ['key', 'value'];
}
