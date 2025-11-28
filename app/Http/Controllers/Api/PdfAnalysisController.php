<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ClaudeVisionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Controller per analisi PDF con Claude Vision API
 * 
 * Endpoints:
 * - POST /api/pdf/analyze - Analizza nuovo PDF
 * - POST /api/pdf/compare - Confronta con mappatura manuale
 * - GET /api/pdf/mappings/{ngo} - Ottieni mappatura salvata
 */
class PdfAnalysisController extends Controller
{
    private ClaudeVisionService $claudeService;

    public function __construct(ClaudeVisionService $claudeService)
    {
        $this->claudeService = $claudeService;
    }

    /**
     * Analizza un PDF e restituisce la mappatura dei campi
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function analyze(Request $request): JsonResponse
    {
        // Validazione input
        $validator = Validator::make($request->all(), [
            'pdf' => 'required|file|mimes:pdf|max:10240', // Max 10MB
            'ngo_code' => 'required|string|in:EMERGENCY,LAV,TELETHON,IEO,GEMELLI,UNKNOWN',
            'output_format' => 'nullable|array',
            'use_cache' => 'nullable|boolean'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errore validazione',
                'errors' => $validator->errors()
            ], 422);
        }
    
        try {

            // Aumenta il timeout di PHP per permettere chiamate API lunghe
            set_time_limit(120); // 2 minutes
            
            $ngoCode = $request->input('ngo_code');
            $outputFormat = $request->input('output_format', []);
            $useCache = $request->input('use_cache', true);
    
            // Salva PDF temporaneamente
            $pdfFile = $request->file('pdf');
            $pdfHash = md5_file($pdfFile->getRealPath());
            
            // Controlla cache se abilitata
            $cacheKey = "pdf_mapping:{$ngoCode}:{$pdfHash}";
            
            if ($useCache && Cache::has($cacheKey)) {
                $cached = Cache::get($cacheKey);
                return response()->json([
                    'success' => true,
                    'message' => 'Mappatura recuperata da cache',
                    'data' => $cached,
                    'from_cache' => true
                ]);
            }
    
            // Salva il file PDF usando Storage
            $pdfPath = $pdfFile->store('temp/pdfs', 'local');
            
            // Usa Storage::disk()->path() per ottenere il percorso completo corretto
            $fullPdfPath = Storage::disk('local')->path($pdfPath);
    
            // Verifica che il file sia stato salvato correttamente
            if (!file_exists($fullPdfPath)) {
                throw new Exception("File PDF non salvato correttamente: {$fullPdfPath}. Percorso relativo: {$pdfPath}");
            }
    
            Log::info('PDF salvato per analisi', [
                'pdf_path' => $pdfPath,
                'full_path' => $fullPdfPath,
                'exists' => file_exists($fullPdfPath),
                'size' => filesize($fullPdfPath)
            ]);
    
            // Analizza PDF
            $result = $this->claudeService->analyzePdf(
                $fullPdfPath,
                $ngoCode,
                $outputFormat
            );
    
            // Pulisci file temporaneo
            Storage::disk('local')->delete($pdfPath);
    
            // Salva in cache (24 ore)
            if ($useCache) {
                Cache::put($cacheKey, $result, now()->addHours(24));
            }
    
            return response()->json([
                'success' => true,
                'message' => 'PDF analizzato con successo',
                'data' => [
                    'mapping' => $result['result'],
                    'usage' => $result['usage'],
                    'metadata' => [
                        ...$result['metadata'],
                        'ngo_code' => $ngoCode,
                        'pdf_hash' => $pdfHash
                    ]
                ],
                'from_cache' => false
            ]);
    
        } catch (Exception $e) {
            Log::error('Errore durante analisi PDF', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Errore durante analisi PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confronta mappatura AI con mappatura manuale di riferimento
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function compare(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ai_mapping' => 'required|array',
            'manual_mapping' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errore validazione',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $aiMapping = $request->input('ai_mapping');
            $manualMapping = $request->input('manual_mapping');

            $comparison = $this->claudeService->compareWithManual($aiMapping, $manualMapping);

            return response()->json([
                'success' => true,
                'message' => 'Confronto completato',
                'data' => $comparison
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante confronto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restituisce la mappatura manuale di riferimento per una ONLUS
     *
     * @param string $ngo
     * @return JsonResponse
     */
    public function getMapping(string $ngo): JsonResponse
    {
        $mappings = $this->getManualMappings();

        $ngoUpper = strtoupper($ngo);

        if (!isset($mappings[$ngoUpper])) {
            return response()->json([
                'success' => false,
                'message' => 'Mappatura non trovata per ONLUS: ' . $ngo
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'ngo_code' => $ngoUpper,
                'template_path' => $mappings[$ngoUpper]['template_path'],
                'fields' => $mappings[$ngoUpper]['fields'],
                'total_fields' => count($mappings[$ngoUpper]['fields'])
            ]
        ]);
    }

