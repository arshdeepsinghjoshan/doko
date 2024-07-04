<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('kycs');
        Schema::create('kycs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->nullable();
            $table->string('email', 128)->nullable();
            $table->string('contact_number', 128)->nullable();
            $table->integer('type_id')->nullable();
            $table->integer('state_id')->default(0);
            $table->string('national_id')->nullable();
            $table->string('front_image')->nullable();
            $table->string('back_image')->nullable();
            $table->string('selfie_image')->nullable();
            $table->string('video')->nullable();
            $table->string('created_by_id')->nullable();
            $table->timestamps();
            $table->index('contact_number');
            $table->index('email');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kycs');
    }
};
