<?php
// app/Services/PdfService.php

namespace App\Services;

use Barryvdh\DomPDF\PDF;

class PdfService
{
    protected $pdf;

    public function __construct(PDF $pdf)
    {
        $this->pdf = $pdf;
    }

    public function generateArabicPdf($data, $filename = 'document.pdf')
    {
        $html = $this->getArabicHtmlTemplate($data);

        $pdf = $this->pdf->loadHTML($html);

        // Set options for DomPDF v3.1
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('fontDir', storage_path('fonts'));
        $pdf->setOption('fontCache', storage_path('fonts'));
        $pdf->setOption('defaultFont', 'amiri');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('isFontSubsettingEnabled', true);
        $pdf->setOption('chroot', base_path());

        return $pdf;
    }

    protected function getArabicHtmlTemplate($data)
    {
        return "
        <!DOCTYPE html>
        <html dir='rtl' lang='ar'>
        <head>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
            <style>
                @font-face {
                    font-family: 'Amiri';
                    font-style: normal;
                    font-weight: normal;
                    src: url('" . storage_path('fonts/Amiri-Regular.ttf') . "') format('truetype');
                }
                @font-face {
                    font-family: 'Amiri';
                    font-style: normal;
                    font-weight: bold;
                    src: url('" . storage_path('fonts/Amiri-Bold.ttf') . "') format('truetype');
                }
                body {
                    font-family: 'Amiri', serif;
                    direction: rtl;
                    text-align: right;
                    line-height: 1.8;
                }
                .arabic-text {
                    font-family: 'Amiri', serif;
                    font-size: 16px;
                    direction: rtl;
                }
                .title {
                    font-size: 24px;
                    font-weight: bold;
                    text-align: center;
                    margin-bottom: 30px;
                }
            </style>
        </head>
        <body>
            <div class='title'>نموذج باللغة العربية</div>
            <div class='arabic-text'>
               <div>sdds{{$data['title']}}</div>
                <p>هذا مثال لنص عربي في ملف PDF</p>
                <p>يمكنك استخدام خط Amiri لعرض النصوص العربية بشكل صحيح</p>
                <p>النصوص العربية ستعرض من اليمين إلى اليسار</p>
                <p><strong>نص عريض:</strong> هذا نص عربي عريض</p>
            </div>
        </body>
        </html>
        ";
    }

    public function downloadArabicPdf($data, $filename = 'document.pdf')
    {
        $pdf = $this->generateArabicPdf($data, $filename);
        return $pdf->download($filename);
    }

    public function streamArabicPdf($data)
    {
        $pdf = $this->generateArabicPdf($data);
        return $pdf->stream();
    }
}
