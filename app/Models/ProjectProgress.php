<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectProgress extends Model
{
    use HasFactory;

    protected $table = 'project_progress';
    protected $primaryKey = 'project_progress_id';
    protected $fillable = [
        'project_id',
        'progress_date',
        'image_path',
        'file_path',
        'progress_description',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }
}
