<?php

namespace App\Exports;

use App\Models\Orden;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdenesExport implements FromQuery, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Define la consulta para obtener las órdenes dentro del rango de fechas.
     */
    public function query()
    {
        return Orden::query()
            // CAMBIO: Precarga la relación con el técnico y las fotos para optimizar
            ->with(['technician', 'fotos']) 
            ->whereBetween('fecha_hora', [$this->startDate, $this->endDate]);
    }

    /**
     * Define los encabezados de las columnas en el archivo Excel.
     */
    public function headings(): array
    {
        return [
            'N° Orden',
            'N° Expediente',
            'Nombre Cliente',
            'Fecha y Hora',
            'Valor Servicio',
            'Placa',
            'Referencia',
            'Nombre Contacto',
            'Celular Contacto',
            'Unidad de Negocio',
            'Movimiento',
            'Servicio',
            'Modalidad',
            'Tipo de Activo',
            'Marca',
            'Técnico Asignado',
            'Ciudad Origen',
            'Dirección Origen',
            'Observaciones Origen',
            'Ciudad Destino',
            'Dirección Destino',
            'Observaciones Destino',
            'Observaciones Generales',
            'Es Programada',
            'Fecha Programada',
            'Estado',
            'Ubicación Fotos', // <-- CAMPO AÑADIDO
        ];
    }

    /**
     * Mapea los datos de cada orden a las columnas del Excel.
     * @param \App\Models\Orden $orden
     */
    public function map($orden): array
    {
        // CAMBIO: Se añade la lógica para las fotos
        $photoPaths = $orden->fotos->isNotEmpty() 
            ? $orden->fotos->pluck('path')->implode(', ') 
            : 'Sin fotos asignadas';

        return [
            $orden->numero_orden,
            $orden->numero_expediente,
            $orden->nombre_cliente,
            $orden->fecha_hora->format('Y-m-d H:i:s'),
            $orden->valor_servicio,
            $orden->placa,
            $orden->referencia,
            $orden->nombre_asignado,
            $orden->celular,
            $orden->unidad_negocio,
            $orden->movimiento,
            $orden->servicio,
            $orden->modalidad,
            $orden->tipo_activo,
            $orden->marca,
            $orden->technician->name ?? 'Sin asignar',
            $orden->ciudad_origen,
            $orden->direccion_origen,
            $orden->observaciones_origen,
            $orden->ciudad_destino,
            $orden->direccion_destino,
            $orden->observaciones_destino,
            $orden->observaciones_generales,
            $orden->es_programada ? 'Sí' : 'No',
            $orden->fecha_programada ? $orden->fecha_programada->format('Y-m-d H:i:s') : 'N/A',
            $orden->status,
            $photoPaths, // <-- CAMPO AÑADIDO
        ];
    }
}
