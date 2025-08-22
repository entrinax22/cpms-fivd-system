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
}
