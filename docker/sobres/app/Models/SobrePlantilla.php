<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SobrePlantilla extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'sobre_plantillas';
    
    use HasFactory;
    use HasUuids; // para visualizar bien los id creados con uuid

    protected $fillable = [
        'id',
        'nombre_sobre',
    ];

    public function imagenes()
    {
        return $this->hasMany(RegistroImagen::class, 'sobre_plantilla_id', 'id');
    }
}
