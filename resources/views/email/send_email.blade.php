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
                    <img class="logo" src="{{$logo_url}}" alt="logo" style=" width:20%;">
                </header>
                <div class="email_content" style="width: 100%;">
                    <p style="font-size: 20px;">Dear <?php echo $name; ?>,</p>
                    <p style="font-size: 20px;"><?php echo $decription; ?></p>
                </div>
                <footer style="font-size: 14px; border-top: 1px solid #ccc;">
                    <h4 style=" font-size: 13px;color: #8c8c8c;">Thanks for your support Enjoy your shopping experience.</h4>
                    <p style=" font-size: 13px;color: #8c8c8c;">Best Wishes</p>
                    <p style=" font-size: 13px;color: #8c8c8c;">{{$company_name}} Client Services</p>
                </footer>
            </div>
        </section>
        <!----- EMAIL-SECTOION-END ------>

    </main>
</body>
</html>