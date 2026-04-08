<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffRole extends Model
{
    protected $table = 'staff_roles';

    protected $fillable = ['name', 'label_key', 'description_key', 'icon'];
}
