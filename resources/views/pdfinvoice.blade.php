<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Invoice</title>

        <!-- Fonts -->
        <!--<link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">-->

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                font-family: 'Raleway', sans-serif;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
			.leftLogo {
				float: left;
				width: 33%;
			}
			.centertitle {
				float: left;
				width: 33%;
				text-align: center;
			}
			.rightInfo {
				float: left;
				width: 33%;
			}
			.rightInfo span{
				text-align:right;
				display:block;
				padding:5px 0;
			}
			
			.centertitle h2 {
				margin:0px;
			}
			hr {
				width: 100%;
			}
			.billingAddress{
				width:32%;
				border:1px solid #000;
				float:left;
				margin-right:10px;
			}
			
			.invoiceInfo{
				width:32%;
				background: #0087e0;
				float:left;
				margin-right:10px;
			}
			.billingAddress span{
				text-align:left;
				display:block;
				padding:3px 5px;
				font-size: 11px;
				color:#000;
			}
			.billingAddress strong{
				text-align: left;
				display: block;
				padding: 3px 5px;
				font-weight: bold;
				font-size: 11px;
			}
			.invoce_left {
				width: 50%;
				float: left;
			}
			.invoce_left strong{
				display:block;
				text-align:left;
				padding:3px;
			}
			.invoce_right {
				width: 50%;
				float: left;
			}
			.invoce_right span{
				display:block;
				text-align:left;
			}
			.table {
				width: 100%;
				float: left;
				margin-top:10px;
			}
			.table th{
				border-bottom:1px solid #000;
				border-top:1px solid #000;
				padding-left:3px;
				background: #0087e0;
			}
			.table td{
				border-bottom:1px solid #000;
				
			}
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="content">
            	<div class="topHeader">
                	
                    <div class="centertitle">
                    	<h2>Create Manifest</h2>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
