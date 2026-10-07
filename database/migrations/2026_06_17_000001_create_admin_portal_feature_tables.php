<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminPortalFeatureTables extends Migration
{
    public function up()
    {
        $this->createTable('admin_support_tickets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('subject');
            $table->string('requester_name')->nullable();
            $table->string('requester_email')->nullable();
            $table->string('priority')->default('Normal');
            $table->string('status')->default('Open');
            $table->text('message')->nullable();
            $table->text('resolution')->nullable();
            $table->timestamps();
        });

        $this->createTable('shipment_bookings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('booking_no')->nullable();
            $table->string('mode')->default('Air');
            $table->string('origin')->nullable();
            $table->string('destination')->nullable();
            $table->string('carrier_name')->nullable();
            $table->date('scheduled_date')->nullable();
            $table->string('status')->default('Scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        $this->createTable('flight_schedules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('flight_no')->nullable();
            $table->string('airline')->nullable();
            $table->string('origin')->nullable();
            $table->string('destination')->nullable();
            $table->dateTime('departure_at')->nullable();
            $table->dateTime('arrival_at')->nullable();
            $table->string('status')->default('Scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        $this->createTable('sea_vessel_schedules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('vessel_name')->nullable();
            $table->string('voyage_no')->nullable();
            $table->string('origin_port')->nullable();
            $table->string('destination_port')->nullable();
            $table->dateTime('departure_at')->nullable();
            $table->dateTime('arrival_at')->nullable();
            $table->string('status')->default('Scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        $this->createTable('warehouse_supplies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('sku')->nullable();
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('reorder_level', 12, 2)->default(0);
            $table->string('unit')->default('pcs');
            $table->string('status')->default('Available');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        $this->createTable('internal_inventory', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('category')->nullable();
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('value', 12, 2)->default(0);
            $table->string('status')->default('Active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        $this->createTable('internal_waste_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('item_name');
            $table->decimal('quantity', 12, 2)->default(0);
            $table->string('unit')->default('pcs');
            $table->string('reason')->nullable();
            $table->date('logged_at')->nullable();
            $table->string('disposal_method')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        $this->createTable('finance_entries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type');
            $table->string('category')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('entry_date')->nullable();
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        $this->createTable('portal_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('setting_key')->unique();
            $table->text('setting_value')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('portal_settings');
        Schema::dropIfExists('finance_entries');
        Schema::dropIfExists('internal_waste_logs');
        Schema::dropIfExists('internal_inventory');
        Schema::dropIfExists('warehouse_supplies');
        Schema::dropIfExists('sea_vessel_schedules');
        Schema::dropIfExists('flight_schedules');
        Schema::dropIfExists('shipment_bookings');
        Schema::dropIfExists('admin_support_tickets');
    }

    private function createTable($name, Closure $callback)
    {
        if (!Schema::hasTable($name)) {
            Schema::create($name, $callback);
        }
    }
}
