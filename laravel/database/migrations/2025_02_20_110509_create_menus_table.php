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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->enum('statut', ['preparation', 'servi', 'termine', 'annule'])->default('preparation');
            $table->decimal('credit', 8, 2)->nullable();
            $table->text('note')->nullable();
            $table->decimal('prix', 8, 2);
            $table->decimal('prix_total', 8, 2);
            $table->foreignId('sauce_id')->constrained('sauces')->onDelete('cascade');
            $table->foreignId('accompagnement_id')->constrained('accompagnements')->onDelete('cascade');
            $table->foreignId('taille_id')->constrained('tailles')->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('cascade');
            $table->foreignId('table_id')->constrained('tables')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
