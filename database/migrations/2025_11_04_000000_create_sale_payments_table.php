<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('sale_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sale_id')->index();
            $table->string('method', 50)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();

            // foreign key is optional depending on project conventions
            // $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sale_payments');
    }
};
