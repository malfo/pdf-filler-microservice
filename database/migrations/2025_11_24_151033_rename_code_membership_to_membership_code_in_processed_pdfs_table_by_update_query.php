<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('processed_pdfs', function (Blueprint $table) {
            $table->string('membership_code')->unique()->after('id');
        });
        
        // Copia i dati
        DB::statement('UPDATE processed_pdfs SET membership_code = code_membership');
        
        // Rimuovi la vecchia colonna
        Schema::table('processed_pdfs', function (Blueprint $table) {
            $table->dropColumn('code_membership');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processed_pdfs', function (Blueprint $table) {
            $table->string('code_membership')->unique()->after('id');
        });
        
        DB::statement('UPDATE processed_pdfs SET code_membership = membership_code');
        
        Schema::table('processed_pdfs', function (Blueprint $table) {
            $table->dropColumn('membership_code');
        });
    }
};
