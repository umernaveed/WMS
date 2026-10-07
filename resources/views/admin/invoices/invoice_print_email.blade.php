<!DOCTYPE html>
<html lang="en">
<head>
    <title>Email</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body {
            overflow-x: hidden;
        }
        @media (max-width:1280px) {
            .main_content ul {
                width: 60% !important;
            }
        }
        @media (max-width:1180px) {
            .account_info {
                width: 90% !important;
            }
        }
        @media (max-width:812px) {
            .email-bg {
                width: 90% !important;
            }
        }
        @media (max-width: 767.98px) {
            .main_content ul {
                width: 90% !important;
            }
        }
        @media (max-width: 600px) {
            .email-bg {
                width: 90% !important;
            }
        }
        @media (max-width:480px) {
            .main_content ul {
                width: 98% !important;
                padding: 6px !important;
                font-size: 14px;
            }
        }
        @media (max-width:380px) {
            .small_text {
                font-size: 16px !important;
            }
            .email_content p {
                font-size: 16px !important;
            }
            .main_content li {
                font-size: 14px !important;
            }
        }
        @media (max-width:320px) {
            .small_text {
                font-size: 14px !important;
            }
        }
    </style>
</head>
<body style="background-color: #f1f1f1; font-family: "Lato", sans-serif; overflow-x: hidden; margin: 0;">
    <main>
        <!----- EMAIL-SECTOION-START ------>
        <section class="email-bg"
            style=" width: 60%;margin: 60px auto;box-shadow: 0 2px 8px hsl(0deg 0% 0% / 16%);background: #fff;padding: 20px;">
            <div class="container">
                <header style="border-bottom: 1px solid #ccc;">
                    <img class="logo" src="{{$logo}}" alt="logo" style=" width:20%;">
                </header>
                <div class="email_content" style="width: 100%;">
                    <div style="border-bottom: 1px solid #ccc;">
                        <h4 style="font-size: 18px;margin-bottom: 0;color: #757575cc; ">BILL FROM:</h4>
                        <h3 style="font-size: 26px; margin:0; padding-top: 5px;">{{$company_name}}</h3>
                        <p style="font-size: 20px; margin: 0; padding-top: 5px;">{{$settings->local_address}}</p>
                        <p style="font-size: 18px; margin: 0; padding: 6px 0;">Phone: {{$settings->phone}}</p>
                    </div>
                    <div style="border-bottom: 1px solid #ccc;">
                        <h4 style="font-size: 18px;margin-bottom: 0;color: #757575cc;">BILL TO:</h4>
                        <h3 style="font-size: 26px; margin:0;  padding-top: 5px;">{{$user->name}}</h3>
                        <p style="font-size: 20px; margin: 0; padding-top: 5px;">{{$user->local_address}}</p>
                        <p style="font-size: 20px; margin: 0; padding-top: 5px;">City & State: {{$user->city.' '.$user->state}}</p>

                        <p style="margin: 0; padding: 6px 0;">Email: <a href="mailto:{{$user->email}}">{{$user->email}}</a></p>
                    </div>
                    <div style="border-bottom: 1px solid #ccc;">
                        <p style="font-size: 20px;margin-bottom: 0;color: #757575cc;">Invoice #: <span style="color: #000; font-weight: 600;">{{$invoice->invoice_no}}</span></p>

                        <p style="font-size: 20px; margin: 0; padding: 5px 0; color: #757575cc;">Date #: <span style="color: #000; font-weight: 600;">{{date('d M, Y' , strtotime($invoice->created_at))}}</span></p>
                    </div>
                    <div class="main_content" style="border-bottom: 1px solid #ccc;">
                        @php
                            $count = 0;
                            $totalAmount = 0;
                        @endphp
                        @if(count($invoicesPack) > 0)
                            @foreach($invoicesPack as $pack)
                                @php
                                    $count1 = $count + 1;
                                @endphp
                                <ul style="list-style: none;width: 40%;box-shadow: 0 0 3px rgb(0 0 0 / 16%);padding: 10px;border-radius: 6px;">
                                    <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">
                                        #
                                    </li>
                                    <li style="display: inline-block; width: 68%; padding: 0 0 10px 0;">{{$count1}}</li>
                                    <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px;color:#737373;vertical-align: text-bottom;">
                                        Description
                                    </li>
                                    <li style="display: inline-block; width: 68%; padding: 0 0 10px 0;">{{$pack->package_description}}</li>
                                    <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">Unit Price</li>
                                    <li style="display: inline-block; width: 68%; padding: 0 0 10px 0;">{{number_format(($pack->package_price) * $us_rate ,2)}} USD</li>
                                    <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">Quantity</li>
                                    <li style="display: inline-block; width: 68%; padding: 0 0 10px 0;">{{$pack->quantity}}</li>
                                    <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">
                                        Amount
                                    </li>
                                    <li style="display: inline-block; width: 68%; padding: 0 0 12px 0;">{{number_format(($pack->package_price * $pack->quantity) * $us_rate ,2)}} USD</li>
                                </ul>
                                @php
                                    $count ++;
                                    $package_price = $pack->package_price * $us_rate;
                                    $pk_price = $package_price * $pack->quantity;
                                    $custom_fee = $pack->custom_fee * $us_rate;
                                    $totalAmount = $totalAmount + $pk_price;
                                @endphp
                            @endforeach
                        @endif
                        
                        @if(count($service_type) > 0)
                            @foreach($service_type as $row)
                                @php
                                    $count1 = $count + 1;
                                @endphp
                                <ul style="list-style: none;width: 40%;box-shadow: 0 0 3px rgb(0 0 0 / 16%);padding: 10px;border-radius: 6px;">
                                    <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">
                                        #
                                    </li>
                                    <li style="display: inline-block; width: 68%; padding: 0 0 10px 0;">{{$count1}}</li>
                                    <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px;color:#737373;vertical-align: text-bottom;">
                                        Description
                                    </li>
                                    <li style="display: inline-block; width: 68%; padding: 0 0 10px 0;">{{$row->name}}</li>
                                    <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">Unit Price</li>
                                    <li style="display: inline-block; width: 68%; padding: 0 0 10px 0;">{{number_format($row->service_fee * $us_rate ,2)}} USD</li>
                                    <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">Quantity</li>
                                    <li style="display: inline-block; width: 68%; padding: 0 0 10px 0;">-</li>
                                    <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">
                                        Amount
                                    </li>
                                    <li style="display: inline-block; width: 68%; padding: 0 0 12px 0;">{{number_format($row->service_fee * $us_rate ,2)}} USD</li>
                                </ul>
                                @php
                                    $count ++;
                                    $totalAmount = $totalAmount + $row->service_fee * $us_rate;
                                @endphp
                            @endforeach
                        @endif


                        <ul style="list-style: none;width: 40%;box-shadow: 0 0 3px rgb(0 0 0 / 16%);padding: 10px;border-radius: 6px;">
                            <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">
                                #
                            </li>
                            <li style="display: inline-block; width: 68%; padding: 0 0 10px 0;">3</li>
                            <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px;color:#737373;vertical-align: text-bottom;">
                                Description
                            </li>
                            <li style="display: inline-block; width: 68%; padding: 0 0 10px 0;">GCT</li>
                            <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">
                                Price
                            </li>
                            @php
                                if($invoice->gct == 0){
                                    $gct_label = '0%';
                                }else{
                                    $gct_label = '7%';
                                }
                            @endphp
                            <li style="display: inline-block; width: 68%; padding: 0 0 10px 0;">{{$gct_label}}</li>
                            <li style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">
                                Amount
                            </li>
                            @php
                                if($invoice->gct == 0){
                                    $total_gct = 0  * $us_rate;
                                    $gct = number_format(0  , 2).' USD';
                                }else{
                                    $total_gct = $totalAmount * 7 / 100;
                                    $gct = number_format($totalAmount * 7 / 100  ,2).' USD';
                                }
                            @endphp
                            <li style="display: inline-block; width: 68%; padding: 0 0 12px 0;">{{$gct}}</li>
                        </ul>
                        <ul style="list-style: none;width: 40%;box-shadow: 0 0 3px rgb(0 0 0 / 16%);padding: 10px;border-radius: 6px;">
                            <li
                                style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#000;">
                                Sub- Total amount
                            </li>
                            <li style="display: inline-block; width: 68%; padding: 0 0 10px 0; ">{{number_format($totalAmount + $total_gct ,2)}} USD</li>
                            <li
                                style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#000;">
                                Grand Total
                            </li>
                            <li style="display: inline-block; width: 68%; padding: 0 0 12px 0;">{{number_format($totalAmount + $total_gct ,2)}} USD</li>
                        </ul>
                    </div>
                    <div>
                        <h4 style="color: #636363;">Thank You for your business</h4>
                        <h4 style="color: #636363;">Invoice powered by {{$company_name}}</h4>
                    </div>
                </div>

            </div>
            <footer>
                <h4 style="border-top: 1px solid #ccc; font-size: 13px; color: #8c8c8c;">Thanks for your support Enjoy
                    your shopping experience. </h4>
                <p style="font-size: 13px; color: #8c8c8c;">Best Wishes</p>
                <p style="font-size: 13px; color: #8c8c8c;">{{$company_name}} Client Services</p>
            </footer>
            </div>
        </section>
        <!----- EMAIL-SECTOION-END ------>
    </main>
</body>
</html>