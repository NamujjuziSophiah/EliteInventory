<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (Schema::hasTable('settings') && ! Schema::hasColumn('settings', 'auto_redirect')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->boolean('auto_redirect')->default(false)->after('currency');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'auto_redirect')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('auto_redirect');
            });
        }
    }
};
