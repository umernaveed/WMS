<?php

namespace App\Console\Commands;

use App\DealerQualityScore;
use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Courier;
use Illuminate\Support\Facades\DB;
use App\LeadCall;
use Illuminate\Support\Facades\Log;

class UnionOneTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unionOneTypes:today';

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
        DB::table('test')->insert(['name' => 'Import Types', 'date' => date('Y-m-d H:i:s')]);
        $courier_id = 9;
        $offset = 0;
        $categoryArray = [];
         $responceTypes = $this->GetPackagesTypes($courier_id);
        $resultType = json_decode($responceTypes,true);
        if(isset($resultType['status']) && $resultType['status'] == 'success'){
            if(count($resultType['data']) > 0){
                foreach($resultType['data'] as $row){
                    $package_categories = DB::table('types')->where('TypeCode',$row['TypeCode'])->first();
                    if($package_categories == null){
                        $categoryArray[] = $row;
                    }
                }
                DB::table('types')->insert($categoryArray);
                return 'Types inserted';
                $data['types'] = $resultType['data'];
            }else{
                $data['types'] = [];
            }
            
        }else{
            $data['types'] = [];
        }
    }

    public function GetPackagesTypes($courier_id = ''){
        try{
            $curl = curl_init();
            $couriers = Courier::where('id' , $courier_id)->first();
            $curl = curl_init();
            $url = $couriers->base_url.'/api/GetPackagesTypes';
            curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'GET',
            ));
            
            $response = curl_exec($curl);
            
            curl_close($curl);
            return  $response;
        }catch(\Exception $e){
            return response()->json(['status'=>"success","message"=>'No data found.','data' => []],200);
        }
    }
}
