<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payment_intents', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('load_id')->index();
            $t->string('provider')->default('priority');
            $t->string('provider_ref'); // Passport intent id
            $t->enum('method',['card','ach']);
            $t->enum('status',['created','authorized','captured','released','refunded','chargeback'])->default('created');
            $t->integer('amount_cents');
            $t->string('checkout_url')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('payment_intents'); }
};
