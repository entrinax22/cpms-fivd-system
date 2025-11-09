<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'must_change_password',
        'password_expires_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'password_expires_at' => 'datetime',
        ];
    }

    public function requestedTools()
    {
        return $this->hasMany(RequestedTool::class, 'user_id');
    }

    /**
     * The development teams the user belongs to.
     */
    public function developmentTeams()
    {
        return $this->belongsToMany(DevelopmentTeam::class, 'development_team_user', 'user_id', 'team_id')
                    ->withTimestamps();
    }

    /**
     * The testing teams the user belongs to.
     */
    public function testingTeams()
    {
        return $this->belongsToMany(TestingTeam::class, 'testing_team_user', 'user_id', 'testing_team_id')
                    ->withTimestamps();
    }
}
