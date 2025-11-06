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
        if (! Schema::hasTable('sale_payments')) {
            Schema::create('sale_payments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('sale_id')->index();
                $table->string('method')->index();
                $table->decimal('amount', 12, 2)->default(0);
                $table->timestamps();

                // foreign key if sales table exists during migrate
                if (Schema::hasTable('sales')) {
                    $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sale_payments');
    }
};
