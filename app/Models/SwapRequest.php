<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SwapRequest extends Model
{
    use HasFactory;

    public function currentUser()
    {
        return $this->belongsTo(User::class, 'current_user_id');
    }

    public function selectedUser()
    {
        return $this->belongsTo(User::class, 'selected_user_id');
    }
}
