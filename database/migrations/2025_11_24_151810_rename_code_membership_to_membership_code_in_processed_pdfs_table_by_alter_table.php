<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('processed_pdfs', 'code_membership')) {
            // Rimuovi l'indice unique se esiste (MySQL potrebbe chiamarlo diversamente)
            try {
                DB::statement('ALTER TABLE processed_pdfs DROP INDEX processed_pdfs_code_membership_unique');
            } catch (\Exception $e) {
                // L'indice potrebbe non esistere o avere un nome diverso
            }
            
            // Rinomina la colonna
            DB::statement('ALTER TABLE processed_pdfs RENAME COLUMN code_membership TO membership_code');
            
            // Ricrea l'indice unique sul nuovo nome
            DB::statement('ALTER TABLE processed_pdfs ADD UNIQUE (membership_code)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('processed_pdfs', 'membership_code')) {
            try {
                DB::statement('ALTER TABLE processed_pdfs DROP INDEX processed_pdfs_membership_code_unique');
            } catch (\Exception $e) {
                // L'indice potrebbe non esistere
            }
            
            DB::statement('ALTER TABLE processed_pdfs RENAME COLUMN membership_code TO code_membership');
            
            DB::statement('ALTER TABLE processed_pdfs ADD UNIQUE (code_membership)');
        }
    }
};