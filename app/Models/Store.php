<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Store_Setting;

class Store extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stores';
    public $is_expired = false;
    
    /**
     * Get the setting associated with the store.
     */
    public function setting()
    {
        return $this->hasOne(Store_Setting::class);
    }
    
    /**
     * Get store object from store hash.
     * @param string $store_hash
     * @return boolean | Store object
     */
    static function get_store($store_hash) {
        $store = self::where('store_hash', $store_hash)->first();
        if ($store) {
            return $store;
        }
        return false;
    }
    
    /**
     * Save authentication details for the store.
     * @param array $data
     */
    static function save_auth( $data ){
        
        $store_hash = $data['context'];
        $store =  self::get_store( $store_hash );
        
        if( !$store ){
            $store = new Store();
            $store->store_hash = $store_hash;
            $store->trial = 1;
            $store->active = 1;
            $store->expires_at = \Carbon\Carbon::now()->addDays(7);
        }
        $store->access_token = $data['access_token'];
        $store->user_id = $data['user']['id'];
        $store->user_email = $data['user']['email'];
        $store->save();
    }
    
    /**
     * Get access token for the store.
     * @param string $store_hash
     * @return type
     */
    public function get_access_token( $store_hash = '' ) {
        if (app()->environment('local')) {
            return Setting::get('bc_local_access_token');
        } else {
            return $this->access_token;
        }
    }
    
    /**
     * Return setting array of the current store.
     * @param boolen $to_array
     * @return array
     */
    public function get_settings( $to_array = true ){
        $setting = $this->setting;
        if ( !$setting ) {
            
            $setting = new Store_Setting();
            $setting->table_columns = [];
            $setting->categories    = [];
            $setting->button_color = Store_Setting::$defaults['button_color'];
            $setting->header_color = Store_Setting::$defaults['header_color'];
            $setting->pagination_color = Store_Setting::$defaults['pagination_color'];
            $setting->bulk_add_color = Store_Setting::$defaults['bulk_add_color'];
            
        } else {
            
            $setting->table_columns = unserialize($setting->table_columns);
            $setting->categories  = (!empty($setting->categories))?unserialize($setting->categories):[];
        }
        $setting->store_hash = $this->store_hash;

        if( $to_array ){
            return $setting->toArray();
        }
        return $setting;
    }
    
    /**
     * Save product table configuration for the store.
     */
    public function save_product_table_setting( ){
         
        $inputs = request()->all();
        
        if (!$this->setting ) {
            $this->setting = new Store_Setting();
            $this->setting->store_id = $this->id;
            
        }
        $this->setting->button_color = $inputs['button_color'];
        $this->setting->header_color = $inputs['header_color'];
        $this->setting->pagination_color = $inputs['pagination_color'];
        $this->setting->bulk_add_color = $inputs['bulk_add_color'];
        $this->setting->table_columns = ( !empty( $inputs['table_columns'] ) )?serialize( $inputs['table_columns'] ):serialize([]);
        $this->setting->categories  = (!empty($inputs['product_categories']))?serialize($inputs['product_categories']):'';
        $this->setting->save();

    }
    
    /**
     * Check if app access is active.
     * @return boolean
     */
    public function is_access_expired(){
        
        if( $this->trial && !$this->active ){
            $this->is_expired = true;
            $expired_message = config('messages.subscription.trial_expired');
            $this->replace_merge_tags( $expired_message );
        }
        elseif( !$this->trial && !$this->active ){
            $this->is_expired = true;
            $expired_message = config('messages.subscription.paid_expired');
            $this->replace_merge_tags( $expired_message );
        }
        return $this->is_expired;
    }

    /**
     * Replace merge tags in expiration message.
     * @param string $string
     */
    public function replace_merge_tags( &$string ){
        
        $replace = [
            '{app_name}' => config('app.name'),
            '{subscription_link}' => 'stripe/'.$this->store_hash
        ];
        
        $string = str_replace(array_keys($replace), array_values($replace), $string); 
        $this->expired_message = $string;
    }
    
    /**
     * Update last access of the product table , for tracking purpose.
     */
    public function update_last_access(){
        $this->setting->update_last_access();
    }
    
    /**
     * Get stripe customer id for the store. 
     * @param StripeClient $stripe
     * @return boolean
     */
    public function get_stripe_customer_id( $stripe ){
        
        $customer_id = $this->stripe_customer_id;
         
        if (!$customer_id) {

            $error = false;
            try {
                $customer = $stripe->customers->create([
                    'name' => $this->user_email,
                    'email' => $this->user_email,
                ]);
                $customer_id = $customer->id;
                $this->stripe_customer_id = $customer_id;
                $this->save();
     
            } catch (Stripe_CardError $e) {

                $error = $e->getMessage();
            } catch (Stripe_InvalidRequestError $e) {

                $error = $e->getMessage();
            } catch (Stripe_AuthenticationError $e) {

                $error = $e->getMessage();
            } catch (Stripe_ApiConnectionError $e) {

                $error = $e->getMessage();
            } catch (Stripe_Error $e) {
                $error = $e->getMessage();
            } catch (Exception $e) {
                $error = $e->getMessage();
            }

            if ($error) {
                pre_print($error);
                _log($error);
                return false;
            }
        }
        return $customer_id;
    }
    
    
        /**
         * Update the access of the store.
         * @param bool $active
         * @param datetime $expires_at
         */
        public function update_access( $active, $expires_at = ''  ){
            $this->trial = 0;
            $this->active = $active;
            if( $expires_at ){
                $this->expires_at = $expires_at;
            }
            
        }
        
        /**
         * Find attached store to the given stripe customer id.
         * @param string $stripe_customer_id
         * @return Store|false 
         */
        public static function find_by_customer_id( $stripe_customer_id ){
            
            $store = self::where( 'stripe_customer_id', $stripe_customer_id)->first();
            if ($store) {
                return $store;
            }
            return false;
        }
    
        
}
