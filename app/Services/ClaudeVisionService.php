<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;

/**
 * Service per analisi PDF con Claude Vision API
 * 
 * Gestisce:
 * - Conversione PDF in immagine
 * - Chiamata a Claude Vision API
 * - Parsing e formattazione risposta
 * - Caching risultati
 */
class ClaudeVisionService
{
    private string $apiKey;
    private string $model;
    private array $config;

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key');
        $this->model = config('services.anthropic.model', 'claude-sonnet-4-20250514');
        
        $this->config = [
            'image_dpi' => 100,
            'max_tokens' => 4096,
            'timeout' => 90,
        ];
    }

    /**
     * Ottimizza e comprime l'immagine PNG
     *
     * @param string $imagePath
     * @return string Path immagine ottimizzata
     */
    private function optimizeImage(string $imagePath): string
    {
        $optimizedPath = $imagePath . '_optimized.png';
        
        // Usa ImageMagick o GD per ridimensionare/comprimere
        // Esempio con ImageMagick (se disponibile)
        $command = sprintf(
            'convert %s -resize 2000x2000> -quality 85 -strip %s',
            escapeshellarg($imagePath),
            escapeshellarg($optimizedPath)
        );
        
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode === 0 && file_exists($optimizedPath)) {
            // Elimina l'immagine originale non ottimizzata
            unlink($imagePath);
            return $optimizedPath;
        }
        
        // Se ImageMagick non è disponibile, usa l'immagine originale
        return $imagePath;
    }

    /**
     * Analizza un PDF e restituisce la mappatura dei campi
     *
     * @param string $pdfPath Path al file PDF
     * @param string $ngoCode Codice ONLUS (EMERGENCY, LAV, etc.)
     * @param array $outputFormat Formato output desiderato
     * @return array
     * @throws Exception
     */
    public function analyzePdf(string $pdfPath, string $ngoCode = 'UNKNOWN', array $outputFormat = []): array
    {
        Log::info("ClaudeVisionService: Inizio analisi PDF", [
            'pdf_path' => $pdfPath,
            'ngo_code' => $ngoCode
        ]);

        try {
            $startTime = microtime(true);
            
            // Step 1: Converti PDF in immagine
            $imagePath = $this->convertPdfToImage($pdfPath);
            Log::info("PDF convertito", ['duration' => microtime(true) - $startTime]);
            
            // Step 1.5: Ottimizza immagine (opzionale)
            // $imagePath = $this->optimizeImage($imagePath);
            
            // Step 2: Converti immagine in base64
            $imageBase64 = $this->imageToBase64($imagePath);
            $imageSizeKB = strlen($imageBase64) / 1024;
            Log::info("Immagine convertita in Base64", [
                'size_kb' => round($imageSizeKB, 2),
                'duration' => microtime(true) - $startTime
            ]);
            
            // Step 3: Genera prompt personalizzato
            $prompt = $this->generatePrompt($ngoCode, $outputFormat);
            
            // Step 4: Chiama Claude Vision API
            $apiStartTime = microtime(true);
            $result = $this->callClaudeVision($imageBase64, $prompt);
            Log::info("API Claude completata", [
                'duration' => microtime(true) - $apiStartTime,
                'total_duration' => microtime(true) - $startTime
            ]);
            
            // Step 5: Pulisci file temporanei
            $this->cleanup($imagePath);
            
            Log::info("ClaudeVisionService: Analisi completata", [
                'fields_detected' => count($result['result']['fields'] ?? []),
                'cost' => $result['usage']['cost']['total'] ?? 0,
                'total_duration' => round(microtime(true) - $startTime, 2)
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            Log::error("ClaudeVisionService: Errore durante analisi", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Converte PDF in immagine PNG
     *
     * @param string $pdfPath
     * @return string Path immagine generata
     * @throws Exception
     */
    private function convertPdfToImage(string $pdfPath): string
    {
        // Verifica che il file PDF esista
        if (!file_exists($pdfPath)) {
            throw new Exception("File PDF non trovato: {$pdfPath}");
        }

        $tempDir = storage_path('app/temp');
        
        // Crea directory temp se non esiste
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $outputPath = $tempDir . '/pdf_' . uniqid() . '.png';
        $tempPath = $tempDir . '/pdf_' . uniqid();

        // Usa pdftoppm per conversione
        $command = sprintf(
            'pdftoppm -png -r %d -f 1 -l 1 %s %s',
            $this->config['image_dpi'],
            escapeshellarg($pdfPath),
            escapeshellarg($tempPath)
        );

        exec($command . ' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception("Errore conversione PDF: " . implode("\n", $output));
        }

        // pdftoppm aggiunge -1 al nome file
        $generatedFile = $tempPath . '-1.png';
        
        if (!file_exists($generatedFile)) {
            throw new Exception("File immagine non generato: " . $generatedFile);
        }

        rename($generatedFile, $outputPath);

        Log::info("PDF convertito in immagine", [
            'pdf_path' => $pdfPath,
            'image_path' => $outputPath,
            'size_kb' => round(filesize($outputPath) / 1024, 2)
        ]);

        return $outputPath;
    }

    /**
     * Converte immagine in base64
     *
     * @param string $imagePath
     * @return string
     */
    private function imageToBase64(string $imagePath): string
    {
        $imageData = file_get_contents($imagePath);
        return base64_encode($imageData);
    }

    /**
     * Genera prompt personalizzato per Claude Vision
     *
     * @param string $ngoCode
     * @param array $outputFormat
     * @return string
     */
    private function generatePrompt(string $ngoCode, array $outputFormat): string
    {
        $formatExample = !empty($outputFormat) 
            ? json_encode($outputFormat, JSON_PRETTY_PRINT)
            : $this->getDefaultOutputFormat();

        return <<<PROMPT
    Sei un esperto di analisi di moduli PDF per donazioni italiane.

    **TASK:** Analizza questo modulo per {$ngoCode} e identifica TUTTI i campi compilabili con massima precisione.

    **OUTPUT RICHIESTO (SOLO JSON VALIDO, NESSUN ALTRO TESTO):**

    {$formatExample}

    **REGOLE CRITICHE:**
    1. Coordinate in MILLIMETRI da angolo top-left (0,0)
    2. Identifica TUTTI i tipi di campo:
    - text: campi testo standard
    - checkbox: caselle SI/NO o opzioni multiple  
    - signature: area firma
    - amount: importi predefiniti
    3. Per IBAN, identifica separatamente tutti i componenti
    4. Stima char_spacing per campi con caratteri separati (0.7-0.8mm)
    5. Rileva importi predefiniti per donazioni
    6. Identifica sezioni logiche del form
    7. Confidence score: 0-1 (>0.9 = molto sicuro)

    **CONVENZIONI NOMI CAMPI:**
    - supporter.* = dati donatore
    - payment.bank.* = dati bancari
    - payment.credit_card.* = carta credito
    - donation.* = scelta donazione
    - authorization.* = firma/autorizzazione
    - ngo_specific.{$ngoCode}.* = campi specifici ONLUS
    - fundraiser.* = dati dialogatore

    **IMPORTANTE - FORMATO RISPOSTA:**
    - Rispondi SOLO con JSON valido
    - NO testo prima o dopo il JSON
    - NO markdown code blocks (on o ```)
    - NO commenti nel JSON
    - NO spiegazioni aggiuntive
    - SOLO JSON puro, valido, ben formato
    - Inizia direttamente con { e termina con }
    PROMPT;
    }

    /**
     * Formato output di default
     *
     * @return string
     */
    private function getDefaultOutputFormat(): string
    {
        return json_encode([
            'form_analysis' => [
                'ngo_detected' => 'string',
                'language' => 'italian',
                'total_fields_detected' => 0,
                'page_dimensions' => ['width_mm' => 0, 'height_mm' => 0]
            ],
            'fields' => [
                [
                    'json_path' => 'supporter.first_name',
                    'label_detected' => 'Nome',
                    'coordinates' => ['x' => 0, 'y' => 0, 'width' => 0],
                    'field_type' => 'text',
                    'char_spacing_mm' => 0.7,
                    'is_required' => true,
                    'section' => 'Dati Personali',
                    'confidence' => 0.95,
                    'notes' => 'Descrizione campo'
                ]
            ],
            'sections_detected' => [
                [
                    'name' => 'Dati Personali',
                    'y_start' => 0,
                    'y_end' => 0,
                    'fields_count' => 0
                ]
            ],
            'special_fields' => [
                'donation_presets' => [],
                'signature_area' => ['x' => 0, 'y' => 0, 'width' => 0, 'height' => 0],
                'iban_fields' => []
            ]
        ], JSON_PRETTY_PRINT);
    }


    /**
 * Estrae JSON dalla risposta testuale di Claude
 * Gestisce vari formati: markdown code blocks, testo prima/dopo JSON, etc.
 *
 * @param string $responseText
 * @return string
 */
    /**
     * Prova a correggere errori comuni nel JSON
     *
     * @param string $jsonText
     * @return string
     */
    private function fixCommonJsonErrors(string $jsonText): string
    {
        $text = $jsonText;
        
        // Rimuovi trailing comma prima di } o ]
        $text = preg_replace('/,\s*}/', '}', $text);
        $text = preg_replace('/,\s*]/', ']', $text);
        
        // Corregge virgolette non chiuse (sostituisce virgolette strane con virgolette standard)
        $smartQuotes = ["\xE2\x80\x9C", "\xE2\x80\x9D", "\xE2\x80\x98", "\xE2\x80\x99"]; // UTF-8 encoded smart quotes
        $text = str_replace($smartQuotes, '"', $text);
        
        // Rimuovi caratteri non validi in stringhe JSON
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $text);
        
        // Corregge escape non validi (rimuove backslash non validi)
        // Nota: questa regex è complessa, la semplifichiamo
        // $text = preg_replace('/\\\(?!["\\\/bfnrt]|u[0-9a-fA-F]{4})/', '', $text);
        
        // Rimuovi commenti residui
        $text = preg_replace('/\/\*.*?\*\//s', '', $text);
        $text = preg_replace('/\/\/.*$/m', '', $text);
        
        return $text;
    }

    /**
     * Estrae JSON dalla risposta testuale di Claude
     * Gestisce vari formati: markdown code blocks, testo prima/dopo JSON, etc.
     *
     * @param string $responseText
     * @return string
     */
    private function extractJsonFromResponse(string $responseText): string
    {
        $text = trim($responseText);
        
        // Rimuovi BOM e caratteri di controllo all'inizio
        $text = preg_replace('/^\xEF\xBB\xBF/', '', $text);
        $text = preg_replace('/^[\x00-\x1F\x7F]+/', '', $text);
        
        // Rimuovi markdown code blocks (vari formati possibili)
        $text = preg_replace('/^```json\s*/im', '', $text);
        $text = preg_replace('/^```\s*/im', '', $text);
        $text = preg_replace('/```\s*$/im', '', $text);
        $text = preg_replace('/```\s*$/m', '', $text);
        
        // Rimuovi eventuali commenti JSON (non standard)
        $text = preg_replace('/\/\*.*?\*\//s', '', $text);
        $text = preg_replace('/\/\/.*$/m', '', $text);
        
        // Cerca il primo { e l'ultimo } per estrarre solo il JSON
        // Usa mb_strpos per gestire meglio i caratteri multibyte
        $firstBrace = mb_strpos($text, '{');
        $lastBrace = mb_strrpos($text, '}');
        
        if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
            $text = mb_substr($text, $firstBrace, $lastBrace - $firstBrace + 1);
        } elseif ($firstBrace === false) {
            // Se non trova {, prova a cercare [ per array JSON
            $firstBracket = mb_strpos($text, '[');
            $lastBracket = mb_strrpos($text, ']');
            if ($firstBracket !== false && $lastBracket !== false && $lastBracket > $firstBracket) {
                $text = mb_substr($text, $firstBracket, $lastBracket - $firstBracket + 1);
            }
        }
        
        // Rimuovi caratteri di controllo residui (ma mantieni spazi e newline nel JSON)
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        
        // Normalizza newline (mantieni \n nel JSON)
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        
        // Rimuovi spazi/tab eccessivi all'inizio/fine di ogni riga (ma mantieni struttura JSON)
        $lines = explode("\n", $text);
        $cleanedLines = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed !== '') {
                $cleanedLines[] = $trimmed;
            }
        }
        $text = implode("\n", $cleanedLines);
        
        // Verifica che inizi con { o [
        if (!preg_match('/^[{\[]/', $text)) {
            Log::warning('JSON estratto non inizia con { o [', [
                'first_chars' => substr($text, 0, 50)
            ]);
        }
        
        return trim($text);
    }

    /**
     * Chiama Claude Vision API
     *
     * @param string $imageBase64
     * @param string $prompt
     * @return array
     * @throws Exception
     */
    private function callClaudeVision(string $imageBase64, string $prompt): array
    {
        $startTime = microtime(true);

        $response = Http::timeout($this->config['timeout'])
            ->withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $this->model,
                'max_tokens' => $this->config['max_tokens'],
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'image',
                                'source' => [
                                    'type' => 'base64',
                                    'media_type' => 'image/png',
                                    'data' => $imageBase64,
                                ]
                            ],
                            [
                                'type' => 'text',
                                'text' => $prompt
                            ]
                        ]
                    ]
                ]
            ]);

        $duration = microtime(true) - $startTime;

        if (!$response->successful()) {
            Log::error('Errore API Claude', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new Exception("Errore API Claude: " . $response->body());
        }

        $data = $response->json();

        // Estrai contenuto testuale
        $responseText = $data['content'][0]['text'] ?? '';
        
        // Log della risposta raw per debug (primi 500 caratteri)
        Log::info('Risposta Claude raw (primi 500 caratteri)', [
            'preview' => substr($responseText, 0, 500),
            'length' => strlen($responseText)
        ]);
        
        // Pulisci e estrai JSON dalla risposta
        $jsonText = $this->extractJsonFromResponse($responseText);
        
        // Prova a decodificare il JSON
        $result = json_decode($jsonText, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Prova a correggere errori comuni
            $jsonText = $this->fixCommonJsonErrors($jsonText);
            $result = json_decode($jsonText, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Salva la risposta completa in un file temporaneo per debug
                $debugFile = storage_path('app/temp/claude_response_' . uniqid() . '.txt');
                $debugDir = dirname($debugFile);
                if (!is_dir($debugDir)) {
                    mkdir($debugDir, 0755, true);
                }
                file_put_contents($debugFile, "=== RAW RESPONSE ===\n" . $responseText . "\n\n=== EXTRACTED JSON ===\n" . $jsonText);
                
                // Log della risposta completa per debug
                Log::error('Errore parsing JSON dopo correzione', [
                    'error' => json_last_error_msg(),
                    'json_error_code' => json_last_error(),
                    'json_text_length' => strlen($jsonText),
                    'json_text_preview' => substr($jsonText, 0, 2000),
                    'raw_response_length' => strlen($responseText),
                    'raw_response_preview' => substr($responseText, 0, 2000),
                    'debug_file' => $debugFile
                ]);
                
                throw new Exception("Errore parsing JSON risposta: " . json_last_error_msg() . ". Risposta salvata in: {$debugFile}");
            }
        }

        // Calcola costo effettivo
        $inputTokens = $data['usage']['input_tokens'] ?? 0;
        $outputTokens = $data['usage']['output_tokens'] ?? 0;
        $inputCost = ($inputTokens / 1_000_000) * 3.0;
        $outputCost = ($outputTokens / 1_000_000) * 15.0;
        $totalCost = $inputCost + $outputCost;

        return [
            'result' => $result,
            'usage' => [
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'total_tokens' => $inputTokens + $outputTokens,
                'cost' => [
                    'input' => round($inputCost, 4),
                    'output' => round($outputCost, 4),
                    'total' => round($totalCost, 4),
                    'total_eur' => round($totalCost * 0.92, 4)
                ],
                'duration' => round($duration, 2)
            ],
            'metadata' => [
                'model' => $this->model,
                'timestamp' => now()->toIso8601String(),
                'image_dpi' => $this->config['image_dpi']
            ]
        ];
    }

    /**
     * Pulisce file temporanei
     *
     * @param string $imagePath
     * @return void
     */
    private function cleanup(string $imagePath): void
    {
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    /**
     * Confronta mappatura AI con mappatura manuale di riferimento
     *
     * @param array $aiMapping
     * @param array $manualMapping
     * @return array
     */
    public function compareWithManual(array $aiMapping, array $manualMapping): array
    {
        $aiFields = [];
        foreach ($aiMapping['fields'] ?? [] as $field) {
            $aiFields[$field['json_path']] = $field['coordinates'];
        }

        $comparison = [
            'total_manual_fields' => count($manualMapping),
            'total_ai_fields' => count($aiFields),
            'correctly_identified' => 0,
            'missed_fields' => [],
            'extra_fields' => [],
            'coordinate_accuracy' => []
        ];

        // Verifica campi identificati correttamente
        foreach ($manualMapping as $fieldName => $manual) {
            if (isset($aiFields[$fieldName])) {
                $ai = $aiFields[$fieldName];
                $comparison['correctly_identified']++;

                // Calcola distanza coordinate
                $distanceX = abs($manual['x'] - $ai['x']);
                $distanceY = abs($manual['y'] - $ai['y']);
                $distance = sqrt($distanceX ** 2 + $distanceY ** 2);

                $comparison['coordinate_accuracy'][] = [
                    'field' => $fieldName,
                    'manual' => $manual,
                    'ai' => $ai,
                    'error_x' => $distanceX,
                    'error_y' => $distanceY,
                    'error_total' => round($distance, 2),
                    'acceptable' => $distance < 5
                ];
            } else {
                $comparison['missed_fields'][] = $fieldName;
            }
        }

        // Trova campi extra
        foreach ($aiFields as $fieldName => $coords) {
            if (!isset($manualMapping[$fieldName])) {
                $comparison['extra_fields'][] = $fieldName;
            }
        }

        // Calcola metriche
        $idRate = ($comparison['correctly_identified'] / count($manualMapping)) * 100;
        $avgError = !empty($comparison['coordinate_accuracy'])
            ? array_sum(array_column($comparison['coordinate_accuracy'], 'error_total')) / count($comparison['coordinate_accuracy'])
            : 0;
        $acceptable = count(array_filter($comparison['coordinate_accuracy'], fn($item) => $item['acceptable']));
        $coordRate = !empty($comparison['coordinate_accuracy'])
            ? ($acceptable / count($comparison['coordinate_accuracy'])) * 100
            : 0;

        $finalScore = ($idRate * 0.6) + ($coordRate * 0.4);

        return [
            ...$comparison,
            'metrics' => [
                'identification_rate' => round($idRate, 1),
                'coordinate_accuracy_rate' => round($coordRate, 1),
                'average_error_mm' => round($avgError, 2),
                'final_score' => round($finalScore, 1)
            ]
        ];
    }
}
