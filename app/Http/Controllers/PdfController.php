<?php

// app/Http/Controllers/PdfController.php

namespace App\Http\Controllers;

use App\Services\PdfFillerService;
use App\Models\ProcessedPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PdfController extends Controller
{
    protected PdfFillerService $pdfService;

    public function __construct(PdfFillerService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function generate(Request $request)
    {
        try {
            // 1. Validazione di base
            $request->validate([
                'onlus_code' => 'required|string|in:LAV,TELETHON,EMERGENCY,IEO,GEMELLI',
                'membership_code' => 'required|string|unique:processed_pdfs,membership_code',
                'reference_id' => 'nullable|string',
                'data' => 'required|array',
                'data.firma_cc' => 'nullable|string',
            ]);
            
            Log::info('Validazione completata', [
                'onlus_code' => $request->input('onlus_code'),
                'membership_code' => $request->input('membership_code'),
            ]);
            
            // Validazione opzionale della firma se presente
            if ($request->has('data.firma_cc') && $request->input('data.firma_cc')) {
                $signature = $request->input('data.firma_cc');
                
                if (strpos($signature, ',') !== false) {
                    $signature = substr($signature, strpos($signature, ',') + 1);
                }
                
                if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $signature)) {
                    return response()->json(['error' => 'Formato Base64 della firma non valido'], 400);
                }
                
                $decoded = base64_decode($signature, true);
                if ($decoded === false || @getimagesizefromstring($decoded) === false) {
                    return response()->json(['error' => 'La firma non è un\'immagine valida'], 400);
                }
            }

            $onlusCode = $request->input('onlus_code');
            $data = $request->input('data');
            
            // Aggiungi membership_code all'array $data per poterlo stampare sul PDF
            $data['membership_code'] = $request->input('membership_code');
            
            Log::info('Inizio generazione PDF', [
                'onlus_code' => $onlusCode,
                'membership_code' => $data['membership_code'],
            ]);

            // 2. Riempimento del PDF
            try {
                $pdfContent = $this->pdfService->fillPdf($onlusCode, $data);
                
                if (empty($pdfContent)) {
                    Log::error('PDF generato vuoto');
                    return response()->json(['error' => 'Errore nella generazione del PDF: contenuto vuoto'], 500);
                }
                
                Log::info('PDF generato con successo', [
                    'size' => strlen($pdfContent),
                ]);
                
            } catch (\Exception $e) {
                Log::error('Errore nella generazione del PDF', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return response()->json([
                    'error' => 'Errore nella generazione del PDF',
                    'details' => config('app.debug') ? $e->getMessage() : 'Errore durante la generazione'
                ], 500);
            }

            // 3. Salvataggio del file su disco
            try {
                $filename = "{$onlusCode}_" . time() . ".pdf";
                $filePath = 'processed/' . $filename;
                
                Storage::disk('local')->put($filePath, $pdfContent);
                
                Log::info('File salvato su disco', [
                    'filename' => $filename,
                    'path' => $filePath,
                ]);
                
            } catch (\Exception $e) {
                Log::error('Errore nel salvataggio del file', [
                    'message' => $e->getMessage(),
                ]);
                return response()->json([
                    'error' => 'Errore nel salvataggio del file',
                    'details' => config('app.debug') ? $e->getMessage() : 'Errore durante il salvataggio'
                ], 500);
            }

            // 4. Salvataggio record nel database
            try {
                $processedPdf = ProcessedPdf::create([
                    'membership_code' => $request->input('membership_code'),
                    'onlus_code' => $onlusCode,
                    'reference_id' => $request->input('reference_id'),
                    'file_path' => $filePath,
                ]);
                
                Log::info('Record salvato nel database', [
                    'id' => $processedPdf->id,
                    'membership_code' => $processedPdf->membership_code,
                ]);
                
            } catch (\Illuminate\Database\QueryException $e) {
                Log::error('Errore database', [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'sql' => $e->getSql() ?? 'N/A',
                ]);
                return response()->json([
                    'error' => 'Errore nel salvataggio nel database',
                    'details' => config('app.debug') ? $e->getMessage() : 'Errore durante il salvataggio'
                ], 500);
            } catch (\Exception $e) {
                Log::error('Errore generico nel salvataggio database', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return response()->json([
                    'error' => 'Errore nel salvataggio nel database',
                    'details' => config('app.debug') ? $e->getMessage() : 'Errore durante il salvataggio'
                ], 500);
            }

            // 5. Ritorno del PDF come risposta API
            Log::info('Invio risposta PDF', [
                'filename' => $filename,
                'content_length' => strlen($pdfContent),
            ]);
            
            return response($pdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Length', strlen($pdfContent));

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Errore di validazione', [
                'errors' => $e->errors(),
            ]);
            return response()->json([
                'error' => 'Errore di validazione',
                'details' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Errore generico nel controller', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'error' => 'Errore durante la generazione del PDF',
                'details' => config('app.debug') ? $e->getMessage() : 'Si è verificato un errore. Controlla i log per i dettagli.'
            ], 500);
        }
    }
}