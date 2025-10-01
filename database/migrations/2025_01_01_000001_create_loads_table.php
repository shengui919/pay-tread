<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('loads', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('ref')->unique();
            $t->foreignId('user_id')->nullable();
            $t->unsignedBigInteger('org_id')->nullable();
            $t->unsignedBigInteger('carrier_id')->nullable();
            $t->integer('amount_cents');
            $t->enum('status',['draft','open','paid','released','completed'])->default('open');
            $t->enum('pod_method',['SIGNED_BOL','PHOTO_ONLY'])->default('SIGNED_BOL');
            $t->decimal('delivery_lat',10,7)->nullable();
            $t->decimal('delivery_lng',10,7)->nullable();
            $t->integer('geofence_radius_m')->nullable();
            $t->string('bol_path')->nullable(); // S3 key
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('loads'); }
};
