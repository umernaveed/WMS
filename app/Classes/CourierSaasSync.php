<?php

namespace App\Classes;

use App\Models\Courier;
use App\Models\Manifest;
use App\Models\Package;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CourierSaasSync
{
    private static $lastError = null;

    public static function lastError(): ?string
    {
        return self::$lastError;
    }

    public static function customer($user): void
    {
        $courierId = (int) ($user->courier_id ?? 0);
        if (! $courierId) {
            return;
        }

        self::post('/api/wms/customers', [
            'courier_id' => $courierId,
            'mailbox' => $user->mailbox ?? '',
            'first_name' => $user->first_name ?? '',
            'last_name' => $user->last_name ?? '',
            'name' => $user->user_name ?? trim(($user->first_name ?? '').' '.($user->last_name ?? '')),
            'email' => $user->email ?? null,
            'phone' => $user->phone ?? null,
            'address' => $user->address_1 ?? null,
            'city' => $user->city ?? null,
            'state' => $user->state ?? null,
            'active' => (string) ($user->status ?? '1') !== '0',
        ]);
    }

    public static function package(Package $package): bool
    {
        $courierId = (int) $package->courier_id;
        if (! $courierId) {
            self::markPackageSync($package, 'skipped', 'Package has no courier_id.');
            return false;
        }

        self::markPackageSync($package, 'pending', null);

        $shipmentType = self::shipmentType($package);
        $region = self::region($package);
        $routingStatus = self::routingStatus($package);
        $response = self::post('/api/wms/packages', [
            'courier_id' => $courierId,
            'courier_identifier' => (string) $courierId,
            'wms_package_id' => $package->packeg_id,
            'tracking_no' => $package->tracking_no,
            'tracking_number' => $package->supplier_tracking_no ?: $package->tracking_no,
            'hawb' => $package->manifest_no ?: $package->tracking_no,
            'supplier_tracking_no' => $package->supplier_tracking_no,
            'mailbox' => $package->mailbox,
            'user_name' => $package->user_name,
            'courier' => $package->courier,
            'merchant' => $package->merchant,
            'item_description' => $package->item_description,
            'package_type' => $package->package_type,
            'weight' => is_numeric($package->weight) ? $package->weight : null,
            'weight_unit' => self::measureUnit($package),
            'pieces' => self::packageColumn($package, 'pieces') ?: 1,
            'volume' => self::volume($package),
            'price' => is_numeric($package->price) ? $package->price : null,
            'freight_type' => $package->freight_type,
            'shipment_type' => $shipmentType,
            'region' => $region,
            'label_type' => self::labelType($package),
            'entry_mode' => self::packageColumn($package, 'entry_mode'),
            'routing_status' => $routingStatus,
            'warehouse_code' => self::packageColumn($package, 'warehouse_code'),
            'consignee_name_on_package' => self::packageColumn($package, 'consignee_name_on_package'),
            'unknown_reason' => self::packageColumn($package, 'unknown_reason'),
            'internal_notes' => self::packageColumn($package, 'internal_notes'),
            'package_image_paths' => self::decodedPackageImages($package),
            'dimension_length' => self::packageColumn($package, 'dimension_length'),
            'dimension_width' => self::packageColumn($package, 'dimension_width'),
            'dimension_height' => self::packageColumn($package, 'dimension_height'),
            'dimension_unit' => self::packageColumn($package, 'dimension_unit'),
            'volume_cuft' => self::packageColumn($package, 'volume_cuft'),
            'volume_cbm' => self::packageColumn($package, 'volume_cbm'),
            'chargeable_weight' => self::packageColumn($package, 'chargeable_weight'),
            'unknown_fee' => self::packageColumn($package, 'unknown_fee'),
            'unknown_fee_jmd' => self::packageColumn($package, 'unknown_fee'),
            'unknown_fee_reason' => self::packageColumn($package, 'unknown_fee_reason'),
            'capture_method' => self::packageColumn($package, 'capture_method'),
            'idempotency_key' => self::packageColumn($package, 'idempotency_key'),
            'status_name' => self::packageStatusName($package, $region),
            'manifest_no' => $package->manifest_no,
            'date_received' => self::validDate($package->date_received) ? $package->date_received : null,
            'metadata' => [
                'wms_package_id' => $package->packeg_id,
                'package_no' => $package->package_no ?? null,
                'package_code' => $package->package_code ?? null,
                'category_id' => $package->category_id ?? null,
                'vendor_id' => $package->vendor_id ?? null,
                'routing_status' => $routingStatus,
                'entry_mode' => self::packageColumn($package, 'entry_mode'),
                'package_image_paths' => self::decodedPackageImages($package),
            ],
        ]);

        $saasPackageId = data_get($response, 'data.package_id');
        if ($saasPackageId) {
            self::markPackageSync($package, 'synced', null, [
                'saas_package_id' => $saasPackageId,
                'saas_last_synced_at' => now(),
            ]);
            self::status($package, $region);
            return true;
        }

        self::markPackageSync($package, 'failed', self::$lastError ?: 'SaaS did not return a package ID.');

        return false;
    }

    public static function packages($packages, int $chunkSize = 25, int $pauseMs = 250): array
    {
        $stats = ['synced' => 0, 'failed' => 0, 'skipped' => 0];

        collect($packages)->chunk(max(1, $chunkSize))->each(function ($chunk) use (&$stats, $pauseMs) {
            if (function_exists('set_time_limit')) {
                @set_time_limit(60);
            }

            foreach ($chunk as $package) {
                $package = $package instanceof Package ? $package : Package::find($package);
                if (! $package) {
                    $stats['skipped']++;
                    continue;
                }

                if (self::package($package)) {
                    $stats['synced']++;
                } else {
                    $fresh = Package::find($package->packeg_id);
                    $status = $fresh->sync_status ?? null;
                    $stats[$status === 'skipped' ? 'skipped' : 'failed']++;
                }
            }

            if ($pauseMs > 0) {
                usleep($pauseMs * 1000);
            }
        });

        return $stats;
    }

    public static function nextPackageTrackingNumbers(int $courierId, int $count = 1): array
    {
        if ($courierId <= 0) {
            return [];
        }

        $response = self::post('/api/wms/packages/next-hawb', [
            'courier_id' => $courierId,
            'count' => max(1, $count),
        ]);

        return collect(data_get($response, 'data.tracking_numbers', []))
            ->map(fn ($value) => strtoupper(trim((string) $value)))
            ->filter()
            ->values()
            ->all();
    }

    public static function nextPackageTrackingNumber(int $courierId): ?string
    {
        return self::nextPackageTrackingNumbers($courierId, 1)[0] ?? null;
    }

    public static function deletePackage(Package $package): bool
    {
        $courierId = (int) $package->courier_id;
        if (! $courierId) {
            return false;
        }

        $response = self::delete('/api/wms/packages', [
            'courier_id' => $courierId,
            'wms_package_id' => $package->packeg_id,
            'tracking_no' => $package->tracking_no,
        ]);

        return (bool) $response && data_get($response, 'status') === 'success';
    }

    public static function status(Package $package, ?string $region = null): bool
    {
        $region = $region ?: self::region($package);
        if (! in_array($region, ['US', 'CN'], true)) {
            return false;
        }

        $response = self::post('/courier/statuses', [
            'courier_id' => (int) $package->courier_id,
            'courier_identifier' => (string) $package->courier_id,
            'tracking_number' => $package->supplier_tracking_no ?: $package->tracking_no,
            'hawb' => $package->manifest_no ?: $package->tracking_no,
            'region' => $region,
            'status' => self::warehouseStatusName($region),
            'timestamp' => now()->toIso8601String(),
        ]);

        return (bool) $response && data_get($response, 'status') === 'success';
    }

    public static function label(string $trackingNumber): ?array
    {
        $trackingNumber = strtoupper(trim($trackingNumber));
        if ($trackingNumber === '') {
            return null;
        }

        return data_get(self::get('/labels/'.rawurlencode($trackingNumber), []), 'data');
    }

    public static function labelHtml(string $trackingNumber): ?string
    {
        $trackingNumber = strtoupper(trim($trackingNumber));
        if ($trackingNumber === '') {
            return null;
        }

        return self::getRaw('/labels/'.rawurlencode($trackingNumber).'/html');
    }

    public static function manifest(Manifest $manifest): void
    {
        $courierId = (int) ($manifest->courier_id ?: $manifest->coerier_id ?? 0);
        if (! $courierId) {
            return;
        }

        $tracking = DB::table('manifest_packeges')
            ->join('packages', 'packages.packeg_id', '=', 'manifest_packeges.packeges_id')
            ->where('manifest_packeges.manifest_id', $manifest->id)
            ->pluck('packages.tracking_no')
            ->filter()
            ->values()
            ->all();

        $response = self::post('/api/wms/manifests', [
            'courier_id' => $courierId,
            'wms_manifest_id' => $manifest->id,
            'manifest_number' => $manifest->master_air_billing_no ?: 'WMS-'.$manifest->id,
            'master_air_billing_no' => $manifest->master_air_billing_no,
            'status' => 'submitted',
            'package_tracking_numbers' => $tracking,
        ]);

        $saasManifestId = data_get($response, 'data.manifest_id');
        if ($saasManifestId) {
            Manifest::where('id', $manifest->id)->update([
                'saas_manifest_id' => $saasManifestId,
                'saas_last_synced_at' => now(),
            ]);
        }
    }

    private static function post(string $path, array $payload): ?array
    {
        return self::send('post', $path, $payload);
    }

    private static function get(string $path, array $payload): ?array
    {
        return self::send('get', $path, $payload);
    }

    private static function getRaw(string $path): ?string
    {
        self::$lastError = null;
        $baseUrl = self::baseUrl();
        $token = config('services.courier_saas.token');
        if ($baseUrl === '' || ! $token) {
            self::$lastError = 'COURIER_SAAS_URL or WMS_SYNC_TOKEN is missing.';
            return null;
        }

        try {
            $response = Http::timeout((int) config('services.courier_saas.timeout', 10))
                ->withToken($token)
                ->get($baseUrl.$path);

            if (! $response->successful()) {
                self::$lastError = 'HTTP '.$response->status().': '.$response->body();
                Log::warning('Courier SaaS raw sync request failed', [
                    'path' => $path,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $response->body();
        } catch (\Throwable $exception) {
            self::$lastError = $exception->getMessage();
            Log::warning('Courier SaaS raw sync request exception', [
                'path' => $path,
                'message' => $exception->getMessage(),
            ]);
            return null;
        }
    }

    private static function delete(string $path, array $payload): ?array
    {
        return self::send('delete', $path, $payload);
    }

    private static function send(string $method, string $path, array $payload): ?array
    {
        self::$lastError = null;
        $baseUrl = self::baseUrl();
        $token = config('services.courier_saas.token');
        if ($baseUrl === '' || ! $token) {
            self::$lastError = 'COURIER_SAAS_URL or WMS_SYNC_TOKEN is missing.';
            return null;
        }

        try {
            $request = Http::timeout((int) config('services.courier_saas.timeout', 10))
                ->acceptJson()
                ->withToken($token);
            $response = $method === 'get'
                ? $request->get($baseUrl.$path, $payload)
                : $request->{$method}($baseUrl.$path, $payload);

            if (! $response->successful()) {
                self::$lastError = 'HTTP '.$response->status().': '.$response->body();
                Log::warning('Courier SaaS sync request failed', [
                    'path' => $path,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $response->json();
        } catch (\Throwable $exception) {
            self::$lastError = $exception->getMessage();
            Log::warning('Courier SaaS sync request exception', [
                'path' => $path,
                'message' => $exception->getMessage(),
            ]);
            return null;
        }
    }

    private static function baseUrl(): string
    {
        $baseUrl = rtrim((string) config('services.courier_saas.url'), '/');

        if (stripos($baseUrl, 'http://freitora.online') === 0) {
            return 'https://'.substr($baseUrl, strlen('http://'));
        }

        return $baseUrl;
    }

    private static function markPackageSync(Package $package, string $status, ?string $error, array $extra = []): void
    {
        $updates = $extra;

        if (Schema::hasColumn('packages', 'sync_status')) {
            $updates['sync_status'] = $status;
        }

        if (Schema::hasColumn('packages', 'sync_error')) {
            $updates['sync_error'] = $error ? substr($error, 0, 1000) : null;
        }

        if ($updates) {
            Package::where('packeg_id', $package->packeg_id)->update($updates);
        }
    }

    private static function validDate($value): bool
    {
        if (! $value || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return false;
        }

        return strtotime((string) $value) !== false;
    }

    private static function shipmentType(Package $package): string
    {
        return stripos((string) $package->freight_type, 'sea') !== false ? 'SEA' : 'AIR';
    }

    private static function volume(Package $package): ?float
    {
        $volume = self::packageColumn($package, self::region($package) === 'CN' ? 'volume_cbm' : 'volume_cuft');
        if (is_numeric($volume) && (float) $volume > 0) {
            return (float) $volume;
        }

        return self::shipmentType($package) === 'SEA' && is_numeric($package->weight) ? (float) $package->weight : null;
    }

    private static function measureUnit(Package $package): string
    {
        if (self::shipmentType($package) === 'SEA') {
            if (is_numeric(self::packageColumn($package, 'volume_cuft')) && (float) self::packageColumn($package, 'volume_cuft') > 0) {
                return 'cuft';
            }
            if (is_numeric(self::packageColumn($package, 'volume_cbm')) && (float) self::packageColumn($package, 'volume_cbm') > 0) {
                return 'cbm';
            }

            return self::region($package) === 'CN' ? 'cbm' : 'cuft';
        }

        $unit = strtolower((string) self::packageColumn($package, 'weight_unit'));

        return $unit === 'kg' ? 'kg' : 'lb';
    }

    private static function labelType(Package $package): string
    {
        $stored = self::packageColumn($package, 'label_type');
        if (trim((string) $stored) !== '') {
            return (string) $stored;
        }

        return (self::region($package) === 'CN' ? 'CN' : 'US').' '.(self::shipmentType($package) === 'SEA' ? 'Sea' : 'Air');
    }

    private static function packageColumn(Package $package, string $column)
    {
        if (! Schema::hasColumn('packages', $column)) {
            return null;
        }

        return $package->{$column} ?? null;
    }

    private static function routingStatus(Package $package): ?string
    {
        $mailbox = trim((string) ($package->mailbox ?? ''));
        $userName = trim((string) ($package->user_name ?? ''));
        if ((int) ($package->courier_id ?? 0) > 0 && $mailbox !== '' && $mailbox !== '0' && $userName !== '') {
            return 'VERIFIED';
        }

        return self::packageColumn($package, 'routing_status');
    }

    private static function decodedPackageImages(Package $package): array
    {
        $value = self::packageColumn($package, 'package_image_paths');
        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? array_values(array_filter($decoded)) : [];
    }

    private static function region(Package $package): ?string
    {
        $warehouseCode = strtoupper(trim((string) self::packageColumn($package, 'warehouse_code')));
        if (in_array($warehouseCode, ['CHINA', 'CN'], true)) {
            return 'CN';
        }
        if (in_array($warehouseCode, ['USA', 'US'], true)) {
            return 'US';
        }

        $text = strtolower(trim(implode(' ', [
            self::packageColumn($package, 'label_type'),
            $package->status_name ?? '',
            $package->courier ?? '',
            $package->freight_type ?? '',
        ])));

        if (str_contains($text, 'china') || str_contains($text, 'cn warehouse') || preg_match('/(^|[^a-z])cn([^a-z]|$)/', $text)) {
            return 'CN';
        }

        if (str_contains($text, 'pompano') || str_contains($text, 'miami') || str_contains($text, 'us warehouse') || preg_match('/(^|[^a-z])us(a)?([^a-z]|$)/', $text)) {
            return 'US';
        }

        return null;
    }

    private static function warehouseStatusName(string $region): string
    {
        return $region === 'CN' ? 'Received at CN Warehouse' : 'Received at US Warehouse';
    }

    private static function packageStatusName(Package $package, ?string $region): string
    {
        $status = trim((string) ($package->status_name ?? ''));
        $statusKey = strtolower($status);

        if ($region && str_contains($statusKey, 'received at') && str_contains($statusKey, 'warehouse')) {
            return self::warehouseStatusName($region);
        }

        return $status;
    }
}
