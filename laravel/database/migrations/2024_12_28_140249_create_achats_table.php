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
        Schema::create('achats', function (Blueprint $table) {
            $table->id();

            // Statut de l'achat
            $table->enum('statut', array_column(StatutEnum::cases(), 'value'))->default(StatutEnum::Pret->value);

            // Relations
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');

            // Paiement
            $table->boolean('est_paye')->default(false);
            $table->enum('moyen_paiement', ['carte', 'espece', 'credit', 'autre'])->nullable();
            $table->decimal('total', 8, 2)->nullable();

            // Crédits
            $table->integer('nombre_credits')->default(0);

            // Formule
            $table->foreignId('formule_id')->nullable()->constrained()->onDelete('set null');

            // Heures ou jours achetés
            $table->integer('nombre_heures')->nullable();
            $table->integer('nombre_jours')->nullable();

            $table->timestamps();
        });

        // Table pivot : Produits dans un achat
        Schema::create('achat_produit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('achat_id')->constrained()->onDelete('cascade');
            $table->foreignId('produit_id')->constrained()->onDelete('cascade');
            $table->integer('quantite')->default(1);
            $table->timestamps();
        });

        // Table pivot : Services dans un achat
        Schema::create('achat_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('achat_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achat_service');
        Schema::dropIfExists('achat_produit');
        Schema::dropIfExists('achats');
    }
};
