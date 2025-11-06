<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (Schema::hasTable('sales') && ! Schema::hasColumn('sales', 'discount')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->decimal('discount', 12, 2)->default(0)->after('status');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'discount')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('discount');
            });
        }
    }
};
