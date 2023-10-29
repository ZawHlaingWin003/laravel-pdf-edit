<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

class PdfController extends Controller
{
    public function index(Request $request)
    {
        return view('form');
    }

    public function download(Request $request)
    {
        $originalFile = $request->file('original-file');
        $originalFileName = 'original.' . $originalFile->getClientOriginalExtension();

        $signatureFile = $request->file('signature-file');
        $signatureFileName = 'signature.' . $signatureFile->getClientOriginalExtension();

        $originalFile->move('original', $originalFileName);
        $signatureFile->move('signatures', $signatureFileName);


        $outputFilePath = public_path("sample_output.pdf");
        $this->fillPDFFile($originalFile, $outputFilePath);

        // return response()->file($outputFilePath);

        return Storage::download($outputFilePath);
    }

    public function preview(Request $request)
    {
        dd($request->all());
        $filePath = public_path("The-Invisible-Man.pdf");
        // $filePath = public_path("sample.pdf");
        $outputFilePath = public_path("sample_output.pdf");
        $this->fillPDFFile($filePath, $outputFilePath);

        return response()->file($outputFilePath);
    }

    public function fillPDFFile($file, $outputFilePath)
    {
        $fpdi = new FPDI();
        $count = $fpdi->setSourceFile($file);

        for ($i = 1; $i <= $count; $i++) {
            $template = $fpdi->importPage($i);
            $size = $fpdi->getTemplateSize($template);
            $fpdi->AddPage($size['orientation'], array($size['width'], $size['height']));
            // $fpdi->useTemplate($template, 10, 10, 100);
            $fpdi->useTemplate($template);

            $fpdi->SetFont("Helvetica", "", 10);
            $fpdi->SetTextColor(153, 0, 153);

            if ($i > 3) {
                $left = 160;
                $top = 258;
                $text = "Write any text you want!";
                $fpdi->Text($left, $top, $text);

                $fpdi->Image(public_path('signature.png'), 150, 260);
            }
        }

        return $fpdi->Output($outputFilePath, 'F');
    }
}
