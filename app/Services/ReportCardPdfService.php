<?php

namespace App\Services;

use Mpdf\Mpdf;

class ReportCardPdfService
{
    public function render(array $data): Mpdf
    {
        $tempDir = storage_path('app/mpdf');

        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode'    => 'utf-8',
            'format'  => 'A4-P',
            'tempDir' => $tempDir,
        ]);

        if (app()->getLocale() === 'ar') {
            $mpdf->SetDirectionality('rtl');
        }

        $mpdf->WriteHTML(view('pdf.report_card', $data)->render());

        return $mpdf;
    }
}
