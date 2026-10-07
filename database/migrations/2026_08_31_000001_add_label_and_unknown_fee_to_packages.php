<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            if (! Schema::hasColumn('packages', 'label_type')) {
                $table->string('label_type', 40)->nullable()->after('freight_type');
            }
            if (! Schema::hasColumn('packages', 'unknown_fee')) {
                $table->decimal('unknown_fee', 10, 2)->default(0)->after('price');
            }
            if (! Schema::hasColumn('packages', 'unknown_fee_reason')) {
                $table->string('unknown_fee_reason')->nullable()->after('unknown_fee');
            }
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            foreach (['unknown_fee_reason', 'unknown_fee', 'label_type'] as $column) {
                if (Schema::hasColumn('packages', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
