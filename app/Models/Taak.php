<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Taak extends Model
{
    protected $table = 'taken'; // De naam van de database tabel waarin de taken worden opgeslagen

    protected $fillable = ['naam', 'omschrijving', 'deadline']; // Vulbare velden voor de taak

    // Voorbeeld van een relatie met een ander model, bijvoorbeeld gebruikers
    public function gebruikers()
    {
        return $this->belongsToMany(User::class, 'gebruiker_taken', 'taak_id', 'gebruiker_id');
    }

    public function taak()
    {
        return $this->belongsTo(Taak::class, 'taak_id');
    }
}
