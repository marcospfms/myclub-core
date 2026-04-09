<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StaffRole extends Model
{
    use HasFactory;

    protected $table = 'staff_roles';

    protected $fillable = ['name', 'label_key', 'description_key', 'icon'];
}
