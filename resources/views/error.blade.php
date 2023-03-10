<!DOCTYPE html>
<html>
        <head>
                <title>Application Error</title>
                <meta name="csrf-token" content="{{csrf_token()}}" />
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <link rel="stylesheet" type="text/css" href="{{mix('/css/app.css')}}">
        </head>
        <body>
                <div class="container mt-md-5">
                        <div class="shadow-sm p-5 mb-5 bg-white rounded">
                                <div class="container">
                                        <h1 class="display-6">An issue has occurred: </h1>
                                        <p class="lead">
                                                @if( session()->has('error_message') )
                                                    {{ session('error_message') }}
                                                @elseif( session()->has('error_code')  )
                                                    {{ session('error_code') }}
                                                @endif
                                                
                                        </p>
                                </div>
                        </div>
                </div>
        </body>
</html>
