<?php
// config/pdf_mappings.php

return [
    'LAV' => [
        'template_path' => storage_path('app/pdf_templates/lav.pdf'),
        'fields' => [
            // DATI PERSONALI
            'nome' => ['x' => 30, 'y' => 63],
            'cognome' => ['x' => 110, 'y' => 57],
            'luogo_nascita' => ['x' => 25, 'y' => 67],
            'data_nascita' => ['x' => 135, 'y' => 67],
            'codice_fiscale' => ['x' => 165, 'y' => 67],
            'indirizzo' => ['x' => 50, 'y' => 87],
            'cap' => ['x' => 25, 'y' => 97],
            'citta' => ['x' => 55, 'y' => 97],
            'email' => ['x' => 80, 'y' => 117],

            // SEZIONE DONAZIONE (CHECKBOX)
            'check_mensile' => ['x' => 23, 'y' => 170, 'type' => 'checkbox'],
            // ... (altre checkbox)

            // DATI BANCARI
            'iban' => ['x' => 25, 'y' => 238],

            // FIRMA (IMPORTANTE: richiede un'immagine Base64)
            'firma_cc' => ['x' => 100, 'y' => 300, 'type' => 'image', 'width' => 50, 'height' => 10],
        ],
    ],
    // 'TELETHON' => [ ... altra mappatura ... ]
];