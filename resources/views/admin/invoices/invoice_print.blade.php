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
    <link href="{{asset('public/admin/bs3/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('public/admin/css/bootstrap-reset.css')}}" rel="stylesheet">
    <link href="{{asset('public/admin/font-awesome/css/font-awesome.css')}}" rel="stylesheet" />

    <!-- Custom styles for this template -->
    <link href="{{asset('public/admin/css/style.css')}}" rel="stylesheet">
    <link href="{{asset('public/admin/css/style-responsive.css')}}" rel="stylesheet" />

    <link href="{{asset('public/admin/css/invoice-print.css')}}" rel="stylesheet" media="all">

    
</head>

<body>

<section id="container" class="print" >

    <!--main content start-->
    <section id="main-content">
        <section class="wrapper">
        <!-- page start-->

        <div class="row">
            <div class="col-md-12">
                <section class="panel">
                    <div class="panel-body invoice">
                        <div class="invoice-header">
                            <div class="invoice-title col-md-3 col-xs-2">
                                    <h1>invoice</h1>
                            </div>
                            <div class="invoice-info col-md-9 col-xs-10">

                                <div class="col-md-8 col-sm-8 pull-left">
                                        
                                        <p>BILL FROM:<br><strong>{{$settings->company_name}}</strong><br>
                                           {{$settings->local_address}}<br>
                                            
                                        </p>
                                    </div>
                                    <div class="col-md-4 col-sm-4 pull-right">
                                        <p> {{$settings->phone}}
                                            </p>
                                    </div>

                            </div>
                        </div>
                        <div class="row invoice-to">
                            <div class="col-md-4 col-sm-4 pull-left">
                                <h4>Invoice To:</h4>
                                <h2>{{$user->first_name.' '.$user->last_name}}</h2>
<!--                                //$settings->local_address-->
                                <p>{{$user->address_1}}<br>
                                    Account no: {{$user->mailbox}}<br>
                                    Email : {{$user->email}}
                                </p>
                            </div>
                            <div class="col-md-4 col-sm-5 pull-right">
                                <div class="row">
                                    <div class="col-md-4 col-sm-5 inv-label">Invoice #</div>
                                    <div class="col-md-8 col-sm-7">{{$invoice->invoice_no}}</div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-4 col-sm-5 inv-label">Date #</div>
                                    <div class="col-md-8 col-sm-7">{{date('d M, Y')}}</div>
                                </div>
                                <br>


                            </div>
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
                                                echo $gct = 0;
                                            }else{
                                                 //$total_gct = $sub_total * 16.50 / 100;
                                                echo $gct = number_format($invoice->service_fee * 16.50 / 100  * $usRate ,2);
                                            }
                                        ?>
                                        JMD
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

                    </div>
                </section>
            </div>
        </div>
        <!-- page end-->
        </section>
    </section>
    <!--main content end-->

</section>

<!-- Placed js at the end of the document so the pages load faster -->

<!--Core js-->
<script src="{{asset('public/admin/js/jquery.js')}}"></script>
<script src="{{asset('public/admin/bs3/js/bootstrap.min.js')}}"></script>
<script class="include" type="text/javascript" src="{{asset('public/admin/js/jquery.dcjqaccordion.2.7.js')}}"></script>
<script src="{{asset('public/admin/js/jquery.scrollTo.min.js')}}"></script>
<script src="{{asset('public/admin/js/jQuery-slimScroll-1.3.0/jquery.slimscroll.js')}}"></script>
<script src="{{asset('public/admin/js/jquery.nicescroll.js')}}"></script>
<!--Easy Pie Chart-->
<script src="{{asset('public/admin/js/easypiechart/jquery.easypiechart.js')}}"></script>
<!--Sparkline Chart-->
<script src="{{asset('public/admin/js/sparkline/jquery.sparkline.js')}}"></script>
<!--jQuery Flot Chart-->
<script src="{{asset('public/admin/js/flot-chart/jquery.flot.js')}}"></script>
<script src="{{asset('public/admin/js/flot-chart/jquery.flot.tooltip.min.js')}}"></script>
<script src="{{asset('public/admin/js/flot-chart/jquery.flot.resize.js')}}"></script>
<script src="{{asset('public/admin/js/flot-chart/jquery.flot.pie.resize.js')}}"></script>


<!--common script init for all pages-->
<script src="{{asset('public/admin/js/scripts.js')}}"></script>

<script type="text/javascript">
    window.print();
</script>

</body>
</html>
