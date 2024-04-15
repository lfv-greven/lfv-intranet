<?php

use App\Models\Aircraft;
use App\Models\GasStation;
use App\Models\User;
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
        Schema::create('refuelings', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(GasStation::class)->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignIdFor(Aircraft::class)->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();

            $table->date('date')->useCurrent();
            $table->string('type');
            $table->string('buyer_name');
            $table->string('buyer_registration')->nullable();
            $table->integer('counter_reading');
            $table->integer('amount');
            $table->text('comment')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refuelings');
    }
};
