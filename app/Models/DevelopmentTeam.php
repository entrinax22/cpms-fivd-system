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

    public function developmentTools()
    {
        return $this->hasMany(DevelopmentTool::class, 'team_id', 'team_id');
    }

    /**
     * Users that belong to this development team.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'development_team_user', 'team_id', 'user_id')
                    ->withTimestamps();
    }
}
