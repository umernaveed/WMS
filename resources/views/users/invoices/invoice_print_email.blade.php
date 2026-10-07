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
                            <div class="invoice-title">                     
                            </div>
                            <div class="invoice-info">
                                <div>   
                                    <p>BILL FROM:<br><strong>{{$settings->company_name}}</strong><br>
                                        {{$settings->local_address}}<br>
                                        P:{{$settings->phone}}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="invoice-to" style="float: left;padding-left: 17px;width: 80%;">
                            <div>
                                <h4>BILL TO:</h4>
                                <h2>{{$user->first_name.' '.$user->last_name}}</h2>
                                <p>{{$user->address_1}}<br>
                                    Account no: {{$user->mailbox}}<br>
                                    Email : {{$user->email}}
                                </p>
                            </div>
                            
                        </div>
                        <div style="float: left;margin-top: 20px;">
                                <div class="inv-label">Invoice #: {{$invoice->invoice_no}}</div>
                                <div class="inv-label">Date #: {{date('d M, Y')}}</div>
                        </div>
                        <table class="table table-invoice" >
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th class="text-center">Price Each</th>
                                <th class="text-center">Amount</th>
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
                                            <td>
                                                <h4>@include('common.invoice_package_measure', ['pack' => $pack])</h4>
                                            </td>
                                            <td class="text-right">{{number_format($pack->package_price * $usRate ,2)}} JMD</td>
                                            <td class="text-right">{{number_format($pack->package_price * $usRate , 2)}} JMD</td>
                                        </tr>
                                        <?php $count ++; 
                                            $totalAmount = $totalAmount + $pack->package_price * $usRate;
                                        ?>
                                    @endforeach
                                @endif
                                @if(count($service_type) > 0)
                                    @foreach($service_type as $service)
                                        <tr>
                                            <td><?php echo $count + 1; ?></td>
                                            <td>
                                                <h4> {{$service->name}}</h4>
                                            </td>
                                            <td class="text-right">{{number_format($service->service_fee * $usRate ,2)}} JMD</td>
                                            <td class="text-right">{{number_format($service->service_fee * $usRate ,2)}} JMD</td>
                                        </tr>
                                        <?php $count ++; 
                                            $totalAmount = $totalAmount + $service->service_fee * $usRate;
                                        ?>
                                    @endforeach
                                @endif
                                <tr>
                                        <td><?php echo $count = $count + 1; ?></td>
                                        <td>
                                            <h4>Services Charges</h4>
                                        </td>
                                    <td class="text-right">{{number_format($invoice->service_fee * $usRate , 2)}} JMD</td>
                                    <td class="text-right">{{number_format($invoice->service_fee * $usRate ,2)}} JMD</td>
                                </tr>
                                <?php  $sub_total = $totalAmount + $invoice->service_fee * $usRate; ?>
                                <tr>
                                    <td><?php echo $count = $count + 1; ?></td>
                                    <td>
                                        <h4>GCT</h4>
                                    </td>
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
                                                echo $gct = number_format(0  * $usRate , 2).' JMD';
                                            }else{
                                                 //$total_gct = $sub_total * 16.50 / 100;
                                                echo $gct = number_format($invoice->service_fee * 16.50 / 100  * $usRate ,2).' JMD';
                                            }
                                                
                                                
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-md-8 col-xs-7 payment-method">
                                <h4></h4>
                                
                                <br>
                                <h3 class="inv-label itatic">Thank you for your business</h3>
                            </div>
                            <div class="col-md-4 col-xs-5 invoice-block pull-right">
                                <ul class="unstyled amounts">
                                    
                                    <li>Sub - Total amount : {{number_format($sub_total + $gct ,2)}} JMD</li>
                                    <li class="grand-total">Grand Total : {{number_format($sub_total + $gct ,2)}} JMD</li>
                                </ul>
                            </div>
                        </div>
                        <div style=" clear: both"></div>
                            <hr>
                            @php
                                $invoiceCompanyName = $settings->company_name ?? config('app.name');
                                $invoiceLogo = ! empty($settings->site_logo) ? url('').'/public/settings/'.$settings->site_logo : '';
                            @endphp
                            @if($invoiceLogo)
                                <img src="{{ $invoiceLogo }}" class="CToWUd" width="150">
                            @endif
                            <p style=" text-align: center;">Invoice powered by {{ $invoiceCompanyName }}</p>
                    </div>
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
