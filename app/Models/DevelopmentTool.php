<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevelopmentTool extends Model
{
    protected $table = 'development_tools';
    protected $primaryKey = 'tool_id';
    protected $fillable = [
        'tool_name',
        'tool_version',
        'license_expiry_date'
    ];
}
