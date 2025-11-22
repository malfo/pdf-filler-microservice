<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessedPdf extends Model
{
    protected $fillable = [
        'code_membership',
        'onlus_code',
        'reference_id',
        'file_path',
    ];
}