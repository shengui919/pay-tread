<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pods', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('load_id')->unique();
            $t->enum('type',['SIGNED_BOL','PHOTO_ONLY']);
            $t->enum('status',['submitted','verified','rejected'])->default('submitted');
            $t->string('bol_path')->nullable();
            $t->string('signed_bol_path')->nullable();
            $t->string('signer_name')->nullable();
            $t->string('signer_role')->nullable();
            $t->string('signature_png_path')->nullable();
            $t->string('signature_hash')->nullable();
            $t->decimal('lat',10,7)->nullable();
            $t->decimal('lng',10,7)->nullable();
            $t->integer('accuracy_m')->nullable();
            $t->integer('geofence_radius_m')->nullable();
            $t->timestamp('submitted_at')->nullable();
            $t->timestamp('verified_at')->nullable();
            $t->string('receiver_email')->nullable();
            $t->string('receiver_phone_e164')->nullable();
            $t->unsignedInteger('share_count')->default(0);
            $t->timestamp('last_shared_at')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('pods'); }
};
