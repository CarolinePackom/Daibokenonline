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
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('image')->nullable();
            $table->decimal('prix', 8, 2);
            $table->boolean('en_vente')->default(true);
            $table->text('description')->nullable();
            $table->boolean('est_commandable_sur_le_logiciel')->default(true);

            // Gestion du stock
            $table->integer('quantite_stock')->default(0);
            $table->integer('seuil_quantite_alerte')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
