<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'clients';
    protected $primaryKey = 'client_id';
    protected $fillable = [
        'client_name',
        'contact_information',
        'registration_date',
        'client_type'
    ];
}
