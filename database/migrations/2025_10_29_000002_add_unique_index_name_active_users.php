<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'name')) {
                return;
            }
            // unique constraint on (name, deleted_at) to prevent reusing same name across active accounts
            $table->unique(['name', 'deleted_at'], 'users_name_deleted_at_unique');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_name_deleted_at_unique');
        });
    }
};
