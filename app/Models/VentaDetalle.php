<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaDetalle extends Model
{
    use HasFactory;

    protected $table = 'venta_detalle'; // Especifica el nombre de la tabla

    protected $fillable = [
        'venta_id',
        'habitacion_id',
        'servicio_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // Relación con venta
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    // Relación con habitación
    public function habitacion()
    {
        return $this->belongsTo(Habitacion::class);
    }

    // Relación con servicio
    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }
}