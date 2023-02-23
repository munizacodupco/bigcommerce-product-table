<!DOCTYPE html>
<html>
   <head>
      <title>{{ config('app.name') }}</title>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <link href="{{ asset('css/app.css') }}" rel="stylesheet">
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
      <style type="text/css">
         .panel-title {
            display: inline;
            font-weight: bold;
         }
         .display-table {
            display: table;
         }
         .display-tr {
            display: table-row;
         }
         .display-td {
            display: table-cell;
            vertical-align: middle;
            width: 61%;
         }
         .quick-order-overlay {
            display: none;
            background: #000;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100vh;
            opacity: 0.6;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
         }
         .quick-order-overlay .spinner-border {
            width: 15rem;
            height: 15rem;
         }
      </style>
   </head>
   <body>
      <div class="container pt-3 pb-5">
         <div class="pt-3">
            <h2 style="float:left">Product Table</h2>
            <form action="{{url( '/?store_hash='.$store_hash )}}" method="GET">
               @csrf
               <input type="hidden" name="quickorder_count" value="1">
               <input type="hidden" name="store_hash" value="{{$store_hash}}" >
               <input style="float: right;margin: 20px 0 10px 0;" class="btn btn-primary" type="submit" value="Back">
            </form>
            <div style="clear:both"></div>
            <hr/>
           
         </div>
         <div class="border rounded p-5 mb-3 mt-3">
            <h4>Product Table Subscription</h4>
            <p>{{config('messages.subscription.activate')}}</p>
            <hr/>
            @if (Session::has('success'))
            <div class="alert alert-success text-center">
               <a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
               <p>{{ Session::get('success') }}</p>
            </div>
            @endif
            @if (Session::has('error'))
            <div class="alert alert-danger text-center">
               <a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
               <p>{{ Session::get('error') }}</p>
            </div>
            @endif
            <div class="row">
                
                <div class="col-md-6 col-md-offset-3">
                  <div class="panel panel-default credit-card-box">
                     <div class="panel-heading display-table" >
                        <div class="row display-tr" >
                           <h3 class="panel-title display-td" >Payment Details</h3>
                           <div class="display-td" >                            
                              <img class="img-responsive pull-right" src="{{asset('/images/stripe.png')}}">
                           </div>
                        </div>
                     </div>
                     <div class="panel-body">
                        
                        
                        @php
                            if( env('PAYMENT_MODE') == 'test')
                                $stripe_key =env('SANDBOX_STRIPE_KEY');
                            else
                                $stripe_key = env('STRIPE_KEY');
                        @endphp
                        <form role="form"  method="POST" class="require-validation" data-cc-on-file="false" data-stripe-publishable-key="{{$stripe_key}}" id="payment-form">
                           @csrf
                           <input type="hidden" name="store_hash" value="{{$store_hash}}" >
                           <div class='form-row row'>
                              <div class='col-xs-12 form-group required'>
                                 <label class='control-label'>Card Number</label> 
                                 <input autocomplete='off' class='form-control card-number' size='16' maxlength="16" type='text'>
                              </div>
                           </div>
                           <div class='form-row row'>
                              <div class='col-xs-12 col-md-4 form-group cvc required'>
                                 <label class='control-label'>CVC</label>
                                 <input autocomplete='off' class='form-control card-cvc' placeholder='ex. 311' size='3' maxlength="3" type='text'>
                              </div>
                              <div class='col-xs-12 col-md-4 form-group expiration required'>
                                 <label class='control-label'>Expiration Month</label> 
                                 <input class='form-control card-expiry-month' placeholder='MM' size='2' maxlength="2" type='text'>
                              </div>
                              <div class='col-xs-12 col-md-4 form-group expiration required'>
                                 <label class='control-label'>Expiration Year</label> 
                                 <input class='form-control card-expiry-year' placeholder='YYYY' size='4' maxlength="4" type='text'>
                              </div>
                           </div>
                           <div class='form-row row'>
                              <div class='col-md-12 error form-group hide'>
                                 <div class='alert-danger alert'>Please correct the errors and try
                                    again.
                                 </div>
                              </div>
                           </div>
                           <div class="row">
                              <div class="col-xs-12">
                                 <input class="btn btn-primary btn-lg btn-block" type="submit" value="Pay Now ({{App\Http\Controllers\PaymentController::get_subscription_price()}})">
                              </div>
                                
                           </div>
                        </form>
                        <p class="text-center">{{App\Http\Controllers\PaymentController::get_interval()}}</p>   
                     </div>
                  </div>
                    @include('support')
               </div>
            </div>
		</div>
      <div class="quick-order-overlay">
         <div class="d-flex justify-content-center">
            <div class="spinner-border" role="status">
               <span class="sr-only">Loading...</span>
            </div>
         </div>
      </div>
      </div>
   </body>
   <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
   <script type="text/javascript">
      $(function() {
         var $form = $(".require-validation");
         $('form.require-validation').bind('submit', function(e) {
            var $form = $(".require-validation"),
               inputSelector = ['input[type=email]', 'input[type=password]',
                     'input[type=text]', 'input[type=file]',
                     'textarea'
               ].join(', '),
               $inputs = $form.find('.required').find(inputSelector),
               $errorMessage = $form.find('div.error'),
               valid = true;
            $errorMessage.addClass('hide');
            $('.has-error').removeClass('has-error');
            $inputs.each(function(i, el) {
               var $input = $(el);
               if ($input.val() === '') {
                     $input.parent().addClass('has-error');
                     $errorMessage.removeClass('hide');
                     e.preventDefault();
               }
            });
            if (!$form.data('cc-on-file')) {
               e.preventDefault();
               Stripe.setPublishableKey($form.data('stripe-publishable-key'));
               Stripe.createToken({
                     number: $('.card-number').val(),
                     cvc: $('.card-cvc').val(),
                     exp_month: $('.card-expiry-month').val(),
                     exp_year: $('.card-expiry-year').val()
               }, stripeResponseHandler);
            }
         });
         function stripeResponseHandler(status, response) {
            if (response.error) {
               $('.error')
                     .removeClass('hide')
                     .find('.alert')
                     .text(response.error.message);
            } else {
               /* token contains id, last4, and card type */
               var token = response['id'];
               $form.find('input[type=text]').empty();
               $form.append("<input type='hidden' name='stripeToken' value='" + token + "'/>");
               $('.quick-order-overlay').css( 'display','flex' );
               $form.get(0).submit();
            }
         }
      });
   </script>
</html>