<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_drafts', function (Blueprint $table) {
            $table->id();
            $table->string('draft_reference')->unique();
            $table->string('form_type')->index();
            $table->string('email')->nullable()->index();
            $table->string('case_reference')->nullable()->index();
            $table->json('data')->nullable();
            $table->string('current_step')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('last_saved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_drafts');
    }
};
