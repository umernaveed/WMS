<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="ThemeBucket">
    <link rel="shortcut icon" href="images/favicon.png">

    <title>Invoice</title>

    <!--Core CSS -->
    

    
    <style>
        .print #main-content{
    margin-left: 0;
}

#main-content {
    margin-left: 0;
    width: 100%;
    margin-top: 0px;
    padding: 0;
}

.wrapper {
    margin-top:0px ;
}

.invoice-to h2, .invoice-to {
    margin-top: 15px;
}

.invoice-header {
    margin-bottom: 10px;
}

.invoice-title {
    background: none;
    padding: 0;
}

.invoice-title h1 {
    font-size: 22px;
    color: #32323a;
    margin-top: 30px;
    float: left;
}

h1 {
    font-size: 20px;
}
h3 {
    font-size: 15px;
}

.inv-label {
    margin: 0;
}

ul.amounts li {
    padding: 3px;
}

.table-invoice {
    margin-top: 10px;
    width: 100%;
}

.table {
    margin-bottom: 5px;
}

.invoice-info p {
    font-size: 10px;
    line-height: normal;
}

.logo-print {
    float: right;
    margin:-35px 0 0 250px;
}

.invoice-header {
    border: none;
}


table {  
    border-collapse: collapse;
}
th, td {
    padding: 10px 10px;
    text-align: center;
    border-bottom: 1px solid #eee;
}
h4 {

    margin: 0px;

}
.payment-method {

    float: left;
    width: 70%;
    padding-left: 20px;

}
.invoice-block.pull-right {

    float: left;
    width: 28%;

}
li {

    list-style: none;

}
.grand-total {

    background: #ccc;

}
.CToWUd {

    margin: 0 auto;
    display: block;

}
    </style>
    
</head>

<body>

<section id="container" class="print" >

    <!--main content start-->
    <section id="main-content">
        <section class="wrapper">
        <!-- page start-->

        <div class="row">
            <div class="col-md-12">
                <section class="panel" style="border: 1px solid #ccc;padding: 20px;margin: 10px;float: left; width: 96%;">
                    <div class="panel-body invoice">
                        <div class="invoice-header">
                            
                            <table>
                                <tr>
                                    <td style=" width: 70%; text-align: left; border-bottom: none;">
                                        @if(! empty($settings->site_logo))
                                            <img src="{{ url('').'/public/settings/'.$settings->site_logo }}" class="CToWUd" style="width: 100px; height: 70px">
                                        @endif
                                    </td>
                                    <td align="right" colspan="17" style=" width: 70% ; text-align:  left; border-bottom: none;">BILL FROM:<br><strong>{{$settings->company_name}}</strong><br>
                                        {{$settings->local_address}}<br>
                                        Phone:{{$settings->phone}}</td>
                                </tr>
                            </table>
<!--                            <div class="invoice-info">
                                <div>   
                                    <p>BILL FROM:<br><strong>{{$settings->company_name}}</strong><br>
                                        {{$user->address_1}}<br>
                                        {{$user->address_2}}<br>
                                        Phone:{{$settings->phone}}
                                    </p>
                                </div>
                            </div>-->
                        </div>
                        <hr>
                        <table>
                                <tr>
                                    <td style=" width: 70%; text-align: left; border-bottom: none;">
                                        <strong>BILL TO:</strong><br>
                                        <strong>{{$user->first_name.' '.$user->last_name}}</strong><br>
                                        {{$user->address_1}}<br>
                                        Account no: {{$user->mailbox}}<br>
                                        Email : {{$user->email}}
                                    </td>
                                    <td width="50%" style=" text-align: left; border-bottom: none;"></td>
                                    <td width="50%" style=" text-align: left; border-bottom: none;"></td>
                                    <td width="50%" style=" text-align: left; border-bottom: none;"></td>
                                    <td width="50%" style=" text-align: left; border-bottom: none;"> </td>
                                    <td width="70%" style=" text-align: left; border-bottom: none;"></td>
                                    <td width="70%" style=" text-align: left; border-bottom: none;"></td>
                                    <td width="70%" style=" text-align: left; border-bottom: none;"></td>
                                    <td width="70%" style=" text-align: left; border-bottom: none;"></td>
                                    <td width="70%" style=" text-align: left; border-bottom: none;"></td>
                                    <td width="70%" style=" text-align: left; border-bottom: none;"></td>
                                    <td align="right" style=" text-align: left; border-bottom: none;">
                                        <div class="inv-label">Invoice #: {{$invoice->invoice_no}}</div>
                                        <div class="inv-label">Date #: {{date('d M, Y')}}</div>
                                    </td>
                                </tr>
                            </table>
                        <hr>
