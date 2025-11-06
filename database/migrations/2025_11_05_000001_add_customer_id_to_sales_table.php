<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('sales') && ! Schema::hasColumn('sales', 'customer_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->unsignedBigInteger('customer_id')->nullable()->after('user_id')->index();
                if (Schema::hasTable('customers')) {
                    $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'customer_id')) {
            Schema::table('sales', function (Blueprint $table) {
                // drop foreign first if exists
                try {
                    $table->dropForeign(['customer_id']);
                } catch (\Exception $e) {
                    // ignore if foreign does not exist
                }
                $table->dropColumn('customer_id');
            });
        }
    }
};
