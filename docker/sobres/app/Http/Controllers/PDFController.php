<?php

namespace App\Http\Controllers;

use TCPDF;
use App\Models\SobrePlantilla;
use App\Models\RegistroImagen;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
class PDFController extends Controller
{
    public function sobrePlantillaPDF(string $id)
    {
        $sobre = SobrePlantilla::findOrFail($id);

        $imagenes = RegistroImagen::where('sobre_plantilla_id', $id)
            ->orderBy('orden', 'asc')
            ->get()
            ->values();

        // PDF (LETTER) en mm
        $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('SOBRES');
        $pdf->SetTitle('Plantilla - ' . $sobre->nombre_sobre);

        // Márgenes
        $leftMargin = 10;
        $rightMargin = 10;
        $topMargin = 10;
        $bottomMargin = 10;

        $pdf->SetMargins($leftMargin, $topMargin, $rightMargin);
        $pdf->SetAutoPageBreak(true, $bottomMargin);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // Config grid
        $cols = 3;
        $rows = 4;
        $perPage = $cols * $rows; // 12

        // Tamaño imagen deseado: 5.1cm = 51mm
        $imgSize = 51;

        // Espacios entre celdas (ajústalos si quieres más “aire”)
        $gapX = 10; // mm
        $gapY = 8;  // mm

        // “Celda” = imagen + gap
        $cellW = $imgSize + $gapX;
        $cellH = $imgSize + $gapY;

        // Header simple (si lo quieres como tu ejemplo con marco, lo hacemos)
        $headerH = 12;

        // Estilo punteado (como tu PDF ejemplo)
        $dotted = ['width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => '1,2', 'color' => [120,120,120]];

        // Función local para dibujar la grilla punteada por página
        $drawGrid = function($startX, $startY) use ($pdf, $cols, $rows, $cellW, $cellH, $dotted) {
            $x0 = $startX;
            $y0 = $startY;
            $xMax = $x0 + ($cols * $cellW);
            $yMax = $y0 + ($rows * $cellH);

            // Verticales (incluye borde izquierdo y derecho)
            for ($c = 0; $c <= $cols; $c++) {
                $x = $x0 + ($c * $cellW);
                $pdf->Line($x, $y0, $x, $yMax, $dotted);
            }

            // Horizontales (incluye borde superior e inferior)
            for ($r = 0; $r <= $rows; $r++) {
                $y = $y0 + ($r * $cellH);
                $pdf->Line($x0, $y, $xMax, $y, $dotted);
            }
        };

        $total = $imagenes->count();

        for ($offset = 0; $offset < $total; $offset += $perPage) {
            $pdf->AddPage();

            // Header
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, $headerH, 'PLANTILLA: ' . $sobre->nombre_sobre, 0, 1, 'C');
            $pdf->Ln(2);

            // Inicio grid (debajo del header)
            $startX = $leftMargin;
            $startY = $pdf->GetY();

            // Dibuja la grilla punteada (como el ejemplo)
            $drawGrid($startX, $startY);

            // Pinta hasta 12 imágenes en esta página
            for ($i = 0; $i < $perPage; $i++) {
                $idx = $offset + $i;
                if ($idx >= $total) break;

                $img = $imagenes[$idx];
                //$imgPath = public_path("storage/{$img->imagen}");
                // Normaliza ruta guardada (por si viene "public/images/..." en registros viejos)
                $relative = ltrim($img->imagen ?? '', '/');
                $relative = preg_replace('#^public/#', '', $relative);

                // Ruta real en disco (NO depende del symlink /public/storage)
                $imgPath = Storage::disk('public')->path($relative);
                
                $row = intdiv($i, $cols);
                $col = $i % $cols;

                // Posición de la celda
                $cellX = $startX + ($col * $cellW);
                $cellY = $startY + ($row * $cellH);

                // Centrar imagen dentro de la celda
                $imgX = $cellX + (($cellW - $imgSize) / 2);
                $imgY = $cellY + (($cellH - $imgSize) / 2);

                if (file_exists($imgPath)) {
                    // Imagen EXACTA 52x52 (si quieres mantener proporción sin deformar, te lo ajusto)
                    $pdf->Image(
                        $imgPath,
                        $imgX,
                        $imgY,
                        $imgSize,
                        $imgSize,
                        '',
                        '',
                        '',
                        false,
                        300,
                        '',
                        false,
                        false,
                        0,
                        'CM',
                        false,
                        false
                    );
                }
            }
        }

        // Output
        $filename = 'Plantilla_' . $sobre->nombre_sobre . '.pdf';
        $pdfContent = $pdf->Output($filename, 'S');

        return response()->make($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
