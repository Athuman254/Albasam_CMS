<?php
// app/Http/Controllers/PdfController.php

namespace App\Http\Controllers;

use OmarAlalwi\Gpdf\Gpdf;
use App\Services\PdfService;
use App\Http\Controllers\Controller;
use Omaralalwi\Gpdf\Facade\Gpdf as GpdfFacade;
use Omaralalwi\Gpdf\GpdfConfig;

class PdfController extends Controller
{
   protected $pdfService;

   public function __construct(PdfService $pdfService)
   {
      $this->pdfService = $pdfService;
   }

   public function generateArabicPdfs()
   {
      $data = [
         'title' => 'نموذج عربي',
         'content' => 'محتوى عربي'
      ];

      return $this->pdfService->downloadArabicPdf($data, 'arabic-document.pdf');
   }

   public function generateArabicPdf()
   {

      $html = <<<EOD
<h2 style="text-align:center;">تقرير شهري</h2>
<p>هذا التقرير يحتوي على بعض المعلومات باللغة العربية.</p>
<p>تم إنشاؤه باستخدام مكتبة Gpdf المبنية على TCPDF.</p>
EOD;
      $html = view('pdf.my_report', compact('data'))->render();
      $pdfContent = GpdfFacade::generate($html);
      return response($pdfContent, 200, ['Content-Type' => 'application/pdf']);
   }
}
