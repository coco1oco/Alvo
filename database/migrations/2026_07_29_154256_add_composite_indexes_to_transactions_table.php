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
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['user_id', 'date'], 'idx_transactions_user_date');
            $table->index(['user_id', 'type', 'date'], 'idx_transactions_user_type_date');
            $table->index(['user_id', 'category_id', 'date'], 'idx_transactions_user_cat_date');
            $table->index(['user_id', 'account_id'], 'idx_transactions_user_account');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_transactions_user_date');
            $table->dropIndex('idx_transactions_user_type_date');
            $table->dropIndex('idx_transactions_user_cat_date');
            $table->dropIndex('idx_transactions_user_account');
        });
    }
};
