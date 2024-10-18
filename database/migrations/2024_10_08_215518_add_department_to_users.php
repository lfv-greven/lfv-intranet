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
            $table->foreignIdFor(\App\Models\Department::class)
                ->nullable()
                ->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->datetime('department_joined_at')->nullable();
            $table->text('department_note')->nullable();
            $table->boolean('department_lead_interest')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignIdFor(\App\Models\Department::class);
            $table->dropColumn(['department_joined_at', 'department_note', 'department_lead_interest']);
        });
    }
};
