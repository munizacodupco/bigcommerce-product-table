<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTrialExpiredFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->tinyInteger('trial')->default(1)->after('owner_email');
            $table->tinyInteger('active')->default(1)->after('trial');
            $table->timestamp('expires_at')->after('active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('trial');
            $table->dropColumn('active');
            $table->dropColumn('expires_at');
        });
    }
}
