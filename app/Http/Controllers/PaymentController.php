<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Store;
use Stripe\StripeClient;
use App\Models\Subscription;
use Carbon\Carbon;

class PaymentController extends Controller {
    
    public static $interval;
    /**
     * Show payment page.
     * @param Request $request
     * @param string $store_hash
     * @return view
     */
    function index(Request $request, $store_hash) {
        
        $store_hash = "stores/" . $store_hash;

        if ('POST' == $request->method()) {
            
            $subscription_created = $this->process_payment($request, $store_hash);
            if( $subscription_created ){   
                $redirect_url = '/?store_hash=' . $store_hash.'&success='.config('messages.subscription.created');          
                return redirect($redirect_url);
            }else{
                return back()->with('error', config('messages.subscription.subscription_error') );
            }      
        }
        
        return view('stripe')->with('store_hash', $store_hash);
       
    }
    
    /**
     * Process payment for the store.
     * @param Request $request
     * @param string $store_hash
     * @return view
     */
    function process_payment($request, $store_hash) {

        $store = Store::get_store($store_hash);
        if (!$store) {
            return redirect('error')->with('error_message', config('messages.setting.not_found'));
        }
        
        if (env('PAYMENT_MODE') == 'test') {
            $stripe_secret = env('SANDBOX_STRIPE_SECRET');
        } else {
            $stripe_secret = env('STRIPE_SECRET');
        }
        
        $stripe = new StripeClient($stripe_secret);       
        $customer_id = $store->get_stripe_customer_id( $stripe );
     
        if ( !$customer_id ) {
            return back()->with('error',config('messages.subscription.customer_error') );
        }
        
        
        $source_created = $this->create_source( $stripe, $customer_id, $request->stripeToken );
        if( !$source_created ){
            return back()->with('error', config('messages.subscription.source_error') );
            
        }
        
        $subscription_created = $this->create_subscription($stripe, $customer_id, $store );
        return $subscription_created;

    }
    
    /**
     * Create subscription for the store on stripe.
     * @param StripeClient $stripe
     * @param string $customer_id
     * @param Store $store
     * @return view
     */
    function create_subscription($stripe, $customer_id, $store) {
        $stripe_price_id = null;
        if (env('PAYMENT_MODE') == 'test') {
            $stripe_price_id = env('SANDBOX_STRIPE_APP_ID');
        } else {
            $stripe_price_id = env('STRIPE_APP_ID');
        }
        try {

            $stripe_subscription = $stripe->subscriptions->create([
                'customer' => $customer_id,
                'items' => [
                    ['price' => $stripe_price_id],
                ],
            ]);
            $expires_at =  Carbon::createFromTimestamp($stripe_subscription->current_period_end)->format('Y-m-d H:i:s');
            $subscription = new Subscription();
            $subscription->store_id = $store->id;
            $subscription->stripe_id = $stripe_subscription->id;
            $subscription->ends_at = $expires_at ;
            $subscription->save();
            $store->update_access( 1, $expires_at );
            $store->save();
            return true;
            
             
        } catch (Stripe_CardError $e) {
            $error = $e->getMessage();
            
        } catch (Stripe_InvalidRequestError $e) { // Invalid parameters were supplied to Stripe's API

            $error = $e->getMessage();
        } catch (Stripe_AuthenticationError $e) { // Authentication with Stripe's API failed
            $error = $e->getMessage();
        } catch (Stripe_ApiConnectionError $e) { // Network communication with Stripe failed
            $error = $e->getMessage();
        } catch (Stripe_Error $e) { // Display a very generic error to the user, and maybe send

            $error = $e->getMessage();
        } catch (Exception $e) { // Something else happened, completely unrelated to Stripe
            $error = $e->getMessage();
        }
        $this->subscription_error =  $error;
        return false;
        $redirect_url = '/?store_hash=' . $store->store_hash.'&error='.config('messages.subscription.subscription_error');
        return redirect($redirect_url);
        
    }
    
