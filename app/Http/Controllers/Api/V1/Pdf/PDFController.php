<?php

namespace App\Http\Controllers\Api\V1\Pdf;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\ExportPDF\ExportPdfService;
use PDF;
use Storage;
// use Font_Metrics;
use App\Traits\ApiResponse;

class PDFController extends Controller
{

    use ApiResponse;
    private $pdf;

    public function __construct()
    {
        $this->pdf = new ExportPdfService();
    }

    /**
     * for exporting the pdf
     *
     * @param Request $request
     * @return void
     */
    public function exportPDF(Request $request)
    {
        $params = $request->query();

        $data = $this->pdf->exportVmapPDF($params);

        // $font = Font_Metrics::get_font("helvetica", "bold");

        Pdf::setOption(['dpi' => 150, 'defaultFont' => 'sans-serif']);
        $pdf = PDF::loadView('pdfs.vmap', compact('data'));
        // $dom_pdf = $pdf->getDomPDF();
        // $canvas = $dom_pdf->getCanvas();

        // $canvas->page_text(72, 18, "Header: {PAGE_NUM} of {PAGE_COUNT}", null, 6, array(0,0,0));

        Storage::disk('pdfs')->put($data['formTitle'].'.pdf', $pdf->output());

        return response()->download('storage/pdfs/'.$data['formTitle'].'.pdf');

    }
}
