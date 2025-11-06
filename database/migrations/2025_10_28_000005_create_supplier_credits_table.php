<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('supplier_credits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('supplier_id')->index();
            $table->unsignedBigInteger('stock_movement_id')->nullable()->index();
            $table->decimal('amount', 12, 2);
            $table->date('due_date')->nullable();
            $table->boolean('paid')->default(false)->index();
            $table->dateTime('paid_at')->nullable();
            $table->text('follow_up')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('supplier_credits');
    }
};
