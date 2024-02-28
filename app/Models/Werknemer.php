<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Werknemer extends Model {
    public function roosters() {
        return $this->hasMany(Rooster::class);
    }
}
