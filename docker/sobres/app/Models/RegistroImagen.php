<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RegistroImagen extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'registro_imagenes';
    use HasFactory;
    use HasUuids; // para visualizar bien los id creados con uuid
    protected $fillable = [
        'id',
        'sobre_plantilla_id',
        'imagen',
        'title',
        'tipo',
        'orden',
        'ancho',
        'alto',
    ];

    public function sobrePlantilla()
    {
        return $this->belongsTo(SobrePlantilla::class, 'sobre_plantilla_id', 'id');
    }
}
