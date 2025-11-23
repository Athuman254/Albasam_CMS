<?php
// app/Models/BankStatementImport.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankStatementImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'account_number',
        'payment_method',
        'records_imported',
        'records_skipped',
        'import_notes',
        'imported_by',
    ];

    /**
     * Get the user who imported the statement
     */
    public function importedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}