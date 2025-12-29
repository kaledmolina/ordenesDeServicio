{{--
  Ruta del archivo: resources/views/pdf/orden-pdf.blade.php
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Servicio #{{ $orden->numero_orden }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; color: #000; margin: 0; padding: 0; }
        .container { width: 100%; border: 1px solid #000; padding: 5px; box-sizing: border-box; }
        
        /* Header Grid */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        .header-table td { vertical-align: top; padding: 0; }
        .logo-cell { width: 25%; }
        .info-cell { width: 50%; text-align: center; font-size: 12px; }
        .meta-cell { width: 25%; text-align: right; font-size: 9px; }
        
        /* Title Bar */
        .title-bar { 
            border-top: 1px solid #000; 
            border-bottom: 1px solid #000; 
            padding: 3px; 
            font-weight: bold; 
            display: flex; 
            justify-content: space-between; 
            background: #eee;
        }

        /* Order Info Grid */
        .info-grid { width: 100%; border-collapse: collapse; margin-bottom: 5px; font-size: 10px; }
        .info-grid td { padding: 2px; }
        .label { font-weight: bold; }
        
        /* Materials Table */
        .materials-table { width: 100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 5px; font-size: 9px; }
        .materials-table th, .materials-table td { border: 1px solid #000; padding: 3px; }
        .materials-table th { text-align: left; background: #eee; font-weight: bold; text-align: center; }
        
        /* Box for Equipment Info */
        .equipment-box { border: 1px solid #000; margin-top: 5px; }
        .equipment-header { text-align: center; border-bottom: 1px solid #000; padding: 3px; font-weight: bold; background: #eee; }
        .equipment-row { display: flex; border-bottom: 1px solid #000; }
        .equipment-cell { flex: 1; padding: 2px; border-right: 1px solid #000; }
        .equipment-cell:last-child { border-right: none; }
        
        /* Footers */
        .footer-obs { border: 1px solid #000; padding: 3px; min-height: 40px; margin-bottom: 5px; }
        .signatures { margin-top: 30px; width: 100%; }
        .sig-line { border-top: 1px solid #000; width: 40%; display: inline-block; margin-right: 5%; padding-top: 3px; text-align: center; }
        
        .contact-footer { text-align: center; font-size: 8px; margin-top: 10px; border-top: 1px solid #ccc; padding-top: 3px; }

        /* Util clases */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>
    @php
        $legacyItems = [
            'Esclavo con wifi (unidad)', 'Mecanico sc/apc', 'Cable drop 1 hilo', 'Grapas de muro', 'Ont', 'Canaleta plastica', 
            'Abrazadera metalicas', 'Chazos(unidad)', 'Tornillos(unidad)', 'Amarres plasticos (unidad)', 'Cinta bandi(centimetro)', 
            'Clavos', 'Conector RG6', 'Cable coaxial'
        ];
        
        // Prepare split lists for two columns
        $totalRows = 14; 
        
        if ($is_blank) {
            $col1 = array_slice($legacyItems, 0, $totalRows);
            $col2 = array_fill(0, $totalRows, ''); // Empty description for second column placeholders
        } else {
            // If filled, we use the actual articles
            $items = $orden->articulos ?? [];
            // Fill up to totalRows to maintain layout structure if needed, or just list them
            $col1 = array_slice($items, 0, $totalRows); 
            // Logic for split columns could be complex if dynamic, for now checking the image it looks like two static lists of fields
            // BUT the user wants "imprimir los campos vacios si no fueron llenados"
            // So we will stick to the legacy template for the structure.
            // If it's filled, we overlay values? 
            // The request says "imprimir los campos vacios si no fueron llenados" implies creating the struct.
        }
    @endphp

    <div class="container">
        <!-- HEADER -->
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    @php
                        $path = public_path('logo.png');
                        $type = pathinfo($path, PATHINFO_EXTENSION);
                        $data = file_exists($path) ? file_get_contents($path) : '';
                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    @endphp
                    <img src="{{ $base64 }}" alt="Logo" style="max-width: 150px; height: auto;">
                    <div style="font-size: 10px; font-weight: bold; color: #008CB4;">TELECOMUNICACIONES</div>
                </td>
                <td class="info-cell">
                    <div style="font-weight: bold; font-size: 14px;">INVERSIONES ZULUAGA SEJIN S.A.S.</div>
                    <div>NIT: 900888246-0</div>
                    <div>CR 25 # 23 -74 brr la pradera</div>
                    <div>Registro MINTIC No 96003059</div>
                </td>
                <td class="meta-cell">
                    <div>Fecha y hora impresión:</div>
                    <div>{{ date('d/12/2025 H:i:s') }}</div>
                </td>
            </tr>
        </table>

        <!-- VENDEDOR / ORDEN BAR -->
        <table style="width: 100%; border-top: 1px solid #000; border-bottom: 1px solid #000; font-size: 10px; margin-bottom: 5px;">
            <tr>
                <td width="40%">Vendedor: {{ $orden->technician->name ?? '---' }}</td>
                <td width="30%">Ingreso orden: {{ $orden->numero_orden }}</td> {{-- Using numero_orden as ingreso --}}
                <td width="20%">SALDO A LA FECHA: {{ $orden->saldo_cliente ?? 0 }}</td>
                <td width="10%" class="text-right bold">EMPRESA</td>
            </tr>
        </table>

        <!-- CLIENT INFO -->
        <table class="info-grid">
            <tr>
                <td class="bold">REVISION TECNICA No:</td>
                <td>{{ $orden->numero_orden }}</td>
                <td class="bold">FECHA:</td>
                <td>{{ $orden->fecha_trn ? $orden->fecha_trn->format('d/m/Y') : '--/--/----' }}</td>
                <td class="bold">CODIGO:</td>
                <td>{{ $orden->cliente_id ?? '---' }}</td>
                <td class="bold">NOMBRES:</td>
                <td colspan="3">{{ $orden->cliente->name ?? 'SIN CLIENTE' }}</td>
                <td class="bold">Cc/Nit:</td>
                <td>{{ $orden->cedula ?? $orden->cliente->email ?? '---' }}</td>
            </tr>
            <tr>
                <td class="bold">DIRECCION:</td>
                <td colspan="5">{{ $orden->direccion ?? '---' }}</td>
                <td class="bold">BARRIO:</td>
                <td>{{ $orden->barrio ?? '---' }}</td>
                <td class="bold">TELEFONO:</td>
                <td colspan="3">{{ $orden->telefono ?? '---' }}</td>
            </tr>
            <tr>
                <td class="bold">OBSERVACIONES:</td>
                <td colspan="10">{{ $orden->solicitud_suscriptor ?? '---' }} &nbsp;&nbsp;&nbsp; {{ $orden->solicitado_por ?? '' }}</td>
                <td class="text-right text-danger bold" style="color: red;">Plan: {{ $orden->tipo_orden ?? '---' }}</td>
            </tr>
        </table>

        <!-- MATERIALS GRID -->
        <table class="materials-table">
            <thead>
                <tr>
                    <th style="width: 35%;">DESCRIPCION</th>
                    <th style="width: 5%;">CANT.</th>
                    <th style="width: 10%;">VALOR</th>
                    <th style="width: 5%;">ASOC</th> <!-- Empty checkbox col? -->
                    <th style="width: 35%;">DESCRIPCION</th>
                    <th style="width: 5%;">CANT.</th>
                    <th style="width: 10%;">VALOR</th>
                    <th style="width: 5%;">ASOC</th>
                </tr>
            </thead>
            <tbody>
                @if($is_blank)
                    {{-- Render fixed legacy items --}}
                    @for($i = 0; $i < 14; $i++)
                        <tr>
                            <td>{{ $legacyItems[$i] ?? '' }}</td>
                            <td></td><td></td><td></td>
                            <td></td> {{-- Second col empty description --}}
                            <td></td><td></td><td></td>
                        </tr>
                    @endfor
                @else
                    {{-- Render Actual Items but formatted into 2 cols if possible, 
                         OR render actual items in col 1 and blanks continue? 
                         User said "imprimir los campos vacios si no fueorn lllenado y vacio si no fueron llenados" --}}
                    @php
                        $items = $orden->articulos ?? [];
                        $count = count($items);
                        $maxRows = 14; // Fixed size to match paper look
                    @endphp
                    @for($i = 0; $i < $maxRows; $i++)
                        <tr>
                            <!-- COL 1 -->
                            @if(isset($items[$i]))
                                <td>{{ $items[$i]['articulo'] ?: ($items[$i]['grupo_articulo'] ?? '---') }}</td>
                                <td class="text-center">{{ !empty($items[$i]['cantidad']) ? $items[$i]['cantidad'] : '' }}</td>
                                <td class="text-right">{{ !empty($items[$i]['total']) ? number_format($items[$i]['total']) : '' }}</td>
                                <td></td>
                            @else
                                <td>{{ $legacyItems[$i] ?? '' }}</td> {{-- If not filled, show legacy item placeholder? Or just empty? User said "campos vacios si no fueron llenados". Let's show legacy items as hints if row is empty, or just blank? "vacio si no fueron llenados" -> I will interpret as just empty lines if not blank form vs filled items. But wait, "imprimir los campos vacios si no fueron llenados" usually means blank lines. --}}
                                <td></td><td></td><td></td>
                            @endif

                            <!-- COL 2 -->
                             @if(isset($items[$i + $maxRows])) {{-- Very unlikely to have >14 items but logic supports it --}}
                                <td>{{ $items[$i+$maxRows]['articulo'] ?? '' }}</td>
                                <td class="text-center">{{ $items[$i+$maxRows]['cantidad'] ?? '' }}</td>
                                <td class="text-right">{{ number_format($items[$i+$maxRows]['total'] ?? 0) }}</td>
                                <td></td>
                             @else
                                <td></td>
                                <td></td><td></td><td></td>
                             @endif
                        </tr>
                    @endfor
                @endif
                <tr>
                   <td colspan="4" style="border:none; text-align:center; font-weight:bold;">OBSERVACIONES DE EJECUCION</td>
                   <td colspan="4" style="border:none;"></td>
                </tr>
            </tbody>
        </table>

        <!-- EQUIPOS INSTALADOS SUB-TABLE (INSIDE THE MATERIALS FLOW visually in image) -->
        <table class="materials-table" style="margin-top: -6px; border-top: none;">
             <tr>
                 <td width="50%" rowspan="4" style="vertical-align: top; border-right: 1px solid #000;">
                     <div>
                         {{ $orden->solucion_tecnico ?? '' }} - {{ $orden->observaciones ?? '' }}
                     </div>
                 </td>
                 <td width="20%">Mac Router</td>
                 <td width="30%"></td>
             </tr>
             <tr>
                 <td>Mac Eoce</td>
                 <td></td>
             </tr>
             <tr>
                 <td>Mac Ap</td>
                 <td></td>
             </tr>
             <tr>
                 <td>Direccion IP</td>
                 <td></td>
             </tr>
        </table>
        
        <!-- FOOTER / SIGNATURES -->
        <div style="margin-top: 20px;">
            <table width="100%">
                <tr>
                    <td width="40%">
                        <div>HORA REAL: HH:_______ M:_______</div>
                        <div style="margin-top: 5px;">FECHA REAL:D_______M_______Y_______</div>
                    </td>
                    <td width="30%" class="text-center" style="vertical-align: bottom;">
                        <div style="border-top: 1px solid #000; width: 80%; margin: 0 auto;">FIRMA TÉCNICO</div>
                    </td>
                    <td width="30%" class="text-center" style="vertical-align: bottom;">
                        <div style="border-top: 1px solid #000; width: 80%; margin: 0 auto;">FIRMA SUSCRIPTOR</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="contact-footer">
            quejas y Reclamos: Teléfono: CR 25 # 23 -74 brr la pradera 3225802429 Email: intalnet.monteria@gmail.com
            <br>
            MONTERIA-CORDOBA Vigilada y regulada por la MINTIC
        </div>

    </div>
</body>
</html>
