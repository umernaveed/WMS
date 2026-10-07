<?php

namespace App\Console\Commands;

use App\DealerQualityScore;
use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Courier;
use Illuminate\Support\Facades\DB;
use App\LeadCall;
use Illuminate\Support\Facades\Log;

class UnionOneUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unionOne:today';

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
        $couriers = Courier::get();
        if(count($couriers) > 0){
            foreach($couriers as $row_co){
                DB::table('test')->insert(['name' => $row_co->name,'no_of_user_import' => 0, 'date' => date('Y-m-d H:i:s')]);
                if($row_co->id == 12){
                    $userArray = [];
                    $responce = $this->getApiUsers($row_co->id,0);
                    $result = json_decode($responce,true);
                    //dd($result);
                    if(count($result) > 0){
                        foreach($result as $row){
                            $user = User::where('mailbox',$row['mailbox'])->where('courier_id' , $row_co->id)->first();
                            if($user == null){
                                $row['user_name'] = $row['first_name'].' '.$row['last_name'];
                                $row['courier_id'] = $row_co->id;
                                $userArray[] = $row;
                            }
                        }
                        if(count($userArray) > 0){
                            User::insert($userArray);
                            Log::info($row_co->name." user inserted:");
                        }else{
                            Log::info($row_co->name." No user inserted:");
                        }
                        
                    }else{
                        Log::info($row_co->name." no Server Responce:");
                    }
                }elseif($row_co->id == 15){
                    $responce = $this->getApiUsers($row_co->id,$offset);
                    $result = json_decode($responce,true);
                    //dd($result);
                    if(count($result) > 0){
                        foreach($result as $row){
                            //return $row['account_num'];
                            $user = User::where('mailbox',$row['account_num'])->where('courier_id',$row_co->id)->first();
                            if($user == null){
                                $rowArr = [];
                                $rowArr['mailbox'] = $row['account_num'];
                                $rowArr['first_name'] = $row['name'];
                                $rowArr['user_name'] = $row['name'];
                                $rowArr['courier_id'] = $row_co->id;
                                $userArray[] = $rowArr;
                            }
                        }
                        //dd($userArray);
                        if(count($userArray) > 0){
                            User::insert($userArray);
                            return 'user inserted';
                        }else{
                            return 'No user inserted';
                        }
                    }else{
                        $data['users'] = [];
                    }
                    dd($data);
                }elseif($row_co->id == 16){
                    $responce = $this->getApiUsers($row_co->id,$offset);
                    $result = json_decode($responce,true);
                    if(isset($result['status']) && $result['status'] == 'success'){
                        if(count($result['data']) > 0){
                            foreach($result['data'] as $row){
                                $user = User::where('mailbox',$row['mailbox'])->where('courier_id',$row_co->id)->first();
                                if($user == null){
                                    $rowArr['mailbox'] = $row['mailbox'];
                                    $rowArr['first_name'] = $row['first_name'];
                                    $rowArr['last_name'] = $row['last_name'];
                                    $rowArr['phone'] = $row['phone'];
                                    $rowArr['user_name'] = $row['first_name'].' '.$row['last_name'];
                                    $rowArr['courier_id'] = $row_co->id;
                                    $userArray[] = $rowArr;
                                }
                            }
                            //dd($userArray);
                            if(count($userArray) > 0){
                                User::insert($userArray);
                                return 'user inserted';
                            }else{
                                return 'No user inserted';
                            }
                        }else{
                            $data['users'] = [];
                        }
                        
                    }else{
                        $data['users'] = [];
                    }
                    dd($data);
                }else{
                    $userArray = [];
                    $responce = $this->getApiUsers($row_co->id,0);
                    //Log::info("User Data:".$responce);
                    $result = json_decode($responce,true);
                    if(isset($result['status']) && $result['status'] == 'success'){
                        if(count($result['data']) > 0){
                            foreach($result['data'] as $row){
                                $user = User::where('mailbox',$row['mailbox'])->where('courier_id',$row_co->id)->first();
                                if($user == null){
                                    $row['user_name'] = $row['first_name'].' '.$row['last_name'];
                                    $row['courier_id'] = $row_co->id;
                                    unset($row['user_id']);
                                    $userArray[] = $row;
                                }
                            }
                            if(count($userArray) > 0){
                                User::insert($userArray);
                                DB::table('test')->insert(['name' => $row_co->name,'no_of_user_import' => $row_co->name.' '.count($userArray)." user inserted", 'date' => date('Y-m-d H:i:s')]);
                                Log::info($row_co->name." user inserted:");
                            }else{
                                DB::table('test')->insert(['name' => $row_co->name,'no_of_user_import' => $row_co->name." No user inserted:", 'date' => date('Y-m-d H:i:s')]);
                                Log::info($row_co->name." No user inserted:");
                            }
                        }else{
                            DB::table('test')->insert(['name' => $row_co->name,'no_of_user_import' => $row_co->name." no Server Responce:", 'date' => date('Y-m-d H:i:s')]);
                            Log::info($row_co->name." no Server Responce:");
                        }
                    }else{
                        DB::table('test')->insert(['name' => $row_co->name,'no_of_user_import' => $row_co->name." no Server Responce:", 'date' => date('Y-m-d H:i:s')]);
                        Log::info($row_co->name." no Server Responce:");
                    }
                }
                
            }
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
                  CURLOPT_CUSTOMREQUEST => 'POST',
                  CURLOPT_POSTFIELDS =>'{"offset":"'.$offset.'"}',
                  CURLOPT_HTTPHEADER => array(
                        'api_key: '.$couriers->api_key,
                        'Content-Type: application/json'
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
