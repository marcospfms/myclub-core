<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeamStaff extends Model
{
    use HasFactory;

    protected $table = 'team_staff';

    protected $fillable = ['team_id', 'staff_member_id'];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class, 'staff_member_id', 'user_id');
    }
}
