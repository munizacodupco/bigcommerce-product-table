<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
        <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">

                <title>Laravel</title>

                <!-- Fonts -->
                <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@200;600&display=swap" rel="stylesheet">

                <!-- Styles -->
                <link rel="stylesheet" type="text/css" href="{{mix('/css/app.css')}}">
                <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>               
                <script src="https://code.jquery.com/jquery-3.5.1.js"></script>


        </head>
        <body>
                <div class="main-content">
                        <div class="container-fluid ">
                                <div class="row">
                                        <div class="container">
                                                <div class="row justify-content-center">
                                                        <div class="col-md-8">
                                                                <div class="card">
                                                                        <div class="card-header">{{ __('App Setting') }}</div>

                                                                        <div class="card-body">
                                                                                <form method="POST">
                                                                                        @csrf

                                                                                        <div class="form-group row">
                                                                                                <label for="client_id" class="col-md-4 col-form-label text-md-right">{{ __('APP Client ID') }}</label>

                                                                                                <div class="col-md-6">
                                                                                                        <input id="client_id" type="text" class="form-control @error('client_id') is-invalid @enderror" name="client_id" value="{{(old('client_id'))?old('client_id'):$client_id }}" required autocomplete="client_id" >

                                                                                                        @error('client_id')
                                                                                                        <span class="invalid-feedback" role="alert">
                                                                                                                <strong>{{ $message }}</strong>
                                                                                                        </span>
                                                                                                        @enderror
                                                                                                </div>
                                                                                        </div>
                                                                                        <div class="form-group row">
                                                                                                <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('APP Client Secret') }}</label>

                                                                                                <div class="col-md-6">
                                                                                                        <input id="client_secret" type="text" class="form-control @error('client_secret') is-invalid @enderror" name="client_secret" value="{{ (old('client_secret'))?old('client_secret'):$client_secret }}" required autocomplete="client_secret" >

                                                                                                        @error('client_secret')
                                                                                                        <span class="invalid-feedback" role="alert">
                                                                                                                <strong>{{ $message }}</strong>
                                                                                                        </span>
                                                                                                        @enderror
                                                                                                </div>
                                                                                        </div>
                                                                                        <div class="form-group row">
                                                                                                <label for="access_token" class="col-md-4 col-form-label text-md-right">{{ __('Access Token') }}</label>

                                                                                                <div class="col-md-6">
                                                                                                        <input id="access_token" type="text" class="form-control @error('access_token') is-invalid @enderror" name="access_token" value="{{ (old('access_token'))?old('access_token'):$token }}" required autocomplete="access_token" >

                                                                                                        @error('access_token')
                                                                                                        <span class="invalid-feedback" role="alert">
                                                                                                                <strong>{{ $message }}</strong>
                                                                                                        </span>
                                                                                                        @enderror
                                                                                                </div>
                                                                                        </div>
                                                                                        <div class="form-group row">
                                                                                                <label for="store_hash" class="col-md-4 col-form-label text-md-right">{{ __('Store Hash') }}</label>

                                                                                                <div class="col-md-6">
                                                                                                        <input id="store_hash" type="text" class="form-control @error('store_hash') is-invalid @enderror" name="store_hash" value="{{ (old('store_hash'))?old('store_hash'):$store_hash }}" required autocomplete="store_hash" >

                                                                                                        @error('store_hash')
                                                                                                        <span class="invalid-feedback" role="alert">
                                                                                                                <strong>{{ $message }}</strong>
                                                                                                        </span>
                                                                                                        @enderror
                                                                                                </div>
                                                                                        </div>
                                                                                        <div class="form-group row mb-0">
                                                                                                <div class="col-md-6 offset-md-4">
                                                                                                        <button type="submit" class="btn btn-primary">
                                                                                                                {{ __('Save') }}
                                                                                                        </button>
                                                                                                </div>
                                                                                        </div>
                                                                                </form>
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

