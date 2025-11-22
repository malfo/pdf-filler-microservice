<?php
// app/Services/PdfFillerService.php

namespace App\Services;

use setasign\Fpdi\Tcpdf\Fpdi;

class PdfFillerService
{
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
        
        // --- LOOP SULLE PAGINE ---
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            
            // Ottieni le dimensioni del template
            $size = $pdf->getTemplateSize($templateId);
            
            // Aggiungi una pagina con le stesse dimensioni del template
            // $size['width'] e $size['height'] sono in mm
            $pdf->AddPage($size['orientation'] ?? 'P', [$size['width'], $size['height']]);
            
            // Usa il template con le dimensioni originali (senza scalatura)
            $pdf->useTemplate($templateId, 0, 0, $size['width'], $size['height'], false);

            // 3. Imposta Stile Testo
            $pdf->SetFont('Helvetica', '', 12);
            $pdf->SetTextColor(0, 0, 0);
            // Imposta la spaziatura tra lettere (in unità di misura del PDF, tipicamente mm)
            // Valore positivo = più spazio, negativo = meno spazio
            $pdf->SetCharSpacing(1.0); // Esempio: 0.5mm di spaziatura tra ogni lettera

            // Disabilita l'auto page break per questa pagina
            $pdf->SetAutoPageBreak(false);

            // 4. Popola i campi
            foreach ($config['fields'] as $field => $coords) {
                
                // Trattamento per campi testuali
                if (!isset($coords['type']) && isset($data[$field])) {
                    $pdf->SetXY($coords['x'], $coords['y']);
                    $pdf->Write(0, $data[$field]);
                }

                // Trattamento per Checkbox (scrive una 'X' se il valore è vero/presente)
                if (isset($coords['type']) && $coords['type'] === 'checkbox' && 
                    isset($data[$field]) && (bool)$data[$field] === true) {
                    
                    // Scrive 'X' nel quadratino della checkbox
                    $pdf->SetFont('ZapfDingbats', '', 10); // Font speciale per simboli (√ o X)
                    $pdf->SetXY($coords['x'], $coords['y']);
                    $pdf->Cell(0, 0, '4', 0, 0, 'L'); // '4' in ZapfDingbats è un segno di spunta
                    $pdf->SetFont('Helvetica', '', 10); // Torna al font normale
                }

                // Trattamento per Firma (Immagine Base64)
                if ($field === 'firma_cc' && isset($data[$field]) && $data[$field] && $coords['type'] === 'image') {
                    try {
                        $imgData = $data[$field];
                        
                        // Rimuovi il prefisso data URI se presente (es: "data:image/png;base64,")
                        if (strpos($imgData, ',') !== false) {
                            $imgData = substr($imgData, strpos($imgData, ',') + 1);
                        }
                        
                        // Decodifica il Base64
                        $decodedImage = base64_decode($imgData, true);
                        
                        // Verifica che la decodifica sia andata a buon fine
                        if ($decodedImage === false) {
                            throw new \Exception("Errore nella decodifica Base64 della firma");
                        }
                        
                        // Verifica che sia un'immagine valida controllando i primi byte (magic bytes)
                        $imageInfo = @getimagesizefromstring($decodedImage);
                        if ($imageInfo === false) {
                            throw new \Exception("Il dato fornito non è un'immagine valida");
                        }
                        
                        // Determina il formato dall'immagine
                        $imageType = $imageInfo[2]; // IMAGETYPE_JPEG, IMAGETYPE_PNG, etc.
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
                        
                        // Utilizza Image con un @ per leggere dalla stringa decodificata (stream)
                        $pdf->Image('@' . $decodedImage, 
                            $coords['x'], $coords['y'], 
                            $coords['width'], $coords['height'],
                            $format
                        );
                    } catch (\Exception $e) {
                        // Log dell'errore e continua senza la firma
                        \Log::error("Errore nell'inserimento della firma: " . $e->getMessage());
                        // Opzionalmente, puoi lanciare l'eccezione per interrompere il processo
                        // throw $e;
                    }
                }
            }
        }
        // 'S' = Restituisce il PDF come stringa (contenuto binario)
        return $pdf->Output('modulo_compilato.pdf', 'S'); 
    }
}