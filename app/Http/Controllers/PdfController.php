<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use Barryvdh\DomPDF\Facade\Pdf;


class PdfController extends Controller
{
    public function downloadOrdenPdf(Orden $orden)
    {
        // Carga la vista 'pdf.orden-pdf' y le pasa la variable 'orden'
        $pdf = Pdf::loadView('pdf.orden-pdf', compact('orden') + ['is_blank' => false]);

        // Descarga el PDF con un nombre de archivo dinámico
        return $pdf->download('orden-'.$orden->numero_orden.'.pdf');
    }

    public function streamOrdenPdf(Orden $orden)
    {
        $pdf = Pdf::loadView('pdf.orden-pdf', compact('orden') + ['is_blank' => false]);
        return $pdf->stream('orden-'.$orden->numero_orden.'.pdf');
    }

}