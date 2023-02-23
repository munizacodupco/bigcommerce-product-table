<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
        <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">

                <title>{{ config('app.name') }}</title>

                <!-- Fonts -->
                <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@200;600&display=swap" rel="stylesheet">

                <!-- Styles -->
                <link rel="stylesheet" type="text/css" href="{{asset('/css/app.css')}}">
                <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
                <!-- Script -->
                <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
               
                <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
               
        </head>
        <body>
                <div class="main-content">
                        <div class="container-fluid ">
                                 @yield('content')
                                <div class="row">
                                       
                                </div>
                        </div>
                </div>

                <script type="text/javascript" src="{{asset('/js/app.js')}}"></script>
                <script type="text/javascript" src="{{asset('/js/product-table.js')}}"></script>
        </body>
</html>
