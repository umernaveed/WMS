<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Models\Manifest;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SaasSyncController extends Controller
{
    public function courier(Request $request)
    {
        $this->authorizeSync($request);
        $data = $request->validate([
            'tenant_id' => 'required|integer',
            'company_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'parish' => 'nullable|string|max:255',
            'portal_url' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
        ]);

        $courier = Courier::where('saas_tenant_id', $data['tenant_id'])->first()
            ?: Courier::where('email', $data['email'])->first()
            ?: new Courier();

        $courier->fill([
            'saas_tenant_id' => $data['tenant_id'],
            'saas_last_synced_at' => now(),
            'name' => $data['company_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? '',
            'local_address' => $data['address'] ?? '',
            'city' => $data['city'] ?? '',
            'state' => $data['parish'] ?? '',
            'base_url' => $data['portal_url'] ?? '',
            'is_courier' => 'Yes',
            'status' => ($data['status'] ?? 'active') === 'active' ? '0' : '1',
        ]);

        if (! $courier->exists) {
            $plainPassword = Str::random(16);
            $courier->api_key = Str::random(30);
            $courier->password = Hash::make($plainPassword);
            $courier->hint_password = $plainPassword;
            $courier->created_at = now();
        }

        $courier->updated_at = now();
        $courier->save();

        return response()->json(['status' => 'success', 'data' => ['courier_id' => $courier->id]]);
    }

    public function customer(Request $request)
    {
        $this->authorizeSync($request);
        $data = $request->validate([
            'courier_id' => 'required|integer',
            'mailbox' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'outlet_id' => 'nullable|string|max:255',
            'active' => 'nullable|boolean',
        ]);

        $name = trim((string) ($data['name'] ?? trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? ''))));
        [$firstName, $lastName] = $this->splitName($name);

        $userId = DB::table('users')
            ->where('courier_id', $data['courier_id'])
            ->where('mailbox', $data['mailbox'])
            ->value('user_id');

        $values = [
            'courier_id' => $data['courier_id'],
            'outlet_id' => $data['outlet_id'] ?? '0',
            'mailbox' => $data['mailbox'],
            'first_name' => $data['first_name'] ?? $firstName,
            'last_name' => $data['last_name'] ?? $lastName,
            'user_name' => $name ?: trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? '')),
            'email' => $this->validEmail($data['email'] ?? null) ?: '',
            'phone' => $data['phone'] ?? '',
            'address_1' => $data['address'] ?? '',
            'city' => $data['city'] ?? '',
            'state' => $data['state'] ?? '',
            'status' => ($data['active'] ?? true) ? '1' : '0',
            'updated_at' => now(),
        ];

        if ($userId) {
            DB::table('users')->where('user_id', $userId)->update($values);
        } else {
            $values['created_at'] = now();
            $userId = DB::table('users')->insertGetId($values);
        }

        return response()->json(['status' => 'success', 'data' => ['user_id' => $userId]]);
    }

    public function package(Request $request)
    {
        $this->authorizeSync($request);
        $data = $request->validate([
            'courier_id' => 'required|integer',
            'package_id' => 'required|integer',
            'tracking_no' => 'required|string|max:100',
            'supplier_tracking_no' => 'nullable|string|max:255',
            'mailbox' => 'nullable|string|max:100',
            'user_name' => 'nullable|string|max:255',
            'courier' => 'nullable|string|max:100',
            'merchant' => 'nullable|string|max:255',
            'item_description' => 'nullable|string',
            'package_type' => 'nullable|string|max:255',
            'weight' => 'nullable',
            'price' => 'nullable',
            'freight_type' => 'nullable|string|max:255',
            'status_name' => 'nullable|string|max:255',
            'manifest_no' => 'nullable|string|max:255',
            'date_received' => 'nullable',
        ]);

        $package = Package::where('saas_package_id', $data['package_id'])->first()
            ?: Package::where('courier_id', $data['courier_id'])->where('tracking_no', $data['tracking_no'])->first()
            ?: new Package();

        $package->fill([
            'saas_package_id' => $data['package_id'],
            'saas_last_synced_at' => now(),
            'courier_id' => $data['courier_id'],
            'tracking_no' => $data['tracking_no'],
            'supplier_tracking_no' => $data['supplier_tracking_no'] ?? '',
            'mailbox' => $data['mailbox'] ?? '0',
            'user_name' => $data['user_name'] ?? '',
            'courier' => $data['courier'] ?? '',
            'merchant' => $data['merchant'] ?? '',
            'item_description' => $data['item_description'] ?? '',
            'package_type' => $data['package_type'] ?? '',
            'weight' => $data['weight'] ?? 0,
            'price' => $data['price'] ?? 0,
            'freight_type' => str_contains(strtolower((string) ($data['freight_type'] ?? '')), 'sea') ? 'Sea Freight' : 'Air Freight',
            'status_name' => $data['status_name'] ?? '',
            'manifest_no' => $data['manifest_no'] ?? '',
            'date_received' => $data['date_received'] ?? '',
            'sync_status' => 'synced',
            'sync_error' => null,
        ]);

        if (! $package->exists) {
            $package->status = 3;
            $package->created_at = now();
        }

        $package->updated_at = now();
        $package->save();

        return response()->json(['status' => 'success', 'data' => ['package_id' => $package->packeg_id]]);
    }

    public function manifest(Request $request)
    {
        $this->authorizeSync($request);
        $data = $request->validate([
            'courier_id' => 'required|integer',
            'manifest_id' => 'required|integer',
            'manifest_number' => 'required|string|max:255',
            'master_air_billing_no' => 'nullable|string|max:255',
            'package_tracking_numbers' => 'nullable|array',
            'package_tracking_numbers.*' => 'string|max:100',
        ]);

        $manifest = Manifest::where('saas_manifest_id', $data['manifest_id'])->first() ?: new Manifest();
        $manifest->fill([
            'saas_manifest_id' => $data['manifest_id'],
            'saas_last_synced_at' => now(),
            'courier_id' => $data['courier_id'],
            'master_air_billing_no' => $data['master_air_billing_no'] ?? $data['manifest_number'],
            'file_pdf' => '',
        ]);
        if (! $manifest->exists) {
            $manifest->created_at = now();
        }
        $manifest->updated_at = now();
        $manifest->save();

        $packageIds = Package::where('courier_id', $data['courier_id'])
            ->whereIn('tracking_no', $data['package_tracking_numbers'] ?? [])
            ->pluck('packeg_id');

        DB::table('manifest_packeges')->where('manifest_id', $manifest->id)->delete();
        foreach ($packageIds as $packageId) {
            DB::table('manifest_packeges')->insert([
                'manifest_id' => $manifest->id,
                'packeges_id' => $packageId,
            ]);
        }

        Package::whereIn('packeg_id', $packageIds)->update(['is_added_manifest' => 1]);

        return response()->json(['status' => 'success', 'data' => ['manifest_id' => $manifest->id]]);
    }

    private function authorizeSync(Request $request): void
    {
        $token = (string) config('services.courier_saas.token');
        abort_if($token === '' || ! hash_equals($token, (string) $request->bearerToken()), 401);
    }

    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2);

        return [$parts[0] ?? '', $parts[1] ?? ''];
    }

    private function validEmail(?string $email): ?string
    {
        $email = trim((string) $email);

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }
}
