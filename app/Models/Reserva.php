<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'habitacion_id',
        'venta_id',
        'fecha_entrada',
        'fecha_salida',
        'noches',
        'adultos',
        'ninos',
        'precio_total',
        'estado',
        'notas'
    ];

    protected $casts = [
        'fecha_entrada' => 'date',
        'fecha_salida' => 'date',
        'precio_total' => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function habitacion()
    {
        return $this->belongsTo(Habitacion::class);
    }

    // Relación con venta (si tienes una relación directa)
    public function venta()
    {
        return $this->hasOne(Venta::class, 'cliente_id', 'cliente_id')
            ->where('monto_total', $this->precio_total)
            ->latest();
    }
}