<?php

// database/migrations/*_create_processed_pdfs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processed_pdfs', function (Blueprint $table) {
            $table->id();
            $table->string('code_membership')->unique();
            $table->string('onlus_code'); // Es. 'LAV'
            $table->string('reference_id')->nullable(); // ID univoco dal chiamante API
            $table->string('file_path'); // Percorso del PDF salvato
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processed_pdfs');
    }
};