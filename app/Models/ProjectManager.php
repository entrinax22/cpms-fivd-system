<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectManager extends Model
{
    protected $table = 'project_managers';
    protected $primaryKey = 'manager_id';
    protected $fillable = [
        'user_id',
        'manager_name',
        'expertise_area',
        'contact_information',
        'years_of_experience',
    ];

    public function manager(){
        return $this->belongsTo(Project::class, 'manager_id', 'manager_id');
    }
}
