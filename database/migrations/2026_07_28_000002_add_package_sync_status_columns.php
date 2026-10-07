<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPackageSyncStatusColumns extends Migration
{
    public function up()
    {
        Schema::table('packages', function (Blueprint $table) {
            if (! Schema::hasColumn('packages', 'sync_status')) {
                $table->string('sync_status', 50)->nullable()->after('saas_last_synced_at')->index();
            }

            if (! Schema::hasColumn('packages', 'sync_error')) {
                $table->text('sync_error')->nullable()->after('sync_status');
            }
        });
    }

    public function down()
    {
        Schema::table('packages', function (Blueprint $table) {
            if (Schema::hasColumn('packages', 'sync_error')) {
                $table->dropColumn('sync_error');
            }

            if (Schema::hasColumn('packages', 'sync_status')) {
                $table->dropColumn('sync_status');
            }
        });
    }
}
