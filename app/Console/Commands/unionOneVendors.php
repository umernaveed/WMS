<?php

namespace App\Console\Commands;

use App\DealerQualityScore;
use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Courier;
use Illuminate\Support\Facades\DB;
use App\LeadCall;
use Illuminate\Support\Facades\Log;

class UnionOneVendors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unionOneVendors:today';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::table('test')->insert(['name' => 'Import Vendors', 'date' => date('Y-m-d H:i:s')]);
        $courier_id = 9;
        $offset = 0;
        $categoryArray = [];
        $responceVendor = $this->GetVendors($courier_id);
        $resultVendor = json_decode($responceVendor,true);
        if(isset($resultVendor['status']) && $resultVendor['status'] == 'success'){
            if(count($resultVendor['data']) > 0){
                foreach($resultVendor['data'] as $row){
                    $package_categories = DB::table('vendors')->where('name',$row['name'])->first();
                    if($package_categories == null){
                        $categoryArray[] = $row;
                    }
                }
                DB::table('vendors')->insert($categoryArray);
                return 'Vendor inserted';
                $data['vendors'] = $resultVendor['data'];
            }else{
                $data['vendors'] = [];
            }
            
        }else{
            $data['vendors'] = [];
        }
        
    }

    public function GetVendors($courier_id = ''){
        try{
            $curl = curl_init();
            $couriers = Courier::where('id' , $courier_id)->first();
            $curl = curl_init();
            $url = $couriers->base_url.'/api/GetVendors';

            curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'GET',
              CURLOPT_HTTPHEADER => array(
                    'api_key: Barrier yS94T5O1gbI4G0pUQuiLRYUJeGhfDM'
              ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
        }catch(\Exception $e){
            return response()->json(['status'=>"success","message"=>'No data found.','data' => []],200);
        }
    }
}
