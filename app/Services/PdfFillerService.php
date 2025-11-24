<?php
// app/Services/PdfFillerService.php

namespace App\Services;

use setasign\Fpdi\Tcpdf\Fpdi;

class PdfFillerService
{
    /**
     * Aggiunge spaziatura tra i caratteri di una stringa
     * 
     * @param string $text Testo originale
     * @param float $spacing Spaziatura in mm (default 0.5)
     * @return string Testo con spazi aggiunti
     */
    private function addCharSpacing(string $text, float $spacing = 0.5): string
    {
        if ($spacing <= 0) {
            return $text;
        }
        
        // Converte la spaziatura in mm in spazi approssimativi
        // 1mm ≈ 2.83 punti, quindi per 0.5mm usiamo circa 1-2 spazi
        $spacesPerChar = max(1, round($spacing * 2));
        $spaces = str_repeat(' ', $spacesPerChar);
        
        // Inserisce spazi tra ogni carattere
        return implode($spaces, mb_str_split($text));
    }

    /**
     * Riempie il PDF in base al codice ONLUS e ai dati forniti.
     * Restituisce il contenuto binario del PDF.
     */
    public function fillPdf(string $onlusCode, array $data, string $signatureBase64 = null): string
    {
        // 1. Carica la configurazione
        $config = config("pdf_mappings.{$onlusCode}");
        if (!$config) {
            throw new \Exception("Mappatura non trovata per l'onlus: " . $onlusCode);
        }

        $pdf = new Fpdi();
        
        // 2. Importa il template
        $sourceFile = $config['template_path'];
        $pageCount = $pdf->setSourceFile($sourceFile);
        
        // Spaziatura globale tra caratteri per questa ONLUS (in mm)
        // Se non specificata, usa 0 (nessuna spaziatura)
        $charSpacing = $config['char_spacing'] ?? 0;
        
        // --- LOOP SULLE PAGINE ---
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            
            // Ottieni le dimensioni del template
            $size = $pdf->getTemplateSize($templateId);
            
            // Aggiungi una pagina con le stesse dimensioni del template
            $pdf->AddPage($size['orientation'] ?? 'P', [$size['width'], $size['height']]);
            
            // Usa il template con le dimensioni originali (senza scalatura)
            $pdf->useTemplate($templateId, 0, 0, $size['width'], $size['height'], false);

            // 3. Imposta Stile Testo
            $pdf->SetFont('Helvetica', '', 12);
            $pdf->SetTextColor(0, 0, 0);
            
            // Disabilita l'auto page break per questa pagina
            $pdf->SetAutoPageBreak(false);

            // 4. Popola i campi
            foreach ($config['fields'] as $field => $coords) {
                
                // Trattamento per campi testuali
                if (!isset($coords['type']) && isset($data[$field])) {
                    // Applica la spaziatura globale dell'ONLUS al testo
                    $text = $charSpacing > 0 ? $this->addCharSpacing($data[$field], $charSpacing) : $data[$field];
                    
                    $pdf->SetXY($coords['x'], $coords['y']);
                    $pdf->Write(0, $text);
                }

                // Trattamento per Checkbox (scrive una 'X' se il valore è vero/presente)
                if (isset($coords['type']) && $coords['type'] === 'checkbox' && 
                    isset($data[$field]) && (bool)$data[$field] === true) {
                    
                    // Scrive 'X' nel quadratino della checkbox
                    $pdf->SetFont('ZapfDingbats', '', 10);
                    $pdf->SetXY($coords['x'], $coords['y']);
                    $pdf->Cell(0, 0, '4', 0, 0, 'L');
                    $pdf->SetFont('Helvetica', '', 12); // Torna al font normale
                }

                // Trattamento per Firma (Immagine Base64)
                if ($field === 'firma_cc' && isset($data[$field]) && $data[$field] && $coords['type'] === 'image') {
                    try {
                        $imgData = $data[$field];
                        
                        // Rimuovi il prefisso data URI se presente
                        if (strpos($imgData, ',') !== false) {
                            $imgData = substr($imgData, strpos($imgData, ',') + 1);
                        }
                        
                        // Decodifica il Base64
                        $decodedImage = base64_decode($imgData, true);
                        
                        if ($decodedImage === false) {
                            throw new \Exception("Errore nella decodifica Base64 della firma");
                        }
                        
                        $imageInfo = @getimagesizefromstring($decodedImage);
                        if ($imageInfo === false) {
                            throw new \Exception("Il dato fornito non è un'immagine valida");
                        }
                        
                        $imageType = $imageInfo[2];
                        $format = '';
                        switch ($imageType) {
                            case IMAGETYPE_JPEG:
                                $format = 'JPEG';
                                break;
                            case IMAGETYPE_PNG:
                                $format = 'PNG';
                                break;
                            case IMAGETYPE_GIF:
                                $format = 'GIF';
                                break;
                            default:
                                throw new \Exception("Formato immagine non supportato. Usa JPEG o PNG");
                        }
                        
                        $pdf->Image('@' . $decodedImage, 
                            $coords['x'], $coords['y'], 
                            $coords['width'], $coords['height'],
                            $format
                        );
                    } catch (\Exception $e) {
                        \Log::error("Errore nell'inserimento della firma: " . $e->getMessage());
                    }
                }
            }
        }
        
        return $pdf->Output('modulo_compilato.pdf', 'S'); 
    }
}