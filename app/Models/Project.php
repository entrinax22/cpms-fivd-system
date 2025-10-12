<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'projects';
    protected $primaryKey = 'project_id';
    protected $fillable = [
        'project_name',
        'client_id',
        'manager_id',
        'start_date',
        'estimated_end_date',
        'project_description',
        'status'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

    public function manager()
    {
        return $this->hasOne(ProjectManager::class, 'manager_id', 'manager_id');
    }

    public function project_progress()
    {
        return $this->hasMany(ProjectProgress::class, 'project_id', 'project_id');
    }
}
