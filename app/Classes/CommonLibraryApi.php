<?php 
namespace App\Classes;
use Session;
use Redirect;
// use Illuminate\Http\Request;
use Request;
use Cookie;
use URL;
use DB;
use App\CommonModel;
use App\Models\Setting;
use File;
use App\Models\Invoice;

class CommonLibraryApi {
    
    
    public static function deletePackage($base_url,$tracking_no){
        $curl = curl_init();
        $url = $base_url.'/api/checkPackage';
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{"tracking_no":"'.$tracking_no.'"}',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    
}

?>