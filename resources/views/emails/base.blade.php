<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #eeeeee;
            margin-bottom: 25px;
        }

        .header h1 {
            color: #901b20;
            margin: 0;
            font-size: 24px;
        }

        .content {
            padding: 0 10px;
        }

        .button {
            display: inline-block;
            background-color: #901b20;
            color: #ffffff;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }

        .button:hover {
            background-color: #7a161b;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666666;
            font-size: 12px;
            border-top: 1px solid #eeeeee;
            padding-top: 20px;
        }

        .highlight-note {
            color: #901b20;
            font-size: 13px;
            font-weight: bold;
            margin: 15px 0;
        }

        .support-link {
            color: #901b20;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('logo.png') }}" alt="ITI Portal Logo" style="max-width: 150px; height: auto;">
            <h1>@yield('header')</h1>
        </div>
        <div class="content">
            @yield('content')
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} ITI Portal. All rights reserved.</p>
            <p>
                <a href="{{ config('app.url') }}" class="support-link">Visit our website</a> |
                <a href="mailto:support@iti.com" class="support-link">Contact Support</a>
            </p>
        </div>
    </div>
</body>

</html>
