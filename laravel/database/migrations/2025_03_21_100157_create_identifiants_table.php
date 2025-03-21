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
        Schema::create('identifiants', function (Blueprint $table) {
            $table->id();
            $table->string('identifiant')->default('Admin');
            $table->text('mot_de_passe')->default('123Soleil-Daiboken');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identifiants');
    }
};
