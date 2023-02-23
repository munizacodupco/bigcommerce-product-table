<?php
/*
 * BigCommerceApi helper class
 */
namespace App\Helpers;

use Setting;
use Illuminate\Support\Facades\Http;


class BigCommerceApi {
    
    public  $api_url    = 'https://api.bigcommerce.com/';
    private $access_token;
    private $store_hash;
    
    function __construct( $store_hash, $access_token ) {
        
       $this->store_hash = $store_hash;
       $this->access_token = $access_token;
    }
    
    /**
     *  Get product category tree.
     * @return array
     */
    function get_product_categories( ){
        
        $endpoint = 'v3/catalog/categories/tree';
        $response = $this->send_request( $endpoint, 'GET' ); 
        
        if(  isset( $response->data ) ){
            return $response->data;
        }
        return [];
    }
    
    /**
     * Get product from bigcommerce api.
     * @return array
     */
    function get_products( $query ){
        
        $endpoint = 'v3/catalog/products'.$query;
        $response = $this->send_request( $endpoint, 'GET' ); 
        
        if(  isset( $response->data ) ){
            return $response;
        }
        return [];
    }
    
    /**
     * Send request to bigcommerce.
     * @param string $endpoint
     * @param string $method
     * @param array $data
     * @return array
     */
    public function send_request( $endpoint, $method = 'get', $content = [], $decode = true) {
        
        if (strrpos($endpoint, 'v2') !== false) {
            $endpoint .= '.json';
        }
        
        $headers =  [
            'X-Auth-Client' => Setting::getAppClientId(),
            'X-Auth-Token' => $this->access_token,
            'Content-Type'  => 'application/json',
        ];
        
        $url = $this->api_url . $this->store_hash . '/' . $endpoint;
        
        if(  'GET' === $method ){
            $response = Http::withOptions( [ 'verify' => false ] )
                ->withHeaders($headers)->get( $url );
        }
        
        if(  'POST' === $method ){
            $response = Http::withOptions( [ 'verify' => false ] )
                ->withHeaders($headers)->post( $url, $content );
        }
        
        if(  'PUT' === $method ){
            $response = Http::withOptions( [ 'verify' => false ] )
                ->withHeaders($headers)->put( $url, $content );
        }
        if( $decode ){
            return json_decode($response->getBody()->getContents());
        }       
        
        return $response;
    }
}
