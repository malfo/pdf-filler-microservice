<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processed_pdfs', function (Blueprint $table) {
            if (!Schema::hasColumn('processed_pdfs', 'code_membership')) {
                $table->string('code_membership')->unique()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('processed_pdfs', function (Blueprint $table) {
            if (Schema::hasColumn('processed_pdfs', 'code_membership')) {
                $table->dropColumn('code_membership');
            }
        });
    }
};