<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $fillable = [
        'cliente_id',
        'monto_total',
        'metodo_pago',
        'estado',
        'facturada',
        'datos_facturacion',
        'facturada',
        'datos_facturacion',

    ];

    protected $casts = [
        'facturada' => 'boolean',
        'datos_facturacion' => 'array',
        'monto_total' => 'decimal:2',
    ];

    // Relación con Cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // RELACIÓN CON RESERVA (una venta tiene una reserva)
    public function reserva()
    {
        return $this->hasOne(Reserva::class, 'venta_id');
    }

    // Relación con Pago
    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    // Relación con VentaDetalle
    public function detalles()
    {
        return $this->hasMany(VentaDetalle::class);
    }
}