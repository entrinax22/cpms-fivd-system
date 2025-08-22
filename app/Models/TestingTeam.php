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
}
