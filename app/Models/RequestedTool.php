<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestedTool extends Model
{
    protected $table = 'requested_tools';
    protected $primaryKey = 'requested_tool_id';
    protected $fillable = [
        'user_id',
        'project_id',
        'tool_id',
        'testing_tool_id',
        'description',
        'status',
    ];
    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
    public function tool() {
        return $this->belongsTo(DevelopmentTool::class, 'tool_id');
    }
    public function testingTool() {
        return $this->belongsTo(TestingTool::class, 'testing_tool_id');
    }
}
