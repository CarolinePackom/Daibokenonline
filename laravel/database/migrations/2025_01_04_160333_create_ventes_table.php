<?php

use App\Enums\StatutEnum;
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
        Schema::create('ventes', function (Blueprint $table) {
            $table->id();

            // Statut de la vente
            $table->enum('statut', array_column(StatutEnum::cases(), 'value'))->default(StatutEnum::Pret->value);

            // Relations
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');

            // Paiement
            $table->enum('moyen_paiement', ['carte', 'espece', 'credit', 'autre'])->nullable();

            // Crédits
            $table->decimal('nombre_credits', 10, 2)->default(0);

            // Formule
            $table->foreignId('formule_id')->nullable()->constrained()->onDelete('set null');

            // Heures ou jours achetés
            $table->integer('nombre_heures')->nullable();
            $table->integer('nombre_jours')->nullable();

            // Total
            $table->decimal('total', 10, 2);

            $table->timestamps();
        });

        Schema::create('vente_produit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vente_id')->constrained()->onDelete('cascade');
            $table->foreignId('produit_id')->constrained()->onDelete('cascade');
            $table->integer('quantite')->default(1);
            $table->timestamps();
        });

        Schema::create('vente_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vente_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vente_service');
        Schema::dropIfExists('vente_produit');
        Schema::dropIfExists('ventes');
    }
};
