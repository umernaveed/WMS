<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Session;
use Validator;
use image;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use App\Models\User;
use App\Models\Courier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CourierControllor extends Controller
{

    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }



    public function index(){
        $data['title'] = 'Manage Couriers';
        $data['class'] = 'Couriers';
        $data['table'] = 'Manage Couriers';
        $couriers = Courier::get();
        $data['couriers'] = $couriers;
        
        return view('admin/couriers/list' , $data);
    }
    
   public function importUser(Request $request)
{
    $courierId = (int) $request->id;
    $courier = Courier::where('id', $courierId)->first();

    if (! $courier) {
        return ["status" => false, "message" => "Invalid courier"];
    }

    if (! empty($courier->saas_tenant_id)) {
        $stats = $this->importCmsUsersForCourier($courier);

        return [
            "status" => $stats['failed'] === 0,
            "message" => "CMS import completed. Inserted {$stats['inserted']}, updated {$stats['updated']}, failed {$stats['failed']}.",
            "total_inserted" => $stats['inserted'],
            "total_updated" => $stats['updated'],
            "total_failed" => $stats['failed'],
            "errors" => $stats['errors'],
        ];
    }

    $offset = 0;
    $limit  = 5000;
    $totalInserted = 0;
    $totalUpdated  = 0;

    // 🔹 Load existing users once
    $existingUsers = User::where('courier_id', $courierId)
        ->get(['mailbox', 'outlet_id'])
        ->keyBy('mailbox');

    do {

        $response = $this->getApiUsers($courierId, $offset);
        $result   = json_decode($response, true);

        if (!isset($result['status']) || $result['status'] !== 'success') {
            break;
        }

        $apiUsers = $result['data'] ?? [];

        if (empty($apiUsers)) {
            break;
        }

        $insertData = [];
        $updateData = [];

        foreach ($apiUsers as $row) {

            $mailbox     = $row['mailbox'];
            $apiOutletId = $row['outlet_id'] ?? null;

            if (!isset($existingUsers[$mailbox])) {

                // 🔹 Insert new user
                $insertData[] = [
                    'courier_id' => $courierId,
                    'mailbox'    => $mailbox,
                    'email'      => $row['email'] ?? null,
                    'first_name' => $row['first_name'],
                    'last_name'  => $row['last_name'],
                    'user_name'  => $row['first_name'].' '.$row['last_name'],
                    'outlet_id'  => $apiOutletId,
                ];

                // mark as existing
                $existingUsers[$mailbox] = (object)['outlet_id' => $apiOutletId];

            } else {

                $currentOutletId = $existingUsers[$mailbox]->outlet_id;

                // 🔹 Update only if existing outlet_id is NULL or 0
                if (
                    ($currentOutletId === null || $currentOutletId == 0)
                    && !empty($apiOutletId)
                ) {
                    $updateData[$mailbox] = $apiOutletId;
                }
            }
        }

        // 🔹 Bulk Insert
        if (!empty($insertData)) {
            collect($insertData)
                ->chunk(1000)
                ->each(function ($chunk) use (&$totalInserted) {
                    User::insert($chunk->toArray());
                    $totalInserted += $chunk->count();
                });
        }

        // 🔹 Bulk Update (FIXED FOR STRING VALUES)
        if (!empty($updateData)) {

            $mailboxes = array_keys($updateData);

            $caseSql = "CASE mailbox ";

            foreach ($updateData as $mailbox => $outletId) {

                $safeMailbox  = addslashes($mailbox);
                $safeOutletId = addslashes($outletId);

                $caseSql .= "WHEN '{$safeMailbox}' THEN '{$safeOutletId}' ";
            }

            $caseSql .= "END";

            User::where('courier_id', $courierId)
                ->whereIn('mailbox', $mailboxes)
                ->where(function ($q) {
                    $q->whereNull('outlet_id')
                      ->orWhere('outlet_id', 0);
                })
                ->update([
                    'outlet_id' => DB::raw($caseSql)
                ]);

            $totalUpdated += count($updateData);
        }

        $offset = $result['offset'] ?? 0;

        Log::info('Import Progress', [
            'offset'         => $offset,
            'received'       => count($apiUsers),
            'inserted'       => count($insertData),
            'updated'        => count($updateData),
            'total_inserted' => $totalInserted,
            'total_updated'  => $totalUpdated,
        ]);

    } while (count($apiUsers) === $limit);

    return [
        "status" => true,
        "message" => "Import completed successfully",
        "total_inserted" => $totalInserted,
        "total_updated"  => $totalUpdated
    ];
}

    public function importAllCmsUsers(Request $request)
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(600);
        }

        $courierStats = $this->importCmsCouriers();

        $totals = [
            'inserted' => 0,
            'updated' => 0,
            'failed' => 0,
            'couriers' => 0,
            'couriers_inserted' => $courierStats['inserted'],
            'couriers_updated' => $courierStats['updated'],
            'couriers_failed' => $courierStats['failed'],
            'errors' => [],
        ];

        foreach ($courierStats['errors'] as $error) {
            if (count($totals['errors']) < 10) {
                $totals['errors'][] = 'Couriers: '.$error;
            }
        }

        $couriers = Courier::whereNotNull('saas_tenant_id')
            ->where('saas_tenant_id', '>', 0)
            ->orderBy('id')
            ->get();

        if ($couriers->isEmpty()) {
            return ["status" => false, "message" => "No CMS-linked couriers found.", "errors" => $totals['errors']];
        }

        foreach ($couriers as $courier) {
            $stats = $this->importCmsUsersForCourier($courier);
            $totals['inserted'] += $stats['inserted'];
            $totals['updated'] += $stats['updated'];
            $totals['failed'] += $stats['failed'];
            $totals['couriers']++;

            foreach ($stats['errors'] as $error) {
                if (count($totals['errors']) < 10) {
                    $totals['errors'][] = $courier->name.': '.$error;
                }
            }
        }

        return [
            "status" => $totals['failed'] === 0 && $totals['couriers_failed'] === 0,
            "message" => "CMS mass import completed. Couriers: inserted {$totals['couriers_inserted']}, updated {$totals['couriers_updated']}. Customers: processed {$totals['couriers']} courier(s), inserted {$totals['inserted']}, updated {$totals['updated']}, failed {$totals['failed']}.",
            "couriers_inserted" => $totals['couriers_inserted'],
            "couriers_updated" => $totals['couriers_updated'],
            "couriers_failed" => $totals['couriers_failed'],
            "total_inserted" => $totals['inserted'],
            "total_updated" => $totals['updated'],
            "total_failed" => $totals['failed'],
            "errors" => $totals['errors'],
        ];
    }

    private function importCmsCouriers(): array
    {
        $offset = 0;
        $limit = 5000;
        $stats = ['inserted' => 0, 'updated' => 0, 'failed' => 0, 'errors' => []];

        do {
            $result = $this->getCmsCouriers($offset, $limit);
            if (($result['status'] ?? null) !== 'success') {
                $stats['failed']++;
                $stats['errors'][] = $result['message'] ?? 'CMS did not return courier data.';
                break;
            }

            $couriers = $result['data'] ?? [];
            if (empty($couriers)) {
                break;
            }

            foreach ($couriers as $row) {
                $tenantId = (int) ($row['tenant_id'] ?? 0);
                if ($tenantId <= 0) {
                    $stats['failed']++;
                    if (count($stats['errors']) < 10) {
                        $stats['errors'][] = 'Skipped courier with missing tenant ID.';
                    }
                    continue;
                }

                $existing = Courier::where('saas_tenant_id', $tenantId)->first();
                if (! $existing && ! empty($row['wms_courier_id'])) {
                    $existing = Courier::where('id', (int) $row['wms_courier_id'])->first();
                }
                if (! $existing && ! empty($row['email'])) {
                    $existing = Courier::where('email', $row['email'])->first();
                }

                $created = ! $existing;
                $courier = $existing ?: new Courier();
                $password = $courier->hint_password ?: $this->randomPassword();
                $courier->fill([
                    'saas_tenant_id' => $tenantId,
                    'saas_last_synced_at' => date('Y-m-d H:i:s'),
                    'name' => $row['company_name'] ?? 'CMS Courier '.$tenantId,
                    'email' => $row['email'] ?: 'cms-courier-'.$tenantId.'@wms.local',
                    'phone' => $row['phone'] ?? '',
                    'local_address' => $row['address'] ?? '',
                    'city' => $row['city'] ?? '',
                    'state' => $row['parish'] ?? '',
                    'base_url' => $row['portal_url'] ?? '',
                    'is_courier' => 'Yes',
                    'status' => in_array($row['status'] ?? 'active', ['active', 'trial'], true) ? '0' : '1',
                    'hint_password' => $password,
                ]);

                if (! $courier->exists) {
                    $courier->api_key = $this->generate_password(30);
                    $courier->password = Hash::make($password);
                    $courier->awb_number = 'OL.'.random_int(1000000000, 9999999999);
                    $courier->created_at = date('Y-m-d H:i:s');
                }

                $courier->updated_at = date('Y-m-d H:i:s');
                $courier->save();

                $created ? $stats['inserted']++ : $stats['updated']++;
            }

            $offset = (int) ($result['offset'] ?? ($offset + count($couriers)));
        } while (! empty($result['has_more']) || count($couriers) === $limit);

        return $stats;
    }

    private function importCmsUsersForCourier(Courier $courier): array
    {
        $offset = 0;
        $limit = 5000;
        $stats = ['inserted' => 0, 'updated' => 0, 'failed' => 0, 'errors' => []];

        do {
            $result = $this->getCmsUsers((int) $courier->id, (int) $courier->saas_tenant_id, $offset, $limit);
            if (($result['status'] ?? null) !== 'success') {
                $stats['failed']++;
                $stats['errors'][] = $result['message'] ?? 'CMS did not return customer data.';
                break;
            }

            $users = $result['data'] ?? [];
            if (empty($users)) {
                break;
            }

            $batchStats = $this->upsertCmsUsers($users, (int) $courier->id);
            $stats['inserted'] += $batchStats['inserted'];
            $stats['updated'] += $batchStats['updated'];
            $stats['failed'] += $batchStats['failed'];
            foreach ($batchStats['errors'] as $error) {
                if (count($stats['errors']) < 10) {
                    $stats['errors'][] = $error;
                }
            }

            $offset = (int) ($result['offset'] ?? ($offset + count($users)));
        } while (! empty($result['has_more']) || count($users) === $limit);

        Log::info('CMS users imported into WMS', [
            'courier_id' => $courier->id,
            'courier_name' => $courier->name,
            'inserted' => $stats['inserted'],
            'updated' => $stats['updated'],
            'failed' => $stats['failed'],
        ]);

        return $stats;
    }

    private function getCmsCouriers(int $offset, int $limit): array
    {
        return $this->postCms('/api/wms/couriers/export', [
            'offset' => $offset,
            'limit' => $limit,
        ]);
    }

    private function getCmsUsers(int $courierId, int $tenantId, int $offset, int $limit): array
    {
        return $this->postCms('/api/wms/customers/export', [
            'tenant_id' => $tenantId,
            'courier_id' => $tenantId > 0 ? null : $courierId,
            'offset' => $offset,
            'limit' => $limit,
        ]);
    }

    private function postCms(string $path, array $payload): array
    {
        $baseUrl = rtrim((string) config('services.courier_saas.url'), '/');
        $token = (string) config('services.courier_saas.token');

        if ($baseUrl === '' || $token === '') {
            return ['status' => 'error', 'message' => 'COURIER_SAAS_URL or WMS_SYNC_TOKEN is missing.'];
        }

        try {
            $response = Http::timeout((int) config('services.courier_saas.timeout', 10) * 6)
                ->acceptJson()
                ->withToken($token)
                ->post($baseUrl.$path, array_filter($payload, fn ($value) => $value !== null));

            if (! $response->successful()) {
                return ['status' => 'error', 'message' => 'CMS HTTP '.$response->status().': '.$response->body()];
            }

            return $response->json() ?: ['status' => 'error', 'message' => 'CMS returned an empty response.'];
        } catch (\Throwable $exception) {
            Log::warning('CMS import request failed', [
                'path' => $path,
                'payload' => $payload,
                'message' => $exception->getMessage(),
            ]);

            return ['status' => 'error', 'message' => $exception->getMessage()];
        }
    }

    private function upsertCmsUsers(array $users, int $fallbackCourierId): array
    {
        $stats = ['inserted' => 0, 'updated' => 0, 'failed' => 0, 'errors' => []];

        foreach ($users as $row) {
            $mailbox = strtoupper(trim((string) ($row['mailbox'] ?? '')));
            $courierId = (int) ($row['courier_id'] ?? 0) ?: $fallbackCourierId;
            if ($mailbox === '' || $courierId <= 0) {
                $stats['failed']++;
                if (count($stats['errors']) < 10) {
                    $stats['errors'][] = 'Skipped customer with missing courier or mailbox.';
                }
                continue;
            }

            $name = trim((string) ($row['name'] ?? ''));
            $firstName = trim((string) ($row['first_name'] ?? ''));
            $lastName = trim((string) ($row['last_name'] ?? ''));
            if ($firstName === '' && $lastName === '' && $name !== '') {
                [$firstName, $lastName] = $this->splitName($name);
            }

            $values = [
                'courier_id' => $courierId,
                'outlet_id' => $row['outlet_id'] ?? '0',
                'mailbox' => $mailbox,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'user_name' => $name ?: trim($firstName.' '.$lastName),
                'email' => filter_var($row['email'] ?? null, FILTER_VALIDATE_EMAIL) ? $row['email'] : '',
                'phone' => $row['phone'] ?? '',
                'address_1' => $row['address'] ?? '',
                'city' => $row['city'] ?? '',
                'state' => $row['state'] ?? '',
                'status' => ($row['active'] ?? true) ? '1' : '0',
            ];

            $userId = DB::table('users')
                ->where('courier_id', $courierId)
                ->where('mailbox', $mailbox)
                ->value('user_id');

            if ($userId) {
                DB::table('users')->where('user_id', $userId)->update($values);
                $stats['updated']++;
            } else {
                DB::table('users')->insert($values);
                $stats['inserted']++;
            }
        }

        return $stats;
    }

    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2);

        return [$parts[0] ?? '', $parts[1] ?? ''];
    }

    private function randomPassword(): string
    {
        return bin2hex(random_bytes(8));
    }

    
    public function getApiUsers($courier_id = '',$offset = ''){
        try{
            
            $couriers = Courier::where('id' , $courier_id)->first();
            if(!$couriers || empty($couriers->base_url)){
                return response()->json(['status'=>"success","message"=>'No data found.','data' => []],200);
            }

            $headers = ['Content-Type: application/json'];
            if(!empty($couriers->api_key)){
                $headers[] = 'api_key: '.$couriers->api_key;
            }

            $url = rtrim($couriers->base_url, '/').'/api/GetUsers';
            $curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS => json_encode(['offset' => $offset]),
              CURLOPT_HTTPHEADER => $headers,
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
            
        }catch(\Exception $e){
            return response()->json(['status'=>"success","message"=>'No data found.','data' => []],200);
        }
    }

    public function add(Request $request){
        $data['title'] = 'Add Courier';
        $data['class'] = 'Couriers';
        $data['table'] = 'Add Courier';
        if ($request->isMethod('get')) {
                return view('admin/couriers/new' , $data);
        }else{
            $validator = Validator::make($request->all(), [
                'email' => 'required|unique:couriers|max:255',
                'password' => 'required|same:confirm_password',
                'confirm_password' => 'required',
            ]);
            if ($validator->fails()) {
                return redirect('admin/courier/create')
                                ->withInput()
                                ->withErrors($validator);
            } else {
                if ($request->hasFile('logo')) {
                    $image_name = '';
                    $imageTempName = $request->file('logo')->getPathname();
                    $venue_img_extension = $request->logo->extension();
                    $imageName = uniqid() . '.' . $venue_img_extension;
                    $path = public_path() . '/couriers/';
                    $request->file('logo')->move($path, $imageName);
                    $image = $imageName;
                } else {
                    $image = '';
                }
                Courier::create([
                    'name' => $request->name,
                    'city' => $request->city,
                    'zip' => $request->zip,
                    'phone' => $request->phone,
                    'state' => $request->state,
                    'local_address' => $request->local_address,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'hint_password' => $request->password,
                    'base_url' => $request->base_url,
                    'description' => $request->description,
                    'is_courier' => $request->is_courier,
                    'logo' => $image,
                    //'rate' => $request->rate,
                    'awb_number' => 'OL.'.random_int(1000000000, 9999999999),
                    'api_key' => $this->generate_password(30)
                ]);
                Session::flash('success_msg', 'Courier has been created successfully.');
                return redirect('admin/couriers');
            }
        }
    }
    public function edit(Request $request, $id = '') {
        $data['title'] = 'Edit Courier';
        $data['class'] = 'Couriers';
        $data['table'] = 'Edit Courier';
        if ($request->isMethod('get')) {
                $data['couriers'] = Courier::where('id' , $id)->first();
                //dd($data['couriers']);
                return view('admin/couriers/edit' , $data);
        }else{
            if($request->email != $request->old_email){
                $validator = Validator::make($request->all(), [
                                'email' => 'required|unique:couriers|max:255',
                            ]);
            }else{
                                $validator = Validator::make($request->all(), [
                               // 'email' => 'required|unique:couriers|max:255',
                            ]);
            }
            
            if ($validator->fails()) {
                return redirect('admin/couriers/edit/'.$id)
                                ->withInput()
                                ->withErrors($validator);
            } else {
                if ($request->hasFile('logo')) {
                    $image_name = '';
                    $imageTempName = $request->file('logo')->getPathname();
                    $venue_img_extension = $request->logo->extension();
                    $imageName = uniqid() . '.' . $venue_img_extension;
                    $path = public_path() . '/couriers/';
                    $request->file('logo')->move($path, $imageName);
                    $image = $imageName;
                } else {
                    $image = '';
                    $courier_img = Courier::where('id', $id)->first();
                    if($courier_img){
                        $image = $courier_img->logo;
                    }
                }

                Courier::where('id', $id)
                ->update([
                    'city' => $request->city,
                    'zip' => $request->zip,
                    'phone' => $request->phone,
                    'state' => $request->state,
                    'local_address' => $request->local_address,
                    'name' => $request->name,
                    'email' => $request->email,
                    'description' => $request->description,
                    'base_url' => $request->base_url,
                    'is_courier' => $request->is_courier,
                    //'rate' => $request->rate,
                    'logo' => $image,
                    //'api_key' => 'Barrier ' .$this->generate_password(30)
                    //'awb_number' => 'awb.'.random_int(1000000000, 9999999999),

                ]);
                Session::flash('success_msg', 'Courier has been updated successfully.');
                return redirect('admin/couriers');
            }
        }
    }

    public function delete(){
        Courier::where(["id"=>request()->id])->delete();
        Session::flash('success_msg', 'Courier has been deleted successfully.');
        return redirect('admin/couriers');
    }

    public function resetPassword(Request $request, $id = '')
    {
        $courier = Courier::where('id', (int) $id)->first();

        if(! $courier){
            return response()->json([
                'status' => false,
                'message' => 'Courier not found.',
            ], 404);
        }

        $password = $this->randomPassword();
        $courier->password = Hash::make($password);
        $courier->hint_password = $password;
        $courier->updated_at = date('Y-m-d H:i:s');
        $courier->save();

        return response()->json([
            'status' => true,
            'message' => 'Courier password has been regenerated.',
            'courier' => $courier->name,
            'password' => $password,
        ]);
    }
    
    function generate_password($length) {
        $chars = "abcdefghijklmnopqrstusdfguyutyuhgfvwxyzABCDEFGHIJKLMNOPSDGFHJUYTYRRQRSTUVWXYZ01234567893564786709864534";
        return substr(str_shuffle($chars), 0, $length);
    }
}
