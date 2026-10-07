<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class PortalFeatureControllor extends Controller
{
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function index($module, Request $request)
    {
        $this->ensureTables();
        $config = $this->moduleConfig($module);
        abort_if(!$config, 404);

        $query = DB::table($config['table'])->orderBy('id', 'DESC');
        $search = trim($request->get('search', ''));

        if ($search !== '') {
            $query->where(function ($q) use ($config, $search) {
                foreach ($config['search'] as $column) {
                    $q->orWhere($column, 'LIKE', '%' . $search . '%');
                }
            });
        }

        $data = [
            'title' => $config['title'],
            'class' => 'portal-module',
            'table' => $config['title'],
            'module' => $module,
            'config' => $config,
            'items' => $query->paginate(25),
            'editItem' => null,
            'search' => $search,
        ];

        return view('admin.portal_modules.index', $data);
    }

    public function store($module, Request $request)
    {
        $this->ensureTables();
        $config = $this->moduleConfig($module);
        abort_if(!$config, 404);

        $validator = Validator::make($request->all(), $config['rules']);
        if ($validator->fails()) {
            return redirect('admin/portal/' . $module)->withInput()->withErrors($validator);
        }

        $data = $this->payload($request, $config);
        $data['created_at'] = now();
        $data['updated_at'] = now();

        DB::table($config['table'])->insert($data);
        Session::flash('success_msg', $config['singular'] . ' created successfully.');

        return redirect('admin/portal/' . $module);
    }

    public function edit($module, $id)
    {
        $this->ensureTables();
        $config = $this->moduleConfig($module);
        abort_if(!$config, 404);

        $data = [
            'title' => 'Edit ' . $config['singular'],
            'class' => 'portal-module',
            'table' => $config['title'],
            'module' => $module,
            'config' => $config,
            'items' => DB::table($config['table'])->orderBy('id', 'DESC')->paginate(25),
            'editItem' => DB::table($config['table'])->where('id', $id)->first(),
            'search' => '',
        ];

        abort_if(!$data['editItem'], 404);

        return view('admin.portal_modules.index', $data);
    }

    public function update($module, $id, Request $request)
    {
        $this->ensureTables();
        $config = $this->moduleConfig($module);
        abort_if(!$config, 404);

        $validator = Validator::make($request->all(), $config['rules']);
        if ($validator->fails()) {
            return redirect('admin/portal/' . $module . '/' . $id . '/edit')->withInput()->withErrors($validator);
        }

        $data = $this->payload($request, $config);
        $data['updated_at'] = now();

        DB::table($config['table'])->where('id', $id)->update($data);
        Session::flash('success_msg', $config['singular'] . ' updated successfully.');

        return redirect('admin/portal/' . $module);
    }

    public function destroy($module, $id)
    {
        $this->ensureTables();
        $config = $this->moduleConfig($module);
        abort_if(!$config, 404);

        DB::table($config['table'])->where('id', $id)->delete();
        Session::flash('success_msg', $config['singular'] . ' deleted successfully.');

        return redirect('admin/portal/' . $module);
    }

    public function reports()
    {
        $this->ensureTables();
        $income = (float) DB::table('finance_entries')->where('type', 'Income')->sum('amount');
        $expenses = (float) DB::table('finance_entries')->where('type', 'Expense')->sum('amount');
        $invoiceOutstanding = (float) DB::table('invoices')
            ->join('invoice_packages', 'invoice_packages.invoice_id', '=', 'invoices.invoice_id')
            ->where('invoices.status', 0)
            ->where('invoice_packages.status', 0)
            ->sum('invoice_packages.total');
        $invoicePaid = (float) DB::table('invoices')
            ->join('invoice_packages', 'invoice_packages.invoice_id', '=', 'invoices.invoice_id')
            ->where('invoices.status', 1)
            ->sum('invoice_packages.total');

        return view('admin.portal_modules.reports', [
            'title' => 'Reports',
            'class' => 'portal-reports',
            'table' => 'Reports',
            'income' => $income,
            'expenses' => $expenses,
            'profit' => $income + $invoicePaid - $expenses,
            'invoiceOutstanding' => $invoiceOutstanding,
            'invoicePaid' => $invoicePaid,
            'packageCount' => DB::table('packages')->count(),
            'unknownCount' => DB::table('packages')->where(function ($query) {
                $query->where('mailbox', '0')->orWhere('mailbox', '')->orWhereNull('mailbox');
            })->count(),
            'manifestCount' => DB::table('manifests')->count(),
            'supportOpen' => DB::table('admin_support_tickets')->where('status', '!=', 'Closed')->count(),
        ]);
    }

    public function settings(Request $request)
    {
        $this->ensureTables();
        $keys = [
            'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_encryption',
            'api_base_url', 'api_key', 'ai_auto_fill_enabled',
        ];

        if ($request->isMethod('post')) {
            foreach ($keys as $key) {
                DB::table('portal_settings')->updateOrInsert(
                    ['setting_key' => $key],
                    [
                        'setting_value' => $request->input($key, ''),
                        'group' => strpos($key, 'smtp_') === 0 ? 'smtp' : 'api',
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }

            Session::flash('success_msg', 'Portal integrations updated successfully.');
            return redirect('admin/portal-settings');
        }

        $settings = DB::table('portal_settings')->pluck('setting_value', 'setting_key')->toArray();

        return view('admin.portal_modules.settings', [
            'title' => 'Portal Integrations',
            'class' => 'portal-settings',
            'table' => 'Portal Integrations',
            'settings' => $settings,
        ]);
    }

    private function payload(Request $request, array $config)
    {
        $data = [];
        foreach ($config['fields'] as $field) {
            $data[$field['name']] = $request->input($field['name']);
        }
        return $data;
    }

    private function ensureTables()
    {
        $statements = [
            "CREATE TABLE IF NOT EXISTS admin_support_tickets (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, subject VARCHAR(255) NOT NULL, requester_name VARCHAR(255) NULL, requester_email VARCHAR(255) NULL, priority VARCHAR(255) NOT NULL DEFAULT 'Normal', status VARCHAR(255) NOT NULL DEFAULT 'Open', message TEXT NULL, resolution TEXT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS shipment_bookings (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, booking_no VARCHAR(255) NULL, mode VARCHAR(255) NOT NULL DEFAULT 'Air', origin VARCHAR(255) NULL, destination VARCHAR(255) NULL, carrier_name VARCHAR(255) NULL, scheduled_date DATE NULL, status VARCHAR(255) NOT NULL DEFAULT 'Scheduled', notes TEXT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS flight_schedules (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, flight_no VARCHAR(255) NULL, airline VARCHAR(255) NULL, origin VARCHAR(255) NULL, destination VARCHAR(255) NULL, departure_at DATETIME NULL, arrival_at DATETIME NULL, status VARCHAR(255) NOT NULL DEFAULT 'Scheduled', notes TEXT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS sea_vessel_schedules (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, vessel_name VARCHAR(255) NULL, voyage_no VARCHAR(255) NULL, origin_port VARCHAR(255) NULL, destination_port VARCHAR(255) NULL, departure_at DATETIME NULL, arrival_at DATETIME NULL, status VARCHAR(255) NOT NULL DEFAULT 'Scheduled', notes TEXT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS warehouse_supplies (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL, sku VARCHAR(255) NULL, quantity DECIMAL(12,2) NOT NULL DEFAULT 0, reorder_level DECIMAL(12,2) NOT NULL DEFAULT 0, unit VARCHAR(255) NOT NULL DEFAULT 'pcs', status VARCHAR(255) NOT NULL DEFAULT 'Available', notes TEXT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS internal_inventory (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL, sku VARCHAR(255) NULL, category VARCHAR(255) NULL, quantity DECIMAL(12,2) NOT NULL DEFAULT 0, value DECIMAL(12,2) NOT NULL DEFAULT 0, status VARCHAR(255) NOT NULL DEFAULT 'Active', notes TEXT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS internal_waste_logs (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, item_name VARCHAR(255) NOT NULL, quantity DECIMAL(12,2) NOT NULL DEFAULT 0, unit VARCHAR(255) NOT NULL DEFAULT 'pcs', reason VARCHAR(255) NULL, logged_at DATE NULL, disposal_method VARCHAR(255) NULL, notes TEXT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS finance_entries (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, type VARCHAR(255) NOT NULL, category VARCHAR(255) NULL, amount DECIMAL(12,2) NOT NULL DEFAULT 0, entry_date DATE NULL, reference VARCHAR(255) NULL, description TEXT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS portal_settings (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, setting_key VARCHAR(255) NOT NULL UNIQUE, setting_value TEXT NULL, `group` VARCHAR(255) NOT NULL DEFAULT 'general', created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        ];

        foreach ($statements as $statement) {
            DB::statement($statement);
        }
    }

    private function moduleConfig($module)
    {
        $configs = [
            'support' => [
                'title' => 'Support',
                'singular' => 'Support ticket',
                'table' => 'admin_support_tickets',
                'search' => ['subject', 'requester_name', 'requester_email', 'status'],
                'columns' => ['subject' => 'Subject', 'requester_name' => 'Requester', 'priority' => 'Priority', 'status' => 'Status'],
                'rules' => ['subject' => 'required|max:255'],
                'fields' => [
                    ['name' => 'subject', 'label' => 'Subject', 'type' => 'text', 'required' => true],
                    ['name' => 'requester_name', 'label' => 'Requester Name', 'type' => 'text'],
                    ['name' => 'requester_email', 'label' => 'Requester Email', 'type' => 'email'],
                    ['name' => 'priority', 'label' => 'Priority', 'type' => 'select', 'options' => ['Low', 'Normal', 'High', 'Urgent']],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['Open', 'Pending', 'Resolved', 'Closed']],
                    ['name' => 'message', 'label' => 'Message', 'type' => 'textarea'],
                    ['name' => 'resolution', 'label' => 'Resolution', 'type' => 'textarea'],
                ],
            ],
            'shipment-bookings' => [
                'title' => 'Shipment Bookings',
                'singular' => 'Shipment booking',
                'table' => 'shipment_bookings',
                'search' => ['booking_no', 'mode', 'origin', 'destination', 'carrier_name', 'status'],
                'columns' => ['booking_no' => 'Booking No', 'mode' => 'Mode', 'origin' => 'Origin', 'destination' => 'Destination', 'scheduled_date' => 'Scheduled', 'status' => 'Status'],
                'rules' => ['mode' => 'required'],
                'fields' => [
                    ['name' => 'booking_no', 'label' => 'Booking No', 'type' => 'text'],
                    ['name' => 'mode', 'label' => 'Mode', 'type' => 'select', 'options' => ['Air', 'Sea']],
                    ['name' => 'origin', 'label' => 'Origin', 'type' => 'text'],
                    ['name' => 'destination', 'label' => 'Destination', 'type' => 'text'],
                    ['name' => 'carrier_name', 'label' => 'Carrier', 'type' => 'text'],
                    ['name' => 'scheduled_date', 'label' => 'Scheduled Date', 'type' => 'date'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['Scheduled', 'Confirmed', 'In Transit', 'Completed', 'Cancelled']],
                    ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea'],
                ],
            ],
            'flight-schedules' => [
                'title' => 'Flight Schedules',
                'singular' => 'Flight schedule',
                'table' => 'flight_schedules',
                'search' => ['flight_no', 'airline', 'origin', 'destination', 'status'],
                'columns' => ['flight_no' => 'Flight No', 'airline' => 'Airline', 'origin' => 'Origin', 'destination' => 'Destination', 'departure_at' => 'Departure', 'status' => 'Status'],
                'rules' => ['flight_no' => 'required|max:255'],
                'fields' => [
                    ['name' => 'flight_no', 'label' => 'Flight No', 'type' => 'text', 'required' => true],
                    ['name' => 'airline', 'label' => 'Airline', 'type' => 'text'],
                    ['name' => 'origin', 'label' => 'Origin', 'type' => 'text'],
                    ['name' => 'destination', 'label' => 'Destination', 'type' => 'text'],
                    ['name' => 'departure_at', 'label' => 'Departure', 'type' => 'datetime-local'],
                    ['name' => 'arrival_at', 'label' => 'Arrival', 'type' => 'datetime-local'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['Scheduled', 'Delayed', 'Departed', 'Arrived', 'Cancelled']],
                    ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea'],
                ],
            ],
            'sea-vessel-schedules' => [
                'title' => 'Sea Vessel Schedules',
                'singular' => 'Sea vessel schedule',
                'table' => 'sea_vessel_schedules',
                'search' => ['vessel_name', 'voyage_no', 'origin_port', 'destination_port', 'status'],
                'columns' => ['vessel_name' => 'Vessel', 'voyage_no' => 'Voyage', 'origin_port' => 'Origin Port', 'destination_port' => 'Destination Port', 'departure_at' => 'Departure', 'status' => 'Status'],
                'rules' => ['vessel_name' => 'required|max:255'],
                'fields' => [
                    ['name' => 'vessel_name', 'label' => 'Vessel Name', 'type' => 'text', 'required' => true],
                    ['name' => 'voyage_no', 'label' => 'Voyage No', 'type' => 'text'],
                    ['name' => 'origin_port', 'label' => 'Origin Port', 'type' => 'text'],
                    ['name' => 'destination_port', 'label' => 'Destination Port', 'type' => 'text'],
                    ['name' => 'departure_at', 'label' => 'Departure', 'type' => 'datetime-local'],
                    ['name' => 'arrival_at', 'label' => 'Arrival', 'type' => 'datetime-local'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['Scheduled', 'Loading', 'Sailed', 'Arrived', 'Cancelled']],
                    ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea'],
                ],
            ],
            'warehouse-supplies' => [
                'title' => 'Warehouse Supplies',
                'singular' => 'Warehouse supply',
                'table' => 'warehouse_supplies',
                'search' => ['name', 'sku', 'status'],
                'columns' => ['name' => 'Name', 'sku' => 'SKU', 'quantity' => 'Quantity', 'reorder_level' => 'Reorder Level', 'unit' => 'Unit', 'status' => 'Status'],
                'rules' => ['name' => 'required|max:255'],
                'fields' => [
                    ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                    ['name' => 'sku', 'label' => 'SKU', 'type' => 'text'],
                    ['name' => 'quantity', 'label' => 'Quantity', 'type' => 'number'],
                    ['name' => 'reorder_level', 'label' => 'Reorder Level', 'type' => 'number'],
                    ['name' => 'unit', 'label' => 'Unit', 'type' => 'text'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['Available', 'Low Stock', 'Out of Stock', 'Archived']],
                    ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea'],
                ],
            ],
            'internal-inventory' => [
                'title' => 'Internal Inventory',
                'singular' => 'Inventory item',
                'table' => 'internal_inventory',
                'search' => ['name', 'sku', 'category', 'status'],
                'columns' => ['name' => 'Name', 'sku' => 'SKU', 'category' => 'Category', 'quantity' => 'Quantity', 'value' => 'Value', 'status' => 'Status'],
                'rules' => ['name' => 'required|max:255'],
                'fields' => [
                    ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                    ['name' => 'sku', 'label' => 'SKU', 'type' => 'text'],
                    ['name' => 'category', 'label' => 'Category', 'type' => 'text'],
                    ['name' => 'quantity', 'label' => 'Quantity', 'type' => 'number'],
                    ['name' => 'value', 'label' => 'Value', 'type' => 'number'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['Active', 'Maintenance', 'Retired']],
                    ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea'],
                ],
            ],
            'internal-waste' => [
                'title' => 'Internal Waste Logs',
                'singular' => 'Waste log',
                'table' => 'internal_waste_logs',
                'search' => ['item_name', 'reason', 'disposal_method'],
                'columns' => ['item_name' => 'Item', 'quantity' => 'Quantity', 'unit' => 'Unit', 'reason' => 'Reason', 'logged_at' => 'Logged At', 'disposal_method' => 'Disposal'],
                'rules' => ['item_name' => 'required|max:255'],
                'fields' => [
                    ['name' => 'item_name', 'label' => 'Item Name', 'type' => 'text', 'required' => true],
                    ['name' => 'quantity', 'label' => 'Quantity', 'type' => 'number'],
                    ['name' => 'unit', 'label' => 'Unit', 'type' => 'text'],
                    ['name' => 'reason', 'label' => 'Reason', 'type' => 'text'],
                    ['name' => 'logged_at', 'label' => 'Logged At', 'type' => 'date'],
                    ['name' => 'disposal_method', 'label' => 'Disposal Method', 'type' => 'text'],
                    ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea'],
                ],
            ],
            'finance-entries' => [
                'title' => 'Income / Expense',
                'singular' => 'Finance entry',
                'table' => 'finance_entries',
                'search' => ['type', 'category', 'reference', 'description'],
                'columns' => ['type' => 'Type', 'category' => 'Category', 'amount' => 'Amount', 'entry_date' => 'Date', 'reference' => 'Reference'],
                'rules' => ['type' => 'required', 'amount' => 'required|numeric'],
                'fields' => [
                    ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'options' => ['Income', 'Expense'], 'required' => true],
                    ['name' => 'category', 'label' => 'Category', 'type' => 'text'],
                    ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'required' => true],
                    ['name' => 'entry_date', 'label' => 'Date', 'type' => 'date'],
                    ['name' => 'reference', 'label' => 'Reference', 'type' => 'text'],
                    ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
                ],
            ],
        ];

        return $configs[$module] ?? null;
    }
}
