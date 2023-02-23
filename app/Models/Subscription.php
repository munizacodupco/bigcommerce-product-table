<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'subscriptions';
    
    
    public static function get_stripe_subscription( $stripe_subscription_id ){
        
        $subscription = self::where( 'stripe_id', $stripe_subscription_id )->first();
        if ( $subscription ) {
            return $subscription;
        }
        return false;
    }

    /**
     * Find attached subscription_id to the given store.
     * @param string $store_id
     * @return Subscription|false 
     */
    public static function get_subscription_by_store_id( $store_id ){
        
        $subscription = self::where( 'store_id', $store_id )->first();
        if ( $subscription ) {
            return $subscription;
        }
        return false;
    }
}
