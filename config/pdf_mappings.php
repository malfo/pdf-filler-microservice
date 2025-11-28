<?php

return [
    // ========================================
    // TELETHON
    // ========================================
    'TELETHON' => [
        'template_path' => storage_path('app/pdf_templates/telethon.pdf'),
        'char_spacing' => 0.7,
        'fields' => [
            // COMPANY DATA (if applicable)
            'company.company_name' => ['x' => 30, 'y' => 52],
            'company.vat_number' => ['x' => 160, 'y' => 52],
            
            // SUPPORTER PERSONAL DATA
            'supporter.first_name' => ['x' => 30, 'y' => 64],
            'supporter.last_name' => ['x' => 120, 'y' => 64],
            'supporter.gender_m' => ['x' => 180, 'y' => 64, 'type' => 'checkbox'],
            'supporter.gender_f' => ['x' => 190, 'y' => 64, 'type' => 'checkbox'],
            
            'supporter.birth_place' => ['x' => 30, 'y' => 72],
            'supporter.birth_province' => ['x' => 160, 'y' => 72],
            'supporter.birth_date' => ['x' => 180, 'y' => 72],
            
            'supporter.fiscal_code' => ['x' => 30, 'y' => 80],
            'supporter.address.street_type' => ['x' => 30, 'y' => 88],
            'supporter.address.street_name' => ['x' => 60, 'y' => 88],
            'supporter.address.street_number' => ['x' => 160, 'y' => 88],
            'supporter.address.city' => ['x' => 30, 'y' => 96],
            'supporter.address.province' => ['x' => 140, 'y' => 96],
            'supporter.address.zip_code' => ['x' => 30, 'y' => 104],
            
            'supporter.contacts.phone' => ['x' => 80, 'y' => 104],
            'supporter.contacts.email' => ['x' => 30, 'y' => 112],
            'supporter.profession' => ['x' => 30, 'y' => 120],
            'supporter.notes' => ['x' => 100, 'y' => 120],
            'supporter.contacts.mobile' => ['x' => 160, 'y' => 120],
            
            // CONSENTS
            'consents.marketing' => ['x' => 30, 'y' => 136, 'type' => 'checkbox'],
            'consents.marketing_no' => ['x' => 80, 'y' => 136, 'type' => 'checkbox'],
            
            // DONATION CHOICE
            'donation.frequency_annual' => ['x' => 30, 'y' => 168, 'type' => 'checkbox'],
            'donation.amount_annual_360' => ['x' => 120, 'y' => 168, 'type' => 'checkbox'],
            'donation.amount_annual_240' => ['x' => 160, 'y' => 168, 'type' => 'checkbox'],
            'donation.amount_annual_other' => ['x' => 190, 'y' => 168],
            
            'donation.frequency_monthly' => ['x' => 30, 'y' => 176, 'type' => 'checkbox'],
            'donation.amount_monthly_30' => ['x' => 120, 'y' => 176, 'type' => 'checkbox'],
            'donation.amount_monthly_20' => ['x' => 160, 'y' => 176, 'type' => 'checkbox'],
            'donation.amount_monthly_other' => ['x' => 190, 'y' => 176],
            
            // BANK DATA
            'payment.bank.bank_name' => ['x' => 30, 'y' => 204],
            'payment.bank.iban.country' => ['x' => 30, 'y' => 220],
            'payment.bank.iban.cin' => ['x' => 45, 'y' => 220],
            'payment.bank.iban.abi' => ['x' => 60, 'y' => 220],
            'payment.bank.iban.cab' => ['x' => 90, 'y' => 220],
            'payment.bank.iban.account' => ['x' => 120, 'y' => 220],
            
            // ACCOUNT HOLDER (if different)
            'payment.account_holder.fiscal_code' => ['x' => 30, 'y' => 236],
            'payment.account_holder.first_name' => ['x' => 30, 'y' => 244],
            'payment.account_holder.last_name' => ['x' => 120, 'y' => 244],
            
            // COMPANY AUTHORIZED SIGNATORY
            'company.authorized_signatory.first_name' => ['x' => 30, 'y' => 252],
            'company.authorized_signatory.last_name' => ['x' => 120, 'y' => 252],
            
            // CREDIT CARD
            'payment.credit_card.card_number' => ['x' => 30, 'y' => 268],
            'payment.credit_card.expiry_month' => ['x' => 140, 'y' => 268],
            'payment.credit_card.expiry_year' => ['x' => 155, 'y' => 268],
            'payment.credit_card.card_cartasi' => ['x' => 30, 'y' => 276, 'type' => 'checkbox'],
            'payment.credit_card.card_mastercard' => ['x' => 70, 'y' => 276, 'type' => 'checkbox'],
            'payment.credit_card.card_amex' => ['x' => 110, 'y' => 276, 'type' => 'checkbox'],
            'payment.credit_card.card_visa' => ['x' => 150, 'y' => 276, 'type' => 'checkbox'],
            
            // AUTHORIZATION
            'authorization.signature_date' => ['x' => 30, 'y' => 292],
            'authorization.signature' => ['x' => 80, 'y' => 290, 'type' => 'signature', 'width' => 100, 'height' => 15],
            
            // FUNDRAISER
            'fundraiser.name' => ['x' => 30, 'y' => 312],
            'fundraiser.id' => ['x' => 90, 'y' => 312],
            'fundraiser.location' => ['x' => 130, 'y' => 312],
            'fundraiser.location_code' => ['x' => 170, 'y' => 312],
            'fundraiser.batch_number' => ['x' => 190, 'y' => 312],
        ],
    ],

    // ========================================
    // LAV
    // ========================================
    'LAV' => [
        'template_path' => storage_path('app/pdf_templates/LAV.pdf'),
        'char_spacing' => 0.7,
        'fields' => [
            // HEADER
            'membership_code' => ['x' => 160, 'y' => 35],
            'membership_date' => ['x' => 30, 'y' => 52],
            
            // SUPPORTER DATA
            'supporter.first_name' => ['x' => 30, 'y' => 64],
            'supporter.last_name' => ['x' => 120, 'y' => 64],
            'supporter.birth_place' => ['x' => 30, 'y' => 72],
            'supporter.birth_province' => ['x' => 160, 'y' => 72],
            'supporter.birth_date' => ['x' => 180, 'y' => 72],
            'supporter.fiscal_code' => ['x' => 30, 'y' => 80],
            'supporter.gender_m' => ['x' => 120, 'y' => 80, 'type' => 'checkbox'],
            'supporter.gender_f' => ['x' => 125, 'y' => 80, 'type' => 'checkbox'],
            
            'supporter.address.street_type' => ['x' => 30, 'y' => 88],
            'supporter.address.street_name' => ['x' => 75, 'y' => 88],
            'supporter.address.street_number' => ['x' => 30, 'y' => 96],
            'supporter.address.zip_code' => ['x' => 60, 'y' => 96],
            'supporter.address.city' => ['x' => 95, 'y' => 96],
            'supporter.address.province' => ['x' => 30, 'y' => 104],
            'supporter.contacts.mobile' => ['x' => 60, 'y' => 104],
            'supporter.contacts.email' => ['x' => 30, 'y' => 112],
            
            'consents.paper_communications' => ['x' => 25, 'y' => 118, 'type' => 'checkbox'],
            'supporter.notes' => ['x' => 30, 'y' => 128],
            
            // DISTANCE ADOPTION
            'ngo_specific.lav.animal_name' => ['x' => 50, 'y' => 135],
            'donation.amount' => ['x' => 120, 'y' => 135],
            'donation.frequency_monthly' => ['x' => 20, 'y' => 145, 'type' => 'checkbox'],
            'donation.frequency_quarterly' => ['x' => 50, 'y' => 145, 'type' => 'checkbox'],
            'donation.frequency_biannual' => ['x' => 82, 'y' => 145, 'type' => 'checkbox'],
            'donation.frequency_annual' => ['x' => 113, 'y' => 145, 'type' => 'checkbox'],
            
            // ACCOUNT HOLDER (if different)
            'payment.account_holder.first_name' => ['x' => 30, 'y' => 159],
            'payment.account_holder.last_name' => ['x' => 120, 'y' => 159],
            'payment.account_holder.birth_place' => ['x' => 30, 'y' => 167],
            'payment.account_holder.birth_date' => ['x' => 180, 'y' => 167],
            'payment.account_holder.fiscal_code' => ['x' => 30, 'y' => 175],
            
            // CREDIT CARD
            'payment.credit_card.card_number' => ['x' => 30, 'y' => 189],
            'payment.credit_card.expiry_month' => ['x' => 123, 'y' => 189],
            'payment.credit_card.expiry_year' => ['x' => 135, 'y' => 189],
            'payment.credit_card.card_visa' => ['x' => 30, 'y' => 197, 'type' => 'checkbox'],
            'payment.credit_card.card_mastercard' => ['x' => 55, 'y' => 197, 'type' => 'checkbox'],
            'payment.credit_card.card_cartasi' => ['x' => 80, 'y' => 197, 'type' => 'checkbox'],
            
            // BANK DATA
            'payment.bank.bank_name' => ['x' => 40, 'y' => 212],
            'payment.bank.iban.country' => ['x' => 30, 'y' => 220],
            'payment.bank.iban.check' => ['x' => 45, 'y' => 220],
            'payment.bank.iban.cin' => ['x' => 60, 'y' => 220],
            'payment.bank.iban.abi' => ['x' => 71, 'y' => 220],
            'payment.bank.iban.cab' => ['x' => 101, 'y' => 220],
            'payment.bank.iban.account' => ['x' => 131, 'y' => 220],
            'payment.bank.bank_city' => ['x' => 30, 'y' => 230],
            'payment.bank.residence_country' => ['x' => 130, 'y' => 230],
            'payment.bank.bic_swift' => ['x' => 70, 'y' => 236],
            
            // QUALITY QUESTIONS
            'ngo_specific.lav.quality_questions.q1_yes' => ['x' => 92, 'y' => 250, 'type' => 'checkbox'],
            'ngo_specific.lav.quality_questions.q1_no' => ['x' => 103, 'y' => 250, 'type' => 'checkbox'],
            'ngo_specific.lav.quality_questions.q2_yes' => ['x' => 190, 'y' => 250, 'type' => 'checkbox'],
            'ngo_specific.lav.quality_questions.q2_no' => ['x' => 201, 'y' => 250, 'type' => 'checkbox'],
            'donation.frequency_conf_monthly' => ['x' => 23, 'y' => 260, 'type' => 'checkbox'],
            'donation.frequency_conf_quarterly' => ['x' => 42, 'y' => 260, 'type' => 'checkbox'],
            'donation.frequency_conf_biannual' => ['x' => 66, 'y' => 260, 'type' => 'checkbox'],
            'donation.frequency_conf_annual' => ['x' => 88, 'y' => 260, 'type' => 'checkbox'],
            'donation.amount_confirmation' => ['x' => 110, 'y' => 260],
            
            // SIGNATURE
            'authorization.signature' => ['x' => 50, 'y' => 282, 'type' => 'signature', 'width' => 120, 'height' => 15],
            
            // FUNDRAISER
            'fundraiser.name' => ['x' => 40, 'y' => 295],
            'fundraiser.id' => ['x' => 120, 'y' => 295],
            'fundraiser.location_code' => ['x' => 168, 'y' => 295],
            'fundraiser.batch_number' => ['x' => 190, 'y' => 295],
        ],
    ],

    // ========================================
    // EMERGENCY
    // ========================================
    'EMERGENCY' => [
        'template_path' => storage_path('app/pdf_templates/EMERGENCY.pdf'),
        'char_spacing' => 0.5,
        'fields' => [
            // HEADER
            'membership_code' => ['x' => 172, 'y' => 47],
            'membership_date' => ['x' => 30, 'y' => 52],
            // SUPPORTER DATA
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
            
            // DONATION CHOICE
            'donation.amount_annual_360' => ['x' => 35, 'y' => 142, 'type' => 'checkbox'],
            'donation.amount_annual_300' => ['x' => 70, 'y' => 142, 'type' => 'checkbox'],
            'donation.amount_annual_240' => ['x' => 105, 'y' => 142, 'type' => 'checkbox'],
            'donation.amount_annual_other' => ['x' => 140, 'y' => 142, 'type' => 'checkbox'],
            'donation.amount_annual_other_value' => ['x' => 140, 'y' => 150],
            'donation.amount_monthly_30' => ['x' => 35, 'y' => 150, 'type' => 'checkbox'],
            'donation.amount_monthly_25' => ['x' => 70, 'y' => 150, 'type' => 'checkbox'],
            'donation.amount_monthly_20' => ['x' => 105, 'y' => 150, 'type' => 'checkbox'],
            'donation.amount_monthly_other' => ['x' => 140, 'y' => 150, 'type' => 'checkbox'],
            'donation.amount_monthly_other_value' => ['x' => 140, 'y' => 150],
            // BANK DATA
            'payment.bank.bank_name' => ['x' => 40, 'y' => 172],
            'payment.bank.bank_address' => ['x' => 30, 'y' => 180],
            'payment.bank.bank_agency_number' => ['x' => 150, 'y' => 172],
            'payment.bank.bank_city' => ['x' => 150, 'y' => 180],
            'payment.bank.iban.country' => ['x' => 30, 'y' => 192],
            'payment.bank.iban.check' => ['x' => 45, 'y' => 192],
            'payment.bank.iban.cin' => ['x' => 60, 'y' => 192],
            'payment.bank.iban.abi' => ['x' => 71, 'y' => 192],
            'payment.bank.iban.cab' => ['x' => 101, 'y' => 192],
            'payment.bank.iban.account' => ['x' => 131, 'y' => 192],
            
            // CREDIT CARD
            'payment.credit_card.card_visa' => ['x' => 45, 'y' => 212, 'type' => 'checkbox'],
            'payment.credit_card.card_mastercard' => ['x' => 75, 'y' => 212, 'type' => 'checkbox'],
            'payment.credit_card.card_amex' => ['x' => 115, 'y' => 212, 'type' => 'checkbox'],
            'payment.credit_card.card_holder' => ['x' => 50, 'y' => 222],
            'payment.credit_card.card_number' => ['x' => 30, 'y' => 230],
            'payment.credit_card.expiry_month' => ['x' => 140, 'y' => 230],
            'payment.credit_card.expiry_year' => ['x' => 155, 'y' => 230],
            
            // AUTHORIZATION
            'authorization.signature_date' => ['x' => 30, 'y' => 250],
            'authorization.signature' => ['x' => 100, 'y' => 248, 'type' => 'signature', 'width' => 90, 'height' => 15],
            
            // CONFIRMATION QUESTIONS
            'ngo_specific.emergency.confirmation_questions.q1_yes' => ['x' => 178, 'y' => 272, 'type' => 'checkbox'],
            'ngo_specific.emergency.confirmation_questions.q1_no' => ['x' => 192, 'y' => 272, 'type' => 'checkbox'],
            'ngo_specific.emergency.confirmation_questions.q2_yes' => ['x' => 178, 'y' => 280, 'type' => 'checkbox'],
            'ngo_specific.emergency.confirmation_questions.q2_no' => ['x' => 192, 'y' => 280, 'type' => 'checkbox'],
            'ngo_specific.emergency.confirmation_questions.q3_yes' => ['x' => 178, 'y' => 288, 'type' => 'checkbox'],
            'ngo_specific.emergency.confirmation_questions.q3_no' => ['x' => 192, 'y' => 288, 'type' => 'checkbox'],
            'donation.amount_confirmation' => ['x' => 80, 'y' => 294],
            'donation.frequency_annual' => ['x' => 120, 'y' => 294, 'type' => 'checkbox'],
            'donation.frequency_monthly' => ['x' => 155, 'y' => 294, 'type' => 'checkbox'],
            
            // FUNDRAISER
            'fundraiser.name' => ['x' => 45, 'y' => 305],
            'fundraiser.id' => ['x' => 145, 'y' => 305],
            'fundraiser.location' => ['x' => 45, 'y' => 313],
            'fundraiser.event_date' => ['x' => 145, 'y' => 313],
        ],
    ],

    // ========================================
    // IEO
    // ========================================
    'IEO' => [
        'template_path' => storage_path('app/pdf_templates/ieo.pdf'),
        'char_spacing' => 0.7,
        'fields' => [
            // SUPPORTER DATA
            'supporter.first_name' => ['x' => 30, 'y' => 64],
            'supporter.last_name' => ['x' => 120, 'y' => 64],
            'supporter.birth_date' => ['x' => 30, 'y' => 72],
            'supporter.birth_place' => ['x' => 90, 'y' => 72],
            'supporter.birth_province' => ['x' => 170, 'y' => 72],
            'supporter.fiscal_code' => ['x' => 30, 'y' => 80],
            'supporter.profession' => ['x' => 120, 'y' => 80],
            'supporter.address.street_name' => ['x' => 30, 'y' => 88],
            'supporter.address.city' => ['x' => 120, 'y' => 88],
            'supporter.address.province' => ['x' => 170, 'y' => 88],
            'supporter.address.zip_code' => ['x' => 185, 'y' => 88],
            'supporter.contacts.mobile' => ['x' => 30, 'y' => 96],
            'supporter.contacts.phone' => ['x' => 80, 'y' => 96],
            'supporter.contacts.email' => ['x' => 130, 'y' => 96],
            
            // DONATION CHOICE
            'donation.amount_monthly' => ['x' => 60, 'y' => 116],
            'donation.amount_biannual' => ['x' => 60, 'y' => 124],
            'donation.amount_annual' => ['x' => 60, 'y' => 132],
            
            // BANK DATA
            'payment.bank.bank_name' => ['x' => 30, 'y' => 156],
            'payment.bank.bank_agency_number' => ['x' => 30, 'y' => 164],
            'payment.bank.bank_address' => ['x' => 80, 'y' => 164],
            'payment.bank.bank_zip_code' => ['x' => 170, 'y' => 164],
            'payment.bank.bank_city' => ['x' => 30, 'y' => 172],
            'payment.bank.bank_province' => ['x' => 170, 'y' => 172],
            'payment.bank.iban.cin' => ['x' => 35, 'y' => 184],
            'payment.bank.iban.abi' => ['x' => 50, 'y' => 184],
            'payment.bank.iban.cab' => ['x' => 80, 'y' => 184],
            'payment.bank.iban.account' => ['x' => 110, 'y' => 184],
            'payment.account_holder.first_name' => ['x' => 30, 'y' => 196],
            'payment.co_holder.first_name' => ['x' => 30, 'y' => 204],
            
            // CREDIT CARD
            'payment.credit_card.card_cartasi' => ['x' => 30, 'y' => 220, 'type' => 'checkbox'],
            'payment.credit_card.card_mastercard' => ['x' => 60, 'y' => 220, 'type' => 'checkbox'],
            'payment.credit_card.card_visa' => ['x' => 100, 'y' => 220, 'type' => 'checkbox'],
            'payment.credit_card.card_number' => ['x' => 30, 'y' => 232],
            'payment.credit_card.expiry_month' => ['x' => 130, 'y' => 232],
            'payment.credit_card.expiry_year' => ['x' => 145, 'y' => 232],
            'payment.credit_card.card_holder' => ['x' => 30, 'y' => 244],
            
            // AUTHORIZATION
            'authorization.signature_date' => ['x' => 30, 'y' => 264],
            'authorization.signature' => ['x' => 80, 'y' => 262, 'type' => 'signature', 'width' => 100, 'height' => 15],
            
            // CONFIRMATION
            'ngo_specific.ieo.confirmation_question_yes' => ['x' => 160, 'y' => 284, 'type' => 'checkbox'],
            'ngo_specific.ieo.confirmation_question_no' => ['x' => 180, 'y' => 284, 'type' => 'checkbox'],
        ],
    ],

    // ========================================
    // GEMELLI
    // ========================================
    'GEMELLI' => [
        'template_path' => storage_path('app/pdf_templates/gemelli.pdf'),
        'char_spacing' => 0.7,
        'fields' => [
            // SUPPORTER DATA
            'supporter.first_name' => ['x' => 30, 'y' => 64],
            'supporter.last_name' => ['x' => 120, 'y' => 64],
            'supporter.birth_place' => ['x' => 30, 'y' => 72],
            'supporter.birth_date' => ['x' => 120, 'y' => 72],
            'supporter.fiscal_code' => ['x' => 30, 'y' => 80],
            'supporter.address.city' => ['x' => 30, 'y' => 88],
            'supporter.address.province' => ['x' => 120, 'y' => 88],
            'supporter.address.street_name' => ['x' => 30, 'y' => 96],
            'supporter.address.street_number' => ['x' => 140, 'y' => 96],
            'supporter.address.zip_code' => ['x' => 160, 'y' => 96],
            'supporter.contacts.phone' => ['x' => 30, 'y' => 104],
            'supporter.contacts.mobile' => ['x' => 30, 'y' => 104],
            'supporter.contacts.email' => ['x' => 100, 'y' => 104],
            
            // DONATION CHOICE
            'donation.amount_monthly' => ['x' => 50, 'y' => 128],
            'donation.amount_biannual' => ['x' => 50, 'y' => 136],
            'donation.amount_annual' => ['x' => 50, 'y' => 144],
            
            // BANK DATA
            'payment.bank.bank_name' => ['x' => 30, 'y' => 168],
            'payment.bank.iban.cin' => ['x' => 35, 'y' => 180],
            'payment.bank.iban.abi' => ['x' => 50, 'y' => 180],
            'payment.bank.iban.cab' => ['x' => 80, 'y' => 180],
            'payment.bank.iban.account' => ['x' => 110, 'y' => 180],
            'payment.account_holder.first_name' => ['x' => 30, 'y' => 192],
            'payment.co_holder.first_name' => ['x' => 30, 'y' => 200],
            
            // CREDIT CARD
            'payment.credit_card.card_visa' => ['x' => 40, 'y' => 216, 'type' => 'checkbox'],
            'payment.credit_card.card_mastercard' => ['x' => 70, 'y' => 216, 'type' => 'checkbox'],
            'payment.credit_card.card_amex' => ['x' => 110, 'y' => 216, 'type' => 'checkbox'],
            'payment.credit_card.card_number' => ['x' => 30, 'y' => 228],
            'payment.credit_card.expiry_month' => ['x' => 140, 'y' => 228],
            'payment.credit_card.expiry_year' => ['x' => 155, 'y' => 228],
            
            // AUTHORIZATION
            'authorization.signature_date' => ['x' => 30, 'y' => 248],
            'authorization.signature' => ['x' => 80, 'y' => 246, 'type' => 'signature', 'width' => 100, 'height' => 15],
            
            // CONSENTS
            'consents.marketing' => ['x' => 30, 'y' => 268, 'type' => 'checkbox'],
            'consents.fiscal_data_transmission' => ['x' => 30, 'y' => 276, 'type' => 'checkbox'],
            
            // FUNDRAISER
            'fundraiser.name' => ['x' => 30, 'y' => 292],
            'fundraiser.location_code' => ['x' => 120, 'y' => 292],
            'fundraiser.batch_number' => ['x' => 160, 'y' => 292],
        ],
    ]
];