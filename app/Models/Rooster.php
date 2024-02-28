<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Taak;

class Rooster extends Model
{
    // De naam van de tabel in de database
    protected $table = 'roosters';

    // Velden die toewijsbaar zijn
    protected $fillable = [
        'user_id', 'start_tijd', 'eind_tijd', 'duur_in_uren',
        'datum', 'taak_id', 'approval_status', 'current_user_id', 'selected_user_id'
    ];

    // Velden die als datum moeten worden behandeld
    protected $dates = ['datum', 'starttijd', 'eindtijd'];

    // Definieer een relatie met de User model
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relatie met de huidige gebruiker
    public function currentUser()
    {
        return $this->belongsTo(User::class, 'current_user_id');
    }

    // Relatie met de geselecteerde gebruiker
    public function selectedUser()
    {
        return $this->belongsTo(User::class, 'selected_user_id');
    }

    // Relatie met de Taak model
    public function taak()
    {
        return $this->belongsTo(Taak::class, 'taak_id');
    }
}
