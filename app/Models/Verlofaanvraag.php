<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verlofaanvraag extends Model
{
    use HasFactory;

    protected $table = 'verlofaanvragen'; // Expliciete aanduiding van de tabelnaam
    protected $dates = ['datum', 'eind_datum'];

    protected $fillable = [
        'user_id',
        'datum',
        'eind_datum', // Zorg ervoor dat dit toegevoegd is
        'reden',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
