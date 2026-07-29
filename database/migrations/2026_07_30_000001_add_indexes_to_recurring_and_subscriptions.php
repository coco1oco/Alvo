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
        Schema::table('recurring_transactions', function (Blueprint $table) {
            $table->index(['is_active', 'next_due_date'], 'idx_recurring_active_next_due');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->index(['is_active', 'next_renewal_date'], 'idx_subs_active_next_renewal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recurring_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_recurring_active_next_due');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex('idx_subs_active_next_renewal');
        });
    }
};
