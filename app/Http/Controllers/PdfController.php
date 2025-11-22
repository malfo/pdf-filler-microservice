<?php

// app/Http/Controllers/PdfController.php

namespace App\Http\Controllers;

use App\Services\PdfFillerService;
use App\Models\ProcessedPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PdfController extends Controller
{
    protected PdfFillerService $pdfService;

    public function __construct(PdfFillerService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function generate(Request $request)
    {
        // 1. Validazione di base (aggiungi più regole in produzione!)
        $request->validate([
            'onlus_code' => 'required|string|in:LAV',
            'code_membership' => 'required|string|unique:processed_pdfs,code_membership', // Richiesto e univoco
            'reference_id' => 'nullable|string', // Opzionale
            'data' => 'required|array',
            'data.firma_cc' => 'nullable|string',
        ]);
        
        // Validazione opzionale della firma se presente
        if ($request->has('data.firma_cc') && $request->input('data.firma_cc')) {
            $signature = $request->input('data.firma_cc');
            
            // Rimuovi il prefisso data URI se presente
            if (strpos($signature, ',') !== false) {
                $signature = substr($signature, strpos($signature, ',') + 1);
            }
            
            // Verifica che sia Base64 valido
            if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $signature)) {
                return response()->json(['error' => 'Formato Base64 della firma non valido'], 400);
            }
            
            // Verifica che decodificato sia un'immagine valida
            $decoded = base64_decode($signature, true);
            if ($decoded === false || @getimagesizefromstring($decoded) === false) {
                return response()->json(['error' => 'La firma non è un\'immagine valida'], 400);
            }
        }

        $onlusCode = $request->input('onlus_code');
        $data = $request->input('data');

        try {
            // 2. Riempimento del PDF
            $pdfContent = $this->pdfService->fillPdf($onlusCode, $data);

            // 3. Salvataggio del file su disco (opzionale ma utile)
            $filename = "{$onlusCode}_" . time() . ".pdf";
            Storage::disk('local')->put('processed/' . $filename, $pdfContent);

            // 4. Salvataggio record nel database
            ProcessedPdf::create([
                'code_membership' => $request->input('code_membership'),
                'onlus_code' => $onlusCode,
                'reference_id' => $request->input('reference_id'),
                'file_path' => 'processed/' . $filename,
            ]);

            // 5. Ritorno del PDF come risposta API
            return response($pdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}