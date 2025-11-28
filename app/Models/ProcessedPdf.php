<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessedPdf extends Model
{
    protected $fillable = [
        'membership_code',
        'onlus_code',
        'reference_id',
        'file_path',
    ];
}