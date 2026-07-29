<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('description');
            $table->boolean('is_reimbursable')->default(false)->after('notes');
            $table->string('attachment_path')->nullable()->after('is_reimbursable');
            $table->json('tags')->nullable()->after('attachment_path');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['notes', 'is_reimbursable', 'attachment_path', 'tags']);
        });
    }
};
