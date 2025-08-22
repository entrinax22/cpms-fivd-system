<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevelopmentTeam extends Model
{
    protected $table = 'development_teams';
    protected $primaryKey = 'team_id';
    protected $fillable = [
        'manager_id',
        'team_name',
        'team_size',
        'specialization'
    ];
}
