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
        'team_id',
        'license_expiry_date'
    ];

    public function requestedTools()
    {
        return $this->hasMany(RequestedTool::class, 'tool_id', 'tool_id');
    }

    public function developmentTeam()
    {
        return $this->belongsTo(DevelopmentTeam::class, 'team_id', 'team_id');
    }
}
