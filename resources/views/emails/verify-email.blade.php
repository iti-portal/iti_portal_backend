<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email Address</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            padding-bottom: 20px;
        }

        .header h1 {
            color: #333333;
        }

        .content {
            text-align: center;
        }

        .button {
            display: inline-block;
            background-color: #ff0000;
            color: #ffffff;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            color: #666666;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>ITI Portal</h1>
        </div>
        <div class="content">
            <p>Please click the button below to verify your email address for ITI Portal.</p>
            <a href="{{ $url }}" class="button">Verify Email Address</a>
            <p>If you did not create an account, no further action is required.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} ITI Portal. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
