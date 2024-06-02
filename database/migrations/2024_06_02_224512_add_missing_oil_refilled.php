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
        Schema::table('oil_logs', function (Blueprint $table) {
            $table->double('oil_refilled')->default(0)->after('oil_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oil_logs', function (Blueprint $table) {
            $table->dropColumn('oil_refilled');
        });
    }
};
