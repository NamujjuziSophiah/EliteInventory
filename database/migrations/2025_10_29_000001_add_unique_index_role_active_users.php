<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // unique constraint on (role, deleted_at) to allow reusing a role after soft-delete
            if (! Schema::hasColumn('users', 'role')) {
                return;
            }
            $table->unique(['role', 'deleted_at'], 'users_role_deleted_at_unique');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_role_deleted_at_unique');
        });
    }
};
