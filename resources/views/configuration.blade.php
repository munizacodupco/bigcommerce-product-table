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
                <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.3/css/bootstrap-colorpicker.min.css" rel="stylesheet">

        </head>
        <body>
                <div class="main-content">
                        <div class="container-fluid ">
                                <div class="row">
                                        <div class="container">
                                                <div class="row justify-content-center">
                                                        <div class="col-md-12">
                                                                <div class="card">
                                                                        <div class="card-header">{{ __('Setup Product Table') }}
                                                                                @if( $is_trial)
                                                                                        <a href="{{ url('/stripe/'.$store_hash) }}" style="float: right;" class="btn btn-primary" >Subscribe</a>
                                                                                @else
                                                                                    <span style="float: right;" class="btn btn-success">Subscribed</span>
                                                                                @endif
                                                                                
                                                                        </div>
                                                                        <div class="card-body">
                                                                                @if(isset($success))
                                                                                    <div class='alert alert-success'>{{$success}}</div>
                                                                                @endif
                                                                                @if(isset($error))
                                                                                    <div class='alert alert-error'>{{$error}}</div>
                                                                                @endif
                                                                                <form method="POST" action="{{route('admin-setting',['store_hash' => $_GET['store_hash']] )}}" >
                                                                                        {{ csrf_field() }}
                                                                                        @include('store-setting')
                                                                                </form>
                                                                                <hr>                                                                            
                                                                                @include('instructions')
                                                                                @include('support')
                                                                                @if( $is_expired )
                                                                                    <div class="app-expired-overlay">
                                                                                        <?php echo ($is_expired); ?>
                                                                                    </div>       
                                                                                @endif
                                                                        </div>
                                                                </div>
                                                        </div>
                                                </div>
                                        </div>
                                </div>
                        </div>
                </div>

                
        </body>
</html>

