<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestingTool extends Model
{
    protected $table = 'testing_tools';
    protected $primaryKey = 'testing_tool_id';
    protected $fillable = [
        'testing_tool_name',
        'testing_team_id',
        'license_key'
    ];

    public function testingTeam()
    {
        return $this->belongsTo(TestingTeam::class, 'testing_team_id', 'testing_team_id');
    }
}
