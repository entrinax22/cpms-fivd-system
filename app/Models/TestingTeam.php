<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestingTeam extends Model
{
    protected $table = 'testing_teams';
    protected $primaryKey = 'testing_team_id';
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

    /**
     * Users that belong to this testing team.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'testing_team_user', 'testing_team_id', 'user_id')
                    ->withTimestamps();
    }
}
