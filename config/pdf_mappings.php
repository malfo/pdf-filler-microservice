<?php

return [
    'LAV' => [
        'template_path' => storage_path('app/pdf_templates/lav.pdf'),
        'char_spacing' => 0.7,
        'fields' => [
            // ========================================
            // SECTION: HEADER - MEMBERSHIP CODE & DATE
            // ========================================
            'code_membership' => ['x' => 160, 'y' => 35],
            'membership_date' => ['x' => 30, 'y' => 52],
            
            // ========================================
            // SECTION: SUPPORTER PERSONAL DATA
            // ========================================
            'first_name' => ['x' => 30, 'y' => 64],
            'last_name' => ['x' => 120, 'y' => 64],
            'birth_place' => ['x' => 30, 'y' => 72],
            'birth_province' => ['x' => 160, 'y' => 72],
            'birth_date' => ['x' => 180, 'y' => 72],
            'fiscal_code' => ['x' => 30, 'y' => 80],
            'gender_m' => ['x' => 120, 'y' => 80, 'type' => 'checkbox'],
            'gender_f' => ['x' => 125, 'y' => 80, 'type' => 'checkbox'],
            
            // Address
            'street_type' => ['x' => 30, 'y' => 88],
            'address' => ['x' => 75, 'y' => 88],
            'street_number' => ['x' => 30, 'y' => 96],
            'zip_code' => ['x' => 60, 'y' => 96],
            'city' => ['x' => 95, 'y' => 96],
            'province' => ['x' => 30, 'y' => 104],
            'mobile' => ['x' => 60, 'y' => 104],
            'email' => ['x' => 30, 'y' => 112],
            
            // Newsletter opt-out
            'no_paper' => ['x' => 25, 'y' => 118, 'type' => 'checkbox'],
            
            // Notes
            'notes' => ['x' => 30, 'y' => 128],
            
            // ========================================
            // SECTION: DISTANCE ADOPTION
            // ========================================
            'animal_name' => ['x' => 50, 'y' => 135],
            'amount' => ['x' => 120, 'y' => 135],
            
            // Frequency (checkbox)
            'frequency_monthly' => ['x' => 20, 'y' => 145, 'type' => 'checkbox'],
            'frequency_quarterly' => ['x' => 50, 'y' => 145, 'type' => 'checkbox'],
            'frequency_biannual' => ['x' => 82, 'y' => 145, 'type' => 'checkbox'],
            'frequency_annual' => ['x' => 113, 'y' => 145, 'type' => 'checkbox'],
            
            // ========================================
            // SECTION: ACCOUNT HOLDER (IF DIFFERENT)
            // ========================================
            'account_holder_first_name' => ['x' => 30, 'y' => 159],
            'account_holder_last_name' => ['x' => 120, 'y' => 159],
            'account_holder_birth_place' => ['x' => 30, 'y' => 167],
            'account_holder_birth_date' => ['x' => 180, 'y' => 167],
            'account_holder_fiscal_code' => ['x' => 30, 'y' => 175],
            
            // ========================================
            // SECTION: CREDIT CARD DATA
            // ========================================
            'card_number' => ['x' => 30, 'y' => 189],
            'card_expiry_month' => ['x' => 123, 'y' => 189],
            'card_expiry_year' => ['x' => 135, 'y' => 189],
            
            // Card type (checkbox)
            'card_visa' => ['x' => 30, 'y' => 197, 'type' => 'checkbox'],
            'card_mastercard' => ['x' => 55, 'y' => 197, 'type' => 'checkbox'],
            'card_cartasi' => ['x' => 80, 'y' => 197, 'type' => 'checkbox'],
            
            // ========================================
            // SECTION: BANK DATA
            // ========================================
            'bank_name' => ['x' => 40, 'y' => 212],
            
            // IBAN breakdown
            'iban_country' => ['x' => 30, 'y' => 220],      // IT
            'iban_check' => ['x' => 45, 'y' => 220],        // 2 digits
            'iban_cin' => ['x' => 60, 'y' => 220],          // 1 letter
            'iban_abi' => ['x' => 71, 'y' => 220],          // 5 digits
            'iban_cab' => ['x' => 101, 'y' => 220],         // 5 digits
            'iban_account' => ['x' => 131, 'y' => 220],     // 12 digits
            
            'bank_city' => ['x' => 30, 'y' => 230],
            'residence_country' => ['x' => 130, 'y' => 230],
            'bic_swift' => ['x' => 70, 'y' => 236],
            
            // ========================================
            // SECTION: QUALITY QUESTIONNAIRE
            // ========================================
            'question1_yes' => ['x' => 92, 'y' => 250, 'type' => 'checkbox'],
            'question1_no' => ['x' => 103, 'y' => 250, 'type' => 'checkbox'],
            'question2_yes' => ['x' => 190, 'y' => 250, 'type' => 'checkbox'],
            'question2_no' => ['x' => 201, 'y' => 250, 'type' => 'checkbox'],
            
            // Donation frequency (questionnaire repeat)
            'freq_monthly' => ['x' => 23, 'y' => 260, 'type' => 'checkbox'],
            'freq_quarterly' => ['x' => 42, 'y' => 260, 'type' => 'checkbox'],
            'freq_biannual' => ['x' => 66, 'y' => 260, 'type' => 'checkbox'],
            'freq_annual' => ['x' => 88, 'y' => 260, 'type' => 'checkbox'],
            'freq_amount' => ['x' => 110, 'y' => 260],
            
            // ========================================
            // SECTION: SIGNATURE
            // ========================================
            'signature' => ['x' => 50, 'y' => 282, 'type' => 'signature', 'width' => 120, 'height' => 15],
            
            // ========================================
            // SECTION: OFFICE USE (Fundraiser)
            // ========================================
            'fundraiser_name' => ['x' => 40, 'y' => 295],
            'fundraiser_id' => ['x' => 120, 'y' => 295],
            'location_number' => ['x' => 168, 'y' => 295],
            'batch_number' => ['x' => 190, 'y' => 295],
        ],
    ],
    
    // Other NGOs can be added here
    // 'TELETHON' => [ ... ],
    // 'EMERGENCY' => [ ... ],
];