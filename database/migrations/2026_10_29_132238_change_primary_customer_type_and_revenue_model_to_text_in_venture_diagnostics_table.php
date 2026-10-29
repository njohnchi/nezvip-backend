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
        Schema::table('venture_diagnostics', function (Blueprint $table) {
            $table->text('primary_customer_type')->nullable(false)->change();
            $table->text('revenue_model')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venture_diagnostics', function (Blueprint $table) {
            $table->string('primary_customer_type', 255)->nullable(false)->change();
            $table->string('revenue_model', 255)->nullable(false)->change();
        });
    }
};
