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
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('insights_subscribed_at')->nullable()->after('email_verified_at');
            $table->timestamp('insights_subscription_expires_at')->nullable()->after('insights_subscribed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'insights_subscribed_at',
                'insights_subscription_expires_at',
            ]);
        });
    }
};
