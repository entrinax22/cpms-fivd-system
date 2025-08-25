<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevelopmentTeam extends Model
{
    protected $table = 'development_teams';
    protected $primaryKey = 'team_id';
    protected $fillable = [
        'team_name',
        'team_size',
        'specialization',
        'manager_id'
    ];

    public function projectManager()
    {
        return $this->belongsTo(ProjectManager::class, 'manager_id', 'manager_id');
    }
}
