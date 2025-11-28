<?php
// app/Services/PdfFillerService.php

namespace App\Services;

use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Support\Arr; // Opzionale, per Arr::get()

class PdfFillerService
{
    /**
     * Aggiunge spaziatura tra i caratteri di una stringa
     */
    private function addCharSpacing(string $text, float $spacing = 0.5): string
    {
        if ($spacing <= 0) {
            return $text;
        }
        
        $spacesPerChar = max(1, round($spacing * 2));
        $spaces = str_repeat(' ', $spacesPerChar);
        
        return implode($spaces, mb_str_split($text));
    }

    public function fillPdf(string $onlusCode, array $data, string $signatureBase64 = null): string
    {
        $config = config("pdf_mappings.{$onlusCode}");
        if (!$config) {
            throw new \Exception("Mappatura non trovata per l'onlus: " . $onlusCode);
        }

        $pdf = new Fpdi();
        $sourceFile = $config['template_path'];
        $pageCount = $pdf->setSourceFile($sourceFile);
        $charSpacing = $config['char_spacing'] ?? 0;
        
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);
            
            $pdf->AddPage($size['orientation'] ?? 'P', [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId, 0, 0, $size['width'], $size['height'], false);

            $pdf->SetFont('Helvetica', '', 13);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetAutoPageBreak(false);

            foreach ($config['fields'] as $field => $coords) {
                
                // Trattamento per campi testuali
                if (!isset($coords['type'])) {
                    // Usa data_get() invece di getNestedValue()
                    $value = data_get($data, $field);
                    
                    if ($value !== null && $value !== '') {
                        $text = is_string($value) ? $value : (string)$value;
                        $text = $charSpacing > 0 ? $this->addCharSpacing($text, $charSpacing) : $text;
                        
                        $pdf->SetXY($coords['x'], $coords['y']);
                        $pdf->Write(0, $text);
                    }
                }

                // Trattamento per Checkbox
                if (isset($coords['type']) && $coords['type'] === 'checkbox') {
                    $value = data_get($data, $field, false);
                    
                    if ((bool)$value === true) {
                        $pdf->SetFont('ZapfDingbats', '', 10);
                        $pdf->SetXY($coords['x'], $coords['y']);
                        $pdf->Cell(0, 0, '4', 0, 0, 'L');
                        $pdf->SetFont('Helvetica', '', 12);
                    }
                }

                // Trattamento per Firma (Immagine Base64)
                if (isset($coords['type']) && in_array($coords['type'], ['image', 'signature'])) {
                    $value = data_get($data, $field);
                    
                    if ($value && !empty($value)) {
                        try {
                            $imgData = $value;
                            
                            if (strpos($imgData, ',') !== false) {
                                $imgData = substr($imgData, strpos($imgData, ',') + 1);
                            }
                            
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
                                $coords['width'] ?? 50, $coords['height'] ?? 10,
                                $format
                            );
                        } catch (\Exception $e) {
                            \Log::error("Errore nell'inserimento della firma: " . $e->getMessage());
                        }
                    }
                }
            }
        }
        
        return $pdf->Output('modulo_compilato.pdf', 'S'); 
    }
}