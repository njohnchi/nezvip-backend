<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addFields('form_submissions');
        $this->addFields('venture_diagnostics');
    }

    public function down(): void
    {
        $this->removeFields('form_submissions');
        $this->removeFields('venture_diagnostics');
    }

    private function addFields(string $table): void
    {
        Schema::table($table, function (Blueprint $tableBlueprint) {
            $tableBlueprint->string('case_reference')->nullable();
            $tableBlueprint->boolean('operator_assisted')->default(false);

            $tableBlueprint->index('case_reference');
        });
    }

    private function removeFields(string $table): void
    {
        Schema::table($table, function (Blueprint $tableBlueprint) {
            $tableBlueprint->dropIndex(['case_reference']);
            $tableBlueprint->dropColumn(['case_reference', 'operator_assisted']);
        });
    }
};