    /**
     * Attach credit card to the the strip customer created for store.
     * @param StripeClient $stripe
     * @param string $customer_id
     * @param string $stripeToken
     * @return boolean
     */
    function create_source( $stripe, $customer_id, $stripeToken){
        try {

            $stripe->customers->createSource(
                $customer_id,
                ['source' => $stripeToken]
            );
            return true;
        } catch (Stripe_CardError $e) {

            $error = $e->getMessage();
        } catch (Stripe_InvalidRequestError $e) {

            // Invalid parameters were supplied to Stripe's API
            $error = $e->getMessage();
        } catch (Stripe_AuthenticationError $e) {

            // Authentication with Stripe's API failed
            $error = $e->getMessage();
        } catch (Stripe_ApiConnectionError $e) {

            // Network communication with Stripe failed
            $error = $e->getMessage();
        } catch (Stripe_Error $e) {

            // Display a very generic error to the user, and maybe send
            // yourself an email
            $error = $e->getMessage();
        } catch (Exception $e) {

            // Something else happened, completely unrelated to Stripe
            $error = $e->getMessage();
        }
        return false;
    }
    
    /**
     * Update subscription expiry on renewal of subscription.
     * @param Request $response
     */
    public function subscription_created( Request $response ){
        
        $event = @file_get_contents('php://input');
        $event = json_decode( $event );
        _log( print_r($event,1), 'Subscription_');
        if( 'customer.subscription.updated' == $event->type  ||  'customer.subscription.created' == $event->type ) {
            
            $stripe_subscription = $event->data->object;
            $subscription        = Subscription::get_stripe_subscription( $stripe_subscription->id );
            if( $subscription ){
                $store = Store::find_by_customer_id( $stripe_subscription->customer );
                if( $store ) {
                    $subscription_period_end = $stripe_subscription->current_period_end;
                    $expires_at              = Carbon::createFromTimestamp( $subscription_period_end )->format('Y-m-d H:i:s');
                    $store->update_access( 1, $expires_at );
                }
            }
            
            
        }

        http_response_code(200);
    }
    
    /**
     * Mark subscription expired if automatic payment failed on stripe.
     * @param Request $response
     */
    public function subscription_failed( Request $response ){
        
        $event = @file_get_contents('php://input');
        $event = json_decode( $event );
        _log( print_r($event,1), 'Subscription_');
        if( 'charge.failed' == $event->type || 'invoice.payment_failed' == $event->type ) { //subscription_schedule.canceled
            
            $stripe_subscription = $event->data->object;
            $subscription        = Subscription::get_stripe_subscription( $stripe_subscription->id );
            if( $subscription ){
                $store = Store::find_by_customer_id( $stripe_subscription->customer );
                if( $store ) {
                    $store->update_access( 0 );
                   
                }
            }

        }
        http_response_code(200);
        
    }
    
    public static function get_subscription_price(){
        $stripe_price_id = null;
        if (env('PAYMENT_MODE') == 'test') {
            $stripe_price_id = env('SANDBOX_STRIPE_APP_ID');
            $stripe_secret = env('SANDBOX_STRIPE_SECRET');
        } else {
            $stripe_price_id = env('STRIPE_APP_ID');
            $stripe_secret = env('STRIPE_SECRET');
        }
        
        
        $stripe = new StripeClient($stripe_secret);   
         try {

            $product_price = $stripe->prices->retrieve($stripe_price_id);

            $symbol        = \Symfony\Component\Intl\Currencies::getSymbol( strtoupper($product_price->currency) );
            $price         = $product_price->unit_amount /100;
            self::$interval = $product_price->recurring->interval;
            return $symbol.''.$price; 
            
             
        } catch (Stripe_CardError $e) {
            $error = $e->getMessage();
            
        } catch (Stripe_InvalidRequestError $e) { // Invalid parameters were supplied to Stripe's API

            $error = $e->getMessage();
        } catch (Stripe_AuthenticationError $e) { // Authentication with Stripe's API failed
            $error = $e->getMessage();
        } catch (Stripe_ApiConnectionError $e) { // Network communication with Stripe failed
            $error = $e->getMessage();
        } catch (Stripe_Error $e) { // Display a very generic error to the user, and maybe send

            $error = $e->getMessage();
        } catch (Exception $e) { // Something else happened, completely unrelated to Stripe
            $error = $e->getMessage();
        }
        return '';
    }
    
    static function get_interval(){
        return 'and every '.self::$interval;
    }

}
