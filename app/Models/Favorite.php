<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'pokemon_id',
        'pokemon_name',
        'pokemon_image',
    ];

    /**
     * Relación: Un favorito pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
