<?php

namespace App\Console\Commands;

use App\DealerQualityScore;
use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Courier;
use Illuminate\Support\Facades\DB;
use App\LeadCall;
use Illuminate\Support\Facades\Log;

class ShiprightUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shipright:today';

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
        DB::table('test')->insert(['name' => 'shipright', 'date' => date('Y-m-d H:i:s')]);
        $userArray = [];
        $responce = $this->getApiUsers(12,0);
        $result = json_decode($responce,true);
        //dd($result);
        if(count($result) > 0){
            foreach($result as $row){
                $user = User::where('mailbox',$row['mailbox'])->first();
                if($user == null){
                    $row['user_name'] = $row['first_name'].' '.$row['last_name'];
                    $row['courier_id'] = $courier_id;
                    $userArray[] = $row;
                }
                
                
            }
            if(count($userArray) > 0){
                User::insert($userArray);
                Log::info("shipright user inserted:");
            }else{
                Log::info("shipright No user inserted:");
            }
            
        }else{
            Log::info("shipright no Server Responce:");
        }
    }

    public function getApiUsers($courier_id = '',$offset = ''){
        try{
            $couriers = Courier::where('id' , $courier_id)->first();
            $url = $couriers->base_url.'/api/GetUsers';
            $curl = curl_init();
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
                    'TOKEN: NpEhna29fhjwGccsZr6f0g=='
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
