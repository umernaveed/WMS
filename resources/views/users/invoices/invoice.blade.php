@extends('users.template')
@section('content')
<?php $getSettings = commonHelper::getSettings(); ?>
<!-- Content Wrapper START -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="container11">
                <div class="card">
                    <div class="pdd-vertical-5 pdd-horizon-10 border bottom print-invisible">
                        <div class="pull-left">
                            <h2>INVOICE</h2>
                        </div>
                        <ul class="list-unstyle list-inline text-right">
                            <li class="list-inline-item">
                                <a href="" class="btn text-gray text-hover display-block padding-10 no-mrg-btm" onclick="window.print();">
                                    <i class="ti-printer text-info pdd-right-5"></i>
                                    <b>Print</b>
                                </a>
                            </li>
<!--                            <li class="list-inline-item">
                                <a href="" class="text-gray text-hover display-block padding-10 no-mrg-btm">
                                    <i class="fa fa-file-pdf-o text-danger pdd-right-5"></i>
                                    <b>Export PDF</b>
                                </a>
                            </li>-->
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="pdd-horizon-30">
                            <div class="mrg-top-15">
                                <div class="inline-block">
                                    <img class="img-responsive" src="{{url('').'/settings/'.$getSettings->site_logo}}" width="100" alt="">
                                    <address class="pdd-left-10 mrg-top-20">
                                        <b class="text-dark">{{$settings->company_name}}</b><br>
                                        <span>{{$settings->local_address}}</span><br>
                                        <span>{{$settings->city.', '.$settings->state.', '.$settings->zip}}</span><br>
                                        <abbr class="text-dark" title="Phone">Phone:</abbr>
                                        <span>{{$settings->phone}}</span>
                                    </address>
                                </div>

                            </div>
                            <div class="row mrg-top-0">
                                <div class="col-md-9 col-sm-9">
                                    <h3 class="pdd-left-10 mrg-top-10">Invoice To:</h3>
                                    <address class="pdd-left-10 mrg-top-10">
                                        <b class="text-dark">{{$user->first_name.' '.$user->last_name}}</b><br>
                                        <span>{{$user->address_1}} </span><br>
                                        <span>Account no : {{$user->mailbox}}</span><br>
                                        <span>Email : {{$user->email}}</span>
                                    </address>
                                </div>
                                <div class="col-md-3 col-sm-3">
                                    <div class="mrg-top-80">
                                        <div class="text-dark text-uppercase inline-block"><b>Invoice No :</b></div>
                                        <div class="pull-right">#{{$invoice->invoice_no}}</div>
                                    </div>
                                    <div class="">
                                        <div class="text-dark text-uppercase inline-block"><b>Date :</b></div>
                                        <div class="pull-right">{{date('d M, Y' , strtotime($invoice->created_at))}}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mrg-top-20">
                                <div class="col-md-12">
                                    <div class="table-overflow">
                                        <table class="table table-hover">
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
                                                <?php
                                                    $count = 0;
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
                                                            <?php
                                        $count ++;
                                        $package_price = $pack->package_price * $usRate;
                                        $service_fee = $pack->service_fee * $usRate;
                                        $custom_fee = $pack->custom_fee * $usRate;
                                        $totalAmount =  $totalAmount + $package_price + $service_fee + $custom_fee;
                                                        ?>
                                                        </tr>
                                    
                                                    @endforeach
                                                @endif
                                               
                                                <tr>
                                                    <td><?php echo $count + 1; ?></td>
                                                    <td align="center">-</td>
                                                    <td>GCT</td>
                                                    <td align="center">-</td>
                                                    <td align="center">-</td>
                                                    <td>
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
                                                                echo $gct = number_format($totalAmount * 16.50 / 100 ,2).' JMD';
                                                            }
                                                        ?>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row mrg-top-30">
                                        <div class="col-md-12">
                                            <div class="pull-right text-right">
                                                <?php //echo $total_gct;
                                               $g_total = $totalAmount + $total_gct;
                                                ?>
                                                <p>Sub - Total amount: {{number_format($g_total ,2)}} JMD</p>
                                                <hr>
                                                <h3><b>Total :</b> {{number_format($g_total ,2)}} JMD</h3>
                                            </div>
                                        </div>
                                    </div>
<!--                                    <div class="row mrg-top-30">
                                        <div class="col-md-12">
                                            <div class="border top bottom pdd-vertical-20">
                                                <p class="text-opacity"><small>In exceptional circumstances, Financial Services can provide an urgent manually processed special cheque. Note, however, that urgent special cheques should be requested only on an emergency basis as manually produced cheques involve duplication of effort and considerable staff resources. Requests need to be supported by a letter explaining the circumstances to justify the special cheque payment.</small></p>
                                            </div>
                                        </div>
                                    </div>-->
                                    <div class="row mrg-vertical-20">
                                        <div class="col-md-6">
                                            <img class="img-responsive text-opacity mrg-top-5" width="100" src="{{url('').'/settings/'.$getSettings->site_logo}}" alt="">
                                        </div>
                                        <div class="col-md-6 text-right">
                                            <small><b>Phone:</b> {{$settings->phone}}</small>
                                            <br>
                                            <small>{{$settings->site_email}}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Content Wrapper END -->
@endsection
