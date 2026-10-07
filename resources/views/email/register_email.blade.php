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
            .account_info {
    width: 90% !important;
}  
        }

        @media (max-width: 767.98px) {
           
        }
        @media (max-width: 600px) {
           .email-bg{
               width: 90%  !important;
           }
        }
    @media (max-width:380px){
        .small_text{
            font-size: 16px  !important;
        }  
        .email_content p {
            font-size: 16px !important;
        }
    }
    @media (max-width:380px){
        .small_text{
            font-size: 14px  !important;
        }  
        .account_info span {
            margin: 0 0 0 60px !important;
        }
    }
    </style>
</head>
<body style="background-color: #f1f1f1; font-family: 'Lato', sans-serif; overflow-x: hidden; margin: 0;">
    <main>
        <!----- EMAIL-SECTOION-START ------>
        <section class="email-bg" style=" width: 60%;margin: 60px auto;box-shadow: 0 2px 8px hsl(0deg 0% 0% / 16%);background: #fff;padding: 20px;">
            <div class="container">
                <header style="border-bottom: 1px solid #ccc;">
                    <img class="logo" src="{{$logo_url}}" alt="logo" style=" width:20%;">
                </header>
                <div class="email_content" style="
                width: 100%;
            ">
                    <h4 style="font-size: 24px; margin-bottom: 0;">Dear <?php echo $userName; ?></h4>
                    <p style="font-size: 20px;">{{$company_name}} We would like to thnak you for joining the {{$company_name}} family. We are looking forward to creating a great business relationship while giving you the best courier service experience possible.</p>
                    <a href="{{$link}}">{{$link}}</a>
                    <p style="font-size: 20px;">Please see your account details below</p>
                    <p style="font-size: 20px;">We have activated your new account. Please loging by visiting our homepage at the following address</p>
                   <div class="account_info" style="  width: 30%;
                   border: 1px solid #cccc;
                   padding: 0 8px;">
                       <p ><b>Account no.</b> <span style="font-size: 14px;margin: 0 0 0 80px;float: right;">{{$mailbox}}</span></p>
                       <p><b>Username </b> <span style="font-size: 14px;margin: 0 0 0 80px;float: right;"><a href="mailto:{{$email}}">{{$email}}</a></span></p>
                   </div>
                    <p style="font-size: 20px;">Please follow the format below when placing your order.</p>
                    <a href="#">admin@admin.com</a>   
                    <h4>Shipping Address:</h4>     
                    <p><b>Name:</b><span>{{$address1}}</span></p>
                    <p><b>Address Line 1:</b><span>{{$address2}}</span></p> 
                    <p><b>Address Line 2:</b><span>{{$address3}}</span></p> 
                    <p><b>City:</b><span>{{$address4}}</span></p>   
                    <p><b>State:</b><span>{{$address5}}</span></p>    
                    <p><b>Zip:</b><span>{{$address6}}</span></p>              
                </div>
                <footer>
                    <h4 style="font-size: 14px;border-top: 1px solid #ccc; font-size: 13px; color: #8c8c8c;">Thanks for your support Enjoy your shopping experience. </h4>
                    <p style="font-size: 13px; color: #8c8c8c;">Best Wishes</p>
                    <p style="font-size: 13px; color: #8c8c8c;">{{$company_name}} Client Services</p>
                </footer>
            </div>
        </section>
        <!----- EMAIL-SECTOION-END ------>

    </main>
</body>
</html>