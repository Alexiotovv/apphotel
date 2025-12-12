<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura {{ $numero_factura }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'DejaVu Sans', Arial, sans-serif;
        }
        
        body {
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .container {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo-section h1 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .logo-section h2 {
            font-size: 18px;
            color: #3498db;
            margin-bottom: 15px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .empresa-info, .factura-info {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .empresa-info h3, .factura-info h3 {
            margin-bottom: 10px;
            color: #2c3e50;
            font-size: 16px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .cliente-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        
        .cliente-info h3 {
            margin-bottom: 10px;
            color: #2c3e50;
            font-size: 16px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .cliente-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        
        .cliente-details p {
            margin: 5px 0;
        }
        
        .label {
            font-weight: bold;
            color: #555;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        
        .items-table th {
            background-color: #2c3e50;
            color: white;
            text-align: left;
            padding: 10px;
            font-weight: bold;
        }
        
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .items-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .totales {
            float: right;
            width: 300px;
            margin-top: 20px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .total-row.grand-total {
            background-color: #2c3e50;
            color: white;
            font-weight: bold;
            font-size: 16px;
        }
        
        .footer {
            margin-top: 100px;
            padding-top: 20px;
            border-top: 2px solid #333;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
        
        .payment-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .payment-info h4 {
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
        
        .qr-code img {
            max-width: 150px;
            height: auto;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mb-10 {
            margin-bottom: 10px;
        }
        
        .mb-20 {
            margin-bottom: 20px;
        }
        
        .mt-20 {
            margin-top: 20px;
        }
        
        .border-box {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Encabezado -->
        <div class="header">
            <div class="logo-section">
                <h1>{{ $empresa['nombre'] }}</h1>
                <h2>FACTURA</h2>
                <div class="border-box">
                    <p><strong>N° Factura:</strong> {{ $numero_factura }}</p>
                    <p><strong>Fecha de Emisión:</strong> {{ $fecha_emision }}</p>
                </div>
            </div>
            
            <div class="info-grid">
                <div class="empresa-info">
                    <h3>Información del Emisor</h3>
                    <p><strong>Razón Social:</strong> {{ $empresa['nombre'] }}</p>
                    <p><strong>RUT:</strong> {{ $empresa['rut'] }}</p>
                    <p><strong>Dirección:</strong> {{ $empresa['direccion'] }}</p>
                    <p><strong>Teléfono:</strong> {{ $empresa['telefono'] }}</p>
                    <p><strong>Email:</strong> {{ $empresa['email'] }}</p>
                    <p><strong>Giro:</strong> {{ $empresa['giro'] }}</p>
                </div>
                
                <div class="factura-info">
                    <h3>Información de Factura</h3>
                    <p><strong>N° Factura:</strong> {{ $numero_factura }}</p>
                    <p><strong>Fecha:</strong> {{ $fecha_emision }}</p>
                    <p><strong>Venta ID:</strong> {{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}</p>
                    <p><strong>Reserva ID:</strong> {{ $reserva ? str_pad($reserva->id, 6, '0', STR_PAD_LEFT) : 'N/A' }}</p>
                    @if($venta->created_at)
                    <p><strong>Fecha de Venta:</strong> {{ $venta->created_at->format('d/m/Y') }}</p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Información del Cliente -->
        <div class="cliente-info">
            <h3>Información del Cliente</h3>
            <div class="cliente-details">
                <p><strong>Nombre:</strong> {{ $cliente->nombre }} {{ $cliente->apellido }}</p>
                <p><strong>DNI:</strong> {{ $cliente->dni }}</p>
                <p><strong>Email:</strong> {{ $cliente->email }}</p>
                <p><strong>Teléfono:</strong> {{ $cliente->telefono }}</p>
                <p><strong>Dirección:</strong> {{ $cliente->direccion }}</p>
            </div>
            
            @if($reserva)
            <div class="border-box mt-20">
                <p><strong>Período de Hospedaje:</strong></p>
                <p>Check-in: {{ \Carbon\Carbon::parse($reserva->fecha_entrada)->format('d/m/Y') }}</p>
                <p>Check-out: {{ \Carbon\Carbon::parse($reserva->fecha_salida)->format('d/m/Y') }}</p>
                <p>Noches: {{ $reserva->noches }} | Adultos: {{ $reserva->adultos }} | Niños: {{ $reserva->ninos }}</p>
            </div>
            @endif
        </div>
        
        <!-- Detalles de la Venta -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 10%;">Código</th>
                    <th style="width: 40%;">Descripción</th>
                    <th style="width: 10%;">Cantidad</th>
                    <th style="width: 15%;">Precio Unitario</th>
                    <th style="width: 15%;">Descuento</th>
                    <th style="width: 15%;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <!-- Habitación -->
                @if($reserva && $habitacion)
                <tr>
                    <td>HAB-{{ str_pad($habitacion->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <strong>{{ $habitacion->tipo ?? 'Habitación' }}</strong><br>
                        <small>{{ $habitacion->descripcion ?? 'Estadía en hotel' }}</small><br>
                        <small>
                            {{ \Carbon\Carbon::parse($reserva->fecha_entrada)->format('d/m/Y') }} 
                            al {{ \Carbon\Carbon::parse($reserva->fecha_salida)->format('d/m/Y') }}
                        </small>
                    </td>
                    <td class="text-center">{{ $reserva->noches ?? 1 }}</td>
                    <td class="text-right">${{ number_format(($reserva->precio_total ?? 0) / ($reserva->noches ?? 1), 2) }}</td>
                    <td class="text-right">$0.00</td>
                    <td class="text-right">${{ number_format($reserva->precio_total ?? 0, 2) }}</td>
                </tr>
                @endif
                
                <!-- Otros servicios si existen -->
                @if(isset($datos_facturacion['servicios']) && is_array($datos_facturacion['servicios']))
                    @foreach($datos_facturacion['servicios'] as $servicio)
                    <tr>
                        <td>SER-{{ $loop->iteration }}</td>
                        <td>{{ $servicio['nombre'] ?? 'Servicio Adicional' }}</td>
                        <td class="text-center">{{ $servicio['cantidad'] ?? 1 }}</td>
                        <td class="text-right">${{ number_format($servicio['precio'] ?? 0, 2) }}</td>
                        <td class="text-right">$0.00</td>
                        <td class="text-right">${{ number_format(($servicio['precio'] ?? 0) * ($servicio['cantidad'] ?? 1), 2) }}</td>
                    </tr>
                    @endforeach
                @endif
                
                <!-- Si no hay detalles específicos, mostrar venta general -->
                @if(!$reserva && !isset($datos_facturacion['servicios']))
                <tr>
                    <td>VENTA-{{ str_pad($venta->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>Servicios de Hotel</td>
                    <td class="text-center">1</td>
                    <td class="text-right">${{ number_format($venta->monto_total, 2) }}</td>
                    <td class="text-right">$0.00</td>
                    <td class="text-right">${{ number_format($venta->monto_total, 2) }}</td>
                </tr>
                @endif
            </tbody>
        </table>
        
        <!-- Totales -->
        <div class="totales">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>${{ number_format($venta->monto_total, 2) }}</span>
            </div>
            
            <div class="total-row">
                <span>IVA (19%):</span>
                <span>${{ number_format($venta->monto_total * 0.19, 2) }}</span>
            </div>
            
            <div class="total-row">
                <span>Descuentos:</span>
                <span>$0.00</span>
            </div>
            
            <div class="total-row grand-total">
                <span>TOTAL:</span>
                <span>${{ number_format($venta->monto_total, 2) }}</span>
            </div>
        </div>
        
        <!-- Información de Pago -->
        @if($venta->pagos && $venta->pagos->count() > 0)
        <div class="payment-info mt-20">
            <h4>Información de Pago</h4>
            @foreach($venta->pagos as $pago)
                @if($pago->estado == 'completado')
                <div class="border-box mb-10">
                    <p><strong>Método:</strong> {{ ucfirst($pago->metodo_pago) }}</p>
                    <p><strong>Referencia:</strong> {{ $pago->referencia }}</p>
                    <p><strong>Monto Pagado:</strong> ${{ number_format($pago->monto, 2) }}</p>
                    <p><strong>Fecha de Pago:</strong> {{ $pago->created_at->format('d/m/Y H:i') }}</p>
                    <p><strong>Estado:</strong> {{ ucfirst($pago->estado) }}</p>
                </div>
                @endif
            @endforeach
        </div>
        @endif
        
        <!-- Términos y Condiciones -->
        <div class="footer">
            <p><strong>TÉRMINOS Y CONDICIONES</strong></p>
            <p>1. Esta factura es un documento válido para efectos tributarios.</p>
            <p>2. El pago debe realizarse en su totalidad antes del servicio.</p>
            <p>3. Las cancelaciones con menos de 24 horas de anticipación no son reembolsables.</p>
            <p>4. Para reclamos o aclaraciones contactar a: {{ $empresa['email'] }}</p>
            <p class="mt-20">
                <strong>{{ $empresa['nombre'] }}</strong><br>
                {{ $empresa['direccion'] }} | Tel: {{ $empresa['telefono'] }}<br>
                Factura generada automáticamente el {{ now()->format('d/m/Y H:i:s') }}
            </p>
        </div>
    </div>
</body>
</html>