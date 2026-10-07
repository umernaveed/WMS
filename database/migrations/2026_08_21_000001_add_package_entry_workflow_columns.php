<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPackageEntryWorkflowColumns extends Migration
{
    public function up()
    {
        Schema::table('packages', function (Blueprint $table) {
            if (! Schema::hasColumn('packages', 'entry_mode')) {
                $table->string('entry_mode', 30)->nullable()->after('is_invoice')->index();
            }
            if (! Schema::hasColumn('packages', 'routing_status')) {
                $table->string('routing_status', 30)->nullable()->after('entry_mode')->index();
            }
            if (! Schema::hasColumn('packages', 'warehouse_code')) {
                $table->string('warehouse_code', 20)->nullable()->after('routing_status');
            }
            if (! Schema::hasColumn('packages', 'weight_unit')) {
                $table->string('weight_unit', 10)->nullable()->after('weight');
            }
            if (! Schema::hasColumn('packages', 'pieces')) {
                $table->unsignedInteger('pieces')->default(1)->after('weight_unit');
            }
            if (! Schema::hasColumn('packages', 'consignee_name_on_package')) {
                $table->string('consignee_name_on_package')->nullable()->after('user_name');
            }
            if (! Schema::hasColumn('packages', 'unknown_reason')) {
                $table->string('unknown_reason', 80)->nullable()->after('consignee_name_on_package');
            }
            if (! Schema::hasColumn('packages', 'internal_notes')) {
                $table->text('internal_notes')->nullable()->after('unknown_reason');
            }
            if (! Schema::hasColumn('packages', 'package_image_paths')) {
                $table->text('package_image_paths')->nullable()->after('internal_notes');
            }
            if (! Schema::hasColumn('packages', 'dimension_length')) {
                $table->decimal('dimension_length', 10, 2)->nullable()->after('freight_type');
            }
            if (! Schema::hasColumn('packages', 'dimension_width')) {
                $table->decimal('dimension_width', 10, 2)->nullable()->after('dimension_length');
            }
            if (! Schema::hasColumn('packages', 'dimension_height')) {
                $table->decimal('dimension_height', 10, 2)->nullable()->after('dimension_width');
            }
            if (! Schema::hasColumn('packages', 'dimension_unit')) {
                $table->string('dimension_unit', 10)->nullable()->after('dimension_height');
            }
            if (! Schema::hasColumn('packages', 'volume_cuft')) {
                $table->decimal('volume_cuft', 12, 4)->nullable()->after('dimension_unit');
            }
            if (! Schema::hasColumn('packages', 'volume_cbm')) {
                $table->decimal('volume_cbm', 12, 4)->nullable()->after('volume_cuft');
            }
            if (! Schema::hasColumn('packages', 'chargeable_weight')) {
                $table->decimal('chargeable_weight', 10, 2)->nullable()->after('volume_cbm');
            }
            if (! Schema::hasColumn('packages', 'capture_method')) {
                $table->string('capture_method', 30)->nullable()->after('chargeable_weight');
            }
            if (! Schema::hasColumn('packages', 'idempotency_key')) {
                $table->string('idempotency_key', 120)->nullable()->unique()->after('capture_method');
            }
        });
    }

    public function down()
    {
        Schema::table('packages', function (Blueprint $table) {
            foreach ([
                'idempotency_key',
                'capture_method',
                'chargeable_weight',
                'volume_cbm',
                'volume_cuft',
                'dimension_unit',
                'dimension_height',
                'dimension_width',
                'dimension_length',
                'package_image_paths',
                'internal_notes',
                'unknown_reason',
                'consignee_name_on_package',
                'pieces',
                'weight_unit',
                'warehouse_code',
                'routing_status',
                'entry_mode',
            ] as $column) {
                if (Schema::hasColumn('packages', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
