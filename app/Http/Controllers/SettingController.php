<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Setting,Store;
use App\Helpers\BigCommerceApi;

class SettingController extends Controller
{
    function __construct() {
       
    }
    
    /**
     * Show and save app credentials page
     * @param Request $request
     * @return view
     */
    function app_setting( Request $request){
        
        if( 'POST' == $request->method()  ){
            $this->save_app_settings( $request );
            
        }
        $client_id = Setting::get('bc_app_client_id','');
        $client_secret = Setting::get('bc_app_secret','');
        $local_client_id = Setting::get('bc_local_client_id','');
        $local_client_secret = Setting::get('bc_local_secret','');
        $local_token = Setting::get('bc_local_access_token','');
        $local_store_hash = Setting::get('bc_local_store_hash','');
        return view('app-setting',[ 
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'local_client_id' => $local_client_id,
            'local_client_secret' => $local_client_secret, 
            'local_token'=> $local_token,
            'local_store_hash'=> $local_store_hash
            ]);
    }
    
    /**
     * Save posted APP setting to db
     * @param Request $request
     */
    function save_app_settings( $request ){
        $client_id = $request->input('client_id');
        $client_secret = $request->input('client_secret');
        $local_client_id = $request->input('local_client_id');
        $local_client_secret = $request->input('local_client_secret');
        $local_token = $request->input('local_access_token');
        $local_store_hash = $request->input('local_store_hash');
        Setting::update_setting('bc_app_client_id',$client_id);
        Setting::update_setting('bc_app_secret', $client_secret);
        Setting::update_setting('bc_local_client_id',$local_client_id);
        Setting::update_setting('bc_local_secret', $local_client_secret);
        Setting::update_setting('bc_local_access_token', $local_token);
        Setting::update_setting('bc_local_store_hash', $local_store_hash);
    }
    
    /**
     * Show setting screen on load
     * @param string $url
     * @param Request $request
     * @return view
     */
    function store_config($url = '',Request $request){

        if( 'oauth' == $url ){
            $inputs = request()->all();
            foreach( $inputs as $name => $value ){
                Setting::update_setting( $name, $value );
            }
        }
        
        $store_hash = $request->input('store_hash');

        // Show welcome screen if no store hash
        if( empty($store_hash)){
           return  view('welcome');
        }
        
        $store = Store::get_store($store_hash);
        if( !$store ){
            return redirect('error')->with('error_message', config('messages.setting.not_found'));
        }

        $settings = $store->get_settings();

        $is_expired = null;
        if( $store->is_access_expired() ){
            $is_expired = $store->expired_message;
        }
        
        $settings['category_options'] = $this->get_categories( $store );
        $settings['is_trial'] = $store->trial;
        
        if(  $request->success ){
            $settings['success'] = $request->success;
        }
        
        if(  $request->error ){
            $settings['error'] = $request->error;
        }
       
        return view('configuration',$settings)->with('is_expired',$is_expired);
    }
    
    
    /**
     * Save Configuration.
     * @param Request $request
     * @return view
     */
    function save_config(Request $request) {
       
        $store = Store::get_store($request->input('store_hash'));    
        $store->save_product_table_setting( );
        
        $is_expired = false;
        if( $store->is_access_expired() ){
            $is_expired = $store->expired_message;
        }
        $settings = $store->get_settings();
        $settings['category_options'] = $this->get_categories( $store );
        $settings['success'] = config('messages.setting.updated');
        $settings['is_trial'] = $store->trial;

        return view('configuration', $settings)->with('is_expired',$is_expired);
    }
    
    
   /**
    * Get categories for the store from bigcommerce.
    * @param Store $store
    * @return array
    */
    function get_categories($store ){
 
        $bigcommerce_api = new BigCommerceApi( $store->store_hash, $store->access_token );    
        $categories = $bigcommerce_api->get_product_categories();
        return $categories;
    }
}
