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
        Schema::create('careers', function (Blueprint $table) {
            $table->id();
            $table->string('venture_name');
            $table->string('role_title');
            $table->string('location'); // e.g. "Lagos, Nigeria" or "Remote"
            $table->string('engagement_type'); // e.g. "Full-time", "Contract", "Retainer"
            $table->text('venture_context');
            $table->text('responsibilities');
            $table->text('execution_expectations');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('careers');
    }
};
