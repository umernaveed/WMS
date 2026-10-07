<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSaasSyncColumns extends Migration
{
    public function up()
    {
        Schema::table('couriers', function (Blueprint $table) {
            if (! Schema::hasColumn('couriers', 'saas_tenant_id')) {
                $table->unsignedBigInteger('saas_tenant_id')->nullable()->after('id')->index();
            }
            if (! Schema::hasColumn('couriers', 'saas_last_synced_at')) {
                $table->timestamp('saas_last_synced_at')->nullable()->after('saas_tenant_id');
            }
        });

        Schema::table('packages', function (Blueprint $table) {
            if (! Schema::hasColumn('packages', 'saas_package_id')) {
                $table->unsignedBigInteger('saas_package_id')->nullable()->after('packeg_id')->index();
            }
            if (! Schema::hasColumn('packages', 'saas_last_synced_at')) {
                $table->timestamp('saas_last_synced_at')->nullable()->after('saas_package_id');
            }
        });

        Schema::table('manifests', function (Blueprint $table) {
            if (! Schema::hasColumn('manifests', 'saas_manifest_id')) {
                $table->unsignedBigInteger('saas_manifest_id')->nullable()->after('id')->index();
            }
            if (! Schema::hasColumn('manifests', 'saas_last_synced_at')) {
                $table->timestamp('saas_last_synced_at')->nullable()->after('saas_manifest_id');
            }
        });
    }

    public function down()
    {
        Schema::table('manifests', function (Blueprint $table) {
            if (Schema::hasColumn('manifests', 'saas_last_synced_at')) {
                $table->dropColumn('saas_last_synced_at');
            }
            if (Schema::hasColumn('manifests', 'saas_manifest_id')) {
                $table->dropColumn('saas_manifest_id');
            }
        });

        Schema::table('packages', function (Blueprint $table) {
            if (Schema::hasColumn('packages', 'saas_last_synced_at')) {
                $table->dropColumn('saas_last_synced_at');
            }
            if (Schema::hasColumn('packages', 'saas_package_id')) {
                $table->dropColumn('saas_package_id');
            }
        });

        Schema::table('couriers', function (Blueprint $table) {
            if (Schema::hasColumn('couriers', 'saas_last_synced_at')) {
                $table->dropColumn('saas_last_synced_at');
            }
            if (Schema::hasColumn('couriers', 'saas_tenant_id')) {
                $table->dropColumn('saas_tenant_id');
            }
        });
    }
}
