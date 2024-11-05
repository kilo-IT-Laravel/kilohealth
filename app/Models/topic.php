<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class topic extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['categorie_id', 'name'];

    public function categorie()
    {
        return $this->belongsTo(categorie::class);
    }
}
