<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;
use Store,Setting,Subscription;
use Stripe\StripeClient;


class BigCommerceController extends BaseController {

    protected $baseURL;

    public function __construct() {
        $this->baseURL = config('app.url');
    }

    /**
     * On installation of app, verify request and save access token.
     * @param Request $request
     * @return view
     */
    public function install(Request $request) {


        // Make sure all required query params have been passed
        if (!$request->has('code') || !$request->has('scope') || !$request->has('context')) {
            return redirect('error')->with('error_message', 'Not enough information was passed to install this app.');
        }

        try {

            $client = new Client();
            $result = $client->request('POST', 'https://login.bigcommerce.com/oauth2/token', [
                'json' => [
                    'client_id' => Setting::getAppClientId(),
                    'client_secret' => Setting::getAppSecret($request),
                    'redirect_uri' => $this->baseURL . '/auth/install',
                    'grant_type' => 'authorization_code',
                    'code' => $request->input('code'),
                    'scope' => $request->input('scope'),
                    'context' => $request->input('context'),
                ]
            ]);

            $statusCode = $result->getStatusCode();
            $data = json_decode($result->getBody(), true);

            if ($statusCode == 200) {
                // TODO: Save access token
                Store::save_auth( $data );
             
                // If the merchant installed the app via an external link, redirect back to the 
                // BC installation success page for this app
                if ($request->has('external_install')) {
                    return redirect('https://login.bigcommerce.com/app/' . Setting::getAppClientId() . '/install/succeeded');
                }
            }

            return redirect($this->get_redirect_url($data));
        } catch (RequestException $e) {

            $statusCode = $e->getResponse()->getStatusCode();
            $errorMessage = "An error occurred.";

            if ($e->hasResponse()) {
                if ($statusCode != 500) {
                    $exception_response = json_decode($e->getResponse()->getBody()->getContents(),true);
                    $errorMessage = $exception_response['error'];
                }
            }

            // If the merchant installed the app via an external link, redirect back to the 
            // BC installation failure page for this app
            if ($request->has('external_install')) {
                return redirect('https://login.bigcommerce.com/app/' . Setting::getAppClientId() . '/install/failed');
            } else {
                return redirect('error')->with(['error_message' => $errorMessage, 'error_code' => $statusCode]);
            }
        }
    }

    /**
     * On Load, show app setting screen.
     * @param Request $request
     */
    public function load(Request $request) {

        $signedPayload = $request->input('signed_payload');

        if (!empty($signedPayload)) {

            $verifiedSignedRequestData = $this->verifySignedRequest($signedPayload, $request);

            if ($verifiedSignedRequestData !== null) {
                // Save verified details of necessary               
            } else {
                return redirect('error')->with('error_message', config('messages.signed_request.invalid')  );
            }
        } else {
            return redirect('error')->with('error_message', config('messages.signed_request.empty'));
        }

        return redirect($this->get_redirect_url($verifiedSignedRequestData));
    }

    /**
     * On uninstall, Mark inactive.
     * @param Request $request
     * @return boolean
     */
    function uninstall(Request $request) {  
        $signedPayload = $request->input('signed_payload');
        if (!empty($signedPayload)) {
            $verifiedSignedRequestData = $this->verifySignedRequest($signedPayload, $request);
            if ($verifiedSignedRequestData !== null) {
                $store_hash = $verifiedSignedRequestData['context'];
                // Mark app uninstalled
                $store = Store::get_store($store_hash);
                if($store){
                    if (env('PAYMENT_MODE') == 'test') {
                        $stripe_secret = env('SANDBOX_STRIPE_SECRET');
                    } else {
                        $stripe_secret = env('STRIPE_SECRET');
                    }
                    $subscription = Subscription::get_subscription_by_store_id($store->id);
                    $stripe = new StripeClient($stripe_secret);      
                    $cancel = $stripe->subscriptions->cancel($subscription->stripe_id,[]);
                    
                    /**
                     * When Successfully stripe subscription cancelled
                     * delete stored user data
                     **/ 
                    if( $cancel->status == "canceled" ){
                        $subscription->delete();
                        $store->delete();
                    }
                }

            } else {
                return redirect('error')->with('error_message', config('messages.signed_request.invalid'));
            }
        } else {
            return redirect('error')->with('error_message', config('messages.signed_request.empty'));
        }
        return true;
    }
    
    /**
     * Verify the payload data of the store.
     * @param string $signedRequest
     * @param Request $request
     * @return type
     */
    private function verifySignedRequest( $signedRequest, $appRequest ) {
        
        list($encodedData, $encodedSignature) = explode('.', $signedRequest, 2);

        // decode the data
        $signature = base64_decode($encodedSignature);
        $jsonStr = base64_decode($encodedData);
        $data = json_decode($jsonStr, true);

        // confirm the signature
        $expectedSignature = hash_hmac('sha256', $jsonStr, Setting::getAppSecret($appRequest), $raw = false);
        if (!hash_equals($expectedSignature, $signature)) {
            error_log('Bad signed request from BigCommerce!');
            return null;
        }
        return $data;
    }
    
    /**
     * Get url of setting screen. Needed after installation and on load.
     * @param array $data
     * @return string
     */
    function get_redirect_url($data) {
        $store_hash = $data['context'];
        $redirect_url = '/?store_hash=' . $store_hash;
        return $redirect_url;
    }
    
    
    /**
     * Send BigCommerce request. 
     * @param Request $request
     * @param string $endpoint
     * @return string
     */
    public function proxy_BigCommerce_API_Request(Request $request, $endpoint) {
        
        $store_hash = $request->input('store_hash');  
        $store = Store::get_store($store_hash);
        
        if( !$store ){
            return response([], 404)->header('Content-Type', 'application/json');
        }
       
        $bigcommerce_api = new \App\Helpers\BigCommerceApi( $store->store_hash, $store->access_token );  
        $response        = $bigcommerce_api->send_request( $endpoint, $request->method(),[], false );
        return response($response->getBody(), $response->getStatusCode())->header('Content-Type', 'application/json');
    }
    
    /**
     * On remove user from bigcommerce, remove user from app.
     * @param Request $request
     * @return boolean
     */
    function remove_user(Request $request) {  
        return true;
    }
    
    /**
     * Show error thrown by application
     * @return view
     */
    function show_error() {
        return view('error');
    }
}