<!--                        <div class="invoice-to" style="padding-left: 17px;width: 80%;">
                            <div>
                                <h4>BILL TO:</h4>
                                <h2>{{$user->first_name.' '.$user->last_name}}</h2>
                                <p>{{$user->address_1}}<br>
                                    Account no: {{$user->mailbox}}<br>
                                    Email : {{$user->email}}
                                </p>
                            </div>
                            
                        </div>
                        <div style="margin-top: 20px;">
                                <div class="inv-label">Invoice #: {{$invoice->invoice_no}}</div>
                                <div class="inv-label">Date #: {{date('d M, Y')}}</div>
                        </div>-->
                        <table class="table table-invoice" >
                            <thead>
                            <tr>
                                <th>#</th>
                                <th align="center">HAWB.</th>
                                <th>Description</th>
                                <th>Custom Fee</th>
                                <th>Service Fee</th>
                                <th>Freight</th>
                                <th class="text-right">Amount</th>
                            </tr>
                            </thead>
                            <tbody>
                                <?php $count = 0; 
                                      $totalAmount = 0;
                                ?>
                                @if(count($invoicesPack) > 0)
                                    @foreach($invoicesPack as $pack)
                                        <tr>
                                            <td><?php echo $count + 1; ?></td>
                                                            <td align="center">{{$pack->manifest_no}}</td>
                                                            <td>@include('common.invoice_package_measure', ['pack' => $pack])</td>
                                                            <td>{{number_format($pack->custom_fee  * $usRate,2)}} JMD</td>
                                                            <td>{{number_format($pack->service_fee  * $usRate,2)}} JMD</td>
                                                            <td>{{number_format($pack->package_price * $usRate ,2)}} JMD</td>
                                                            <td class="text-right">{{number_format(($pack->package_price + $pack->custom_fee + $pack->service_fee) * $usRate ,2)}} JMD</td>
                                        <?php $count ++; 
                                            $package_price = $pack->package_price * $usRate;
                                        $service_fee = $pack->service_fee * $usRate;
                                        $custom_fee = $pack->custom_fee * $usRate;
                                        $totalAmount =  $totalAmount + $package_price + $service_fee + $custom_fee;
                                        ?>
                                    @endforeach
                                @endif
                                <tr>
                                    <td><?php echo $count = $count + 1; ?></td>
                                    <td align="center">-</td>
                                    <td>GCT</td>
                                    <td align="center">-</td>
                                    <td align="center">-</td>
                                    <td class="text-right">
                                        @if($invoice->gct == 0)
                                            0%
                                        @else
                                            16.50%
                                        @endif
                                        
                                    </td>
                                    <td class="text-right">
                                        <?php 
                                            if($invoice->gct == 0){
                                                $total_gct = 0  * $usRate;
                                                echo $gct = number_format(0  * $usRate , 2).' JMD';
                                            }else{
                                                 $total_gct = $totalAmount * 16.50 / 100;
                                                echo $gct = number_format($invoice->service_fee * 16.50 / 100  * $usRate ,2).' JMD';
                                            }   
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style=" text-align: left;">Thank you for your business</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    @if( isset( $total_gct ) )
                                    <td style=" text-align: right; background: #ccc;">Grand Total : {{number_format($totalAmount + $total_gct ,2)}} JMD</td>
                                    @else
                                    <td style=" text-align: right; background: #ccc;">Grand Total : {{number_format($totalAmount, 2)}} JMD</td>
                                    @endif
                                </tr>
                                <tr>
                                    <td style=" text-align: left;"></td>
                                    <td>
                                        
                                    </td>
                                     
<!--                                    <td class="text-right">${{$invoice->SCF}}</td>-->
                                    <td style=" text-align: right;"></td>
                                </tr>
                            </tbody>
                        </table>

                </section>
            </div>
        </div>
        <!-- page end-->
        </section>
    </section>
    <!--main content end-->

</section>
</body>
</html>
