<!DOCTYPE html>
<html lang="en">

<head>
    <title>Email</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body{
            overflow-x: hidden;
        }
        @media (max-width:1180px) {
            
        }

        @media (max-width: 767.98px) {
           
        }
        @media (max-width:812px) {
    .email-bg{
               width: 90%  !important;
           }
}
.main_content ul {
    width: 80% !important;
}
        @media (max-width: 600px) {
            .main_content ul {
    width: 96% !important;
}
           .email-bg{
               width: 90%  !important;
               padding: 10px !important;
           }
        }
             @media (max-width:480px){
            .main_content ul {
    width: 98% !important;
    padding: 6px !important;
    font-size: 14px;
}
.email_content p {
    font-size: 16px !important;
}
.email-bg{
               width: 93%  !important;
               padding: 10px !important;
           }
             }
        @media (max-width:380px){
          .small_text{
              font-size: 16px  !important;
          }  
          .email_content p {
    font-size: 16px !important;
}
.big_head {
    font-size: 13px !important;
    width: 25% !important;
}
        }
        @media (max-width:380px){
          .small_text{
              font-size: 12px  !important;
          }  
        }
    </style>

</head>

<body style="background-color: #f1f1f1; font-family: 'Lato', sans-serif; overflow-x: hidden; margin: 0;">
    <main>
        <!----- EMAIL-SECTOION-START ------>
        <section class="email-bg"
            style=" width: 60%;margin: 60px auto;box-shadow: 0 2px 8px hsl(0deg 0% 0% / 16%);background: #fff;padding: 20px;">
            <div class="container">
                <header style="border-bottom: 1px solid #ccc;">
                    <!--<img class="logo" src="{{$logo_url}}" alt="logo" style=" width:20%;">-->
                </header>
                <div class="email_content" style="
                width: 100%;
            ">
                    <h4 style="font-size: 24px;margin-bottom:0;">Brand New UPDATE from Quicklee Shipping</h4>
                    <p style="font-size: 20px;">Dear {{$userName}},</p>
                    <p style="font-size: 20px;">We have received the following package at our Miami warehouse. your
                        package details are listed below</p>
                    
                        <div class="main_content" style="border-bottom: 1px solid #ccc;">
                            <ul style="list-style: none;width: 50%;box-shadow: 0 0 3px rgb(0 0 0 / 16%);padding: 10px;border-radius: 6px;">
                                <li class="big_head"style="display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373; vertical-align: middle;">
                                    Supplier Tracking #
                                </li>
                                <li class="small_text" style="display: inline-block; width: 68%; padding: 0 0 10px 0; vertical-align: middle;">{{$supplier_tracking_no}}</li>
                                <li class="big_head" style="vertical-align: middle; display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px;color:#737373;">
                                    Account no
                                </li>
                                <li style="vertical-align: middle; display: inline-block; width: 68%; padding: 0 0 10px 0;">{{$mailbox}}</li>
                                <li class="big_head" style="vertical-align: middle; display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">
                                    Supplier
                                </li>
                                <li style="vertical-align: middle; display: inline-block; width: 68%; padding: 0 0 10px 0;">{{$courier}}</li>
                                <li class="big_head" style="vertical-align: middle; display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">
                                    Weight
                                </li>
                                <li style="vertical-align: middle; display: inline-block; width: 68%; padding: 0 0 12px 0;">{{$weight}} lbs</li>
                                <li class="big_head" style="vertical-align: middle; display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">
                                    Description
                                </li>
                                <li style="vertical-align: middle; display: inline-block; width: 68%; padding: 0 0 12px 0;">{{$item_description}}</li>
                                <li class="big_head" style="vertical-align: middle; display: inline-block;width: 30%;padding: 0 0 12px 0;font-weight: 800;font-size: 16px; color:#737373;">
                                    Status
                                </li>
                                <li style="vertical-align: middle; display: inline-block; width: 68%; padding: 0 0 12px 0;">{{$status}}</li>
                            </ul>
                        </div>
                    <p style="font-size: 20px;">You will be notified of your charges as soon as your package arrives in
                        Jamaica.</p>
                    <p style="font-size: 20px;">To ensure smooth process kindly update a copy of the invoice(s) of your
                        purchas(s) provided by the selier</p>
                    <p style="font-size: 20px;">This procedire will avoid any delays at Customs, upon arrival of your
                        package.</p>

                </div>
                <footer style="font-size: 14px; border-top: 1px solid #ccc;">
                    <h4 style=" font-size: 13px;color: #8c8c8c;">Thanks for your support Enjoy your shopping experience.
                    </h4>
                    <p style=" font-size: 13px;color: #8c8c8c;">Best Wishes</p>
                    <p style=" font-size: 13px;color: #8c8c8c;">Quicklee Shipping Client Services</p>
                </footer>
            </div>
        </section>
        <!----- EMAIL-SECTOION-END ------>

    </main>
</body>
</html>