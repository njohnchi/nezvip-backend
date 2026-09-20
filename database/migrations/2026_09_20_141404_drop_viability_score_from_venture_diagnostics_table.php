<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venture_diagnostics', function (Blueprint $table) {
            $table->dropColumn('viability_score');
        });
    }

    public function down(): void
    {
        Schema::table('venture_diagnostics', function (Blueprint $table) {
            $table->integer('viability_score')->nullable()->after('admin_notes');
        });
    }
};
