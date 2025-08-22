<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestingTool extends Model
{
    protected $table = 'testing_tools';
    protected $primaryKey = 'tersting_tool_id';
    protected $fillable = [
        'testing_tool_name',
        'testing_team_id',
        'license_key'
    ];
}
