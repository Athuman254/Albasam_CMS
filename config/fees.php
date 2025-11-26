<?php

return [
   
    'min_transfer_amount' => env('MIN_TRANSFER_AMOUNT', 1),
    'max_transfer_amount' => env('MAX_TRANSFER_AMOUNT', 100000),

   
    'valid_transfer_reasons' => [
        'fee_adjustment' => 'Fee Adjustment',
        'scholarship_allocation' => 'Scholarship Allocation',
        'bursary_transfer' => 'Bursary Transfer',
        'sponsorship_adjustment' => 'Sponsorship Adjustment',
        'payment_correction' => 'Payment Correction',
        'family_discount' => 'Family Discount',
        'sibling_transfer' => 'Sibling Transfer',
        'staff_discount' => 'Staff Discount',
        'promotional_offer' => 'Promotional Offer',
        'late_payment_waiver' => 'Late Payment Waiver',
        'overpayment_refund' => 'Overpayment Refund',
        'financial_aid' => 'Financial Aid',
        'sports_scholarship' => 'Sports Scholarship',
        'academic_award' => 'Academic Award',
        'other' => 'Other (Requires Approval)'
    ],

   
    'require_reason_approval' => [
        'other' => true, 
    ],

  
    'allow_reversals' => env('ALLOW_TRANSFER_REVERSALS', false),
    'max_daily_transfers' => env('MAX_DAILY_TRANSFERS', 10),
    'max_daily_amount' => env('MAX_DAILY_AMOUNT', 500000),

  
    'currency' => env('FEE_CURRENCY', 'KES'),
    'currency_symbol' => env('FEE_CURRENCY_SYMBOL', 'KSh'),
    'decimal_places' => env('FEE_DECIMAL_PLACES', 2),
];