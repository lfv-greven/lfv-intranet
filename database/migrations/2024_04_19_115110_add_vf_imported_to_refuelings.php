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
        Schema::table('refuelings', function (Blueprint $table) {
            $table->dateTime('vf_exported_at')->nullable();
        });

        Schema::table('gas_stations', function (Blueprint $table) {
            $table->string('vf_articleid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refuelings', function (Blueprint $table) {
            $table->dropColumn('vf_exported_at');
        });

        Schema::table('gas_stations', function (Blueprint $table) {
            $table->dropColumn('vf_articleid');
        });
    }
};
