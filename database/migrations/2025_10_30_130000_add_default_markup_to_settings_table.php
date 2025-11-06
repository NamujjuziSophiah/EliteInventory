<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (Schema::hasTable('settings') && ! Schema::hasColumn('settings', 'default_markup_percent')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->decimal('default_markup_percent', 5, 2)->default(20)->after('auto_redirect');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'default_markup_percent')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('default_markup_percent');
            });
        }
    }
};
