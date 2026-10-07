<?php

namespace App\Console\Commands;

use App\DealerQualityScore;
use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Courier;
use Illuminate\Support\Facades\DB;
use App\LeadCall;
use Illuminate\Support\Facades\Log;

class UnionOneCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unionOneCategories:today';

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
        DB::table('test')->insert(['name' => 'Import Categories', 'date' => date('Y-m-d H:i:s')]);
        $courier_id = 9;
        $offset = 0;
        $categoryArray = [];
        $responceCategory = $this->GetCategories($courier_id);
        $resultCategory = json_decode($responceCategory,true);
        if(isset($resultCategory['status']) && $resultCategory['status'] == 'success'){
            if(count($resultCategory['data']) > 0){
                foreach($resultCategory['data'] as $row){
                    $package_categories = DB::table('package_categories')->where('title',$row['title'])->first();
                    if($package_categories == null){
                        $categoryArray[] = $row;
                    }
                    
                }
                DB::table('package_categories')->insert($categoryArray);
                return 'category inserted';
                //dd($categoryArray);
                //$data['categories'] = $resultCategory['data'];
            }else{
                $data['categories'] = [];
            }
            
        }else{
            $data['categories'] = [];
        }
        
    }

    public function GetCategories($courier_id = ''){
        try{
            $curl = curl_init();
            $couriers = Courier::where('id' , $courier_id)->first();
            $curl = curl_init();
            $url = $couriers->base_url.'/api/GetCategories';
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
            return  $response;
        }catch(\Exception $e){
            return response()->json(['status'=>"success","message"=>'No data found.','data' => []],200);
        }
    }
}