    /**
     * Restituisce tutte le ONLUS supportate
     *
     * @return JsonResponse
     */
    public function getSupportedNgos(): JsonResponse
    {
        $mappings = $this->getManualMappings();

        return response()->json([
            'success' => true,
            'data' => [
                'ngos' => array_keys($mappings),
                'total' => count($mappings)
            ]
        ]);
    }

    /**
     * Test endpoint per verificare configurazione
     *
     * @return JsonResponse
     */
    public function test(): JsonResponse
    {
        $apiKey = config('services.anthropic.api_key');
        $model = config('services.anthropic.model');
        
        $tempDir = storage_path('app/temp');
        $tempPdfsDir = storage_path('app/temp/pdfs');

        return response()->json([
            'success' => true,
            'data' => [
                'api_configured' => !empty($apiKey),
                'model' => $model,
                'pdftoppm_available' => $this->checkPdftoppm(),
                'cache_enabled' => config('cache.default') !== 'array',
                'storage_writable' => is_writable(storage_path('app')),
                'temp_dir_exists' => is_dir($tempDir),
                'temp_dir_writable' => is_dir($tempDir) && is_writable($tempDir),
                'temp_pdfs_dir_exists' => is_dir($tempPdfsDir),
                'temp_pdfs_dir_writable' => is_dir($tempPdfsDir) && is_writable($tempPdfsDir),
            ]
        ]);
    }

    /**
     * Verifica disponibilità pdftoppm
     *
     * @return bool
     */
    private function checkPdftoppm(): bool
    {
        exec('which pdftoppm', $output, $returnCode);
        return $returnCode === 0;
    }

    /**
     * Mappature manuali di riferimento
     * TODO: Spostare in database o file di configurazione
     *
     * @return array
     */
    private function getManualMappings(): array
    {
        return [
            'EMERGENCY' => [
                'template_path' => storage_path('app/pdf_templates/emergency.pdf'),
                'char_spacing' => 0.7,
                'fields' => [
                    'supporter.last_name' => ['x' => 30, 'y' => 64],
                    'supporter.first_name' => ['x' => 120, 'y' => 64],
                    'supporter.address.street_name' => ['x' => 30, 'y' => 72],
                    'supporter.address.street_number' => ['x' => 160, 'y' => 72],
                    'supporter.address.city' => ['x' => 30, 'y' => 80],
                    'supporter.address.zip_code' => ['x' => 130, 'y' => 80],
                    'supporter.address.province' => ['x' => 170, 'y' => 80],
                    'supporter.birth_date' => ['x' => 30, 'y' => 88],
                    'supporter.birth_place' => ['x' => 90, 'y' => 88],
                    'supporter.birth_province' => ['x' => 170, 'y' => 88],
                    'supporter.fiscal_code' => ['x' => 30, 'y' => 96],
                    'supporter.gender_f' => ['x' => 163, 'y' => 96, 'type' => 'checkbox'],
                    'supporter.gender_m' => ['x' => 172, 'y' => 96, 'type' => 'checkbox'],
                    'supporter.contacts.phone' => ['x' => 30, 'y' => 104],
                    'supporter.contacts.mobile' => ['x' => 100, 'y' => 104],
                    'supporter.contacts.email' => ['x' => 30, 'y' => 112],
                    // ... altri campi
                ]
            ],
            'LAV' => [
                'template_path' => storage_path('app/pdf_templates/lav.pdf'),
                'char_spacing' => 0.7,
                'fields' => [
                    'supporter.first_name' => ['x' => 30, 'y' => 64],
                    'supporter.last_name' => ['x' => 120, 'y' => 64],
                    // ... altri campi
                ]
            ],
            // ... altre ONLUS
        ];
    }
}
