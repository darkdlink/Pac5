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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Cliente
            $table->unsignedBigInteger('esthetician_id'); // Esteticista (também é um usuário)
            $table->unsignedBigInteger('service_id');
            $table->dateTime('appointment_datetime');
            $table->integer('duration_minutes');
            $table->enum('status', ['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show'])
                 ->default('scheduled');
            $table->text('notes')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->unsignedBigInteger('order_id')->nullable(); // Caso o pagamento seja processado via pedido
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')
                  ->onDelete('cascade');
            $table->foreign('esthetician_id')->references('id')->on('users')
                  ->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')
                  ->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointments');
    }
};
