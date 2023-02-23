<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;
use App\Http\Controllers\BigCommerceController;
use App\Models\Setting;
use App\Helpers\BigCommerceApi;

class ProductTableController extends Controller
{
    function __construct() {
       
    }
    
    /**
     * Show product table structure.
     * @param Request $request
     * @return view
     */
    function index( Request $request ){
        $hash =  $request->input('store_hash');
        $store = Store::get_store($hash);
        if( !$store ){
            return response(['success'=> false, 'message'=> config('messages.setting.not_found')], 404)->header('Content-Type', 'application/json');
        }
        $store->update_last_access();
        if( $store->is_access_expired() ){
            return response(['success'=> false, 'message'=> 'Expired'], 401)->header('Content-Type', 'application/json');
        }
        
        $setting = $store->setting;
        $setting->table_columns = (!empty($setting->table_columns))?unserialize($setting->table_columns):[];
        $categories = (!empty($setting->categories))?unserialize($setting->categories):[];
        $query = '?include=images';
        
      
        if( !empty($categories) ){
            $query .= '&categories:in='. implode(',', $categories);
        }
        
        $this->get_currency($request);       
        $columns = [
            'Product Name',
            'Product Description',
            'Product Quantity',
            'Product Price',
            'Cart Actions',
             
        ];
        
        return view('producttable',["columns"=>$columns, "setting"=>$setting, 'products' => [] ]);
    }
    
    /**
     * Get products for product table.
     * @param Request $request
     * @return string
     */
    function get_products(Request $request) {
        
        $response_Arr = [
            'draw' => intval($request->input('draw')),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'totalPages' => 0,
            'currentPage' => 0
        ];
        $hash  = $request->input('store_hash');
        $store = Store::get_store($hash);
        if( !$store ){
            return $response_Arr;
        }
        $setting    = $store->get_settings( false );
        $categories = $setting->categories;
        $query      = '?include=images,options';
        
        if (!empty($categories)) {
            $query .= '&categories:in=' . implode(',', $categories);
        }


        if (!is_null($request['search']['value'])) {
            $query .= '&keyword=' . urlencode($request['search']['value']);
        }

        $start  = $request['start'];
        $length = 10;
        if ($start == 0) {
            $page = 1;
        } else {
            $page = ( $start / $length ) + 1;
        }
        $currency = unserialize(Setting::get('currency', (object)[]));
        $bigcommerce_api = new BigCommerceApi( $store->store_hash, $store->access_token ); 
        $response = $bigcommerce_api->get_products( $query . '&page=' . $page );

        $data = [];
        if ( property_exists( $response, 'data' ) ) {
            $images = [];
            foreach ( $response->data as $product) {
                $data[] = $this->prepare_data($product, $setting->table_columns, $images, $currency);
              
            }
            $response_Arr = [
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $response->meta->pagination->total,
                'recordsFiltered' =>  $response->meta->pagination->total,
                'data' => $data,
                'totalPages' =>  $response->meta->pagination->total_pages,
                'currentPage' => $response->meta->pagination->current_page,
                'product_images' => $images
            ];
        } else {
            $response_Arr = [
                'draw' => intval($request->input('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => $data,
                'totalPages' => 0,
                'currentPage' => 0
            ];
        }

        echo json_encode($response_Arr);
        exit;
    }
    
    /**
     *  Prepare product rows for product table.
     * @param object $product
     * @param array $columns
     * @param array $images
     * @param sting $currency
     * @return string
     */
    function prepare_data($product, $columns, &$images,$currency) {
        $data = [];
        $images[$product->id] = (!empty($product->images) )?$product->images[0]->url_thumbnail:asset('/images/placeholder.png');

        $data[] = '<div class="cwpt-selection-wrapper"><input type="checkbox" class="cwpt-multiselect_checkbox" value="' . $product->id . '"></div>';
        if (in_array('Product Image', $columns)) {
            if (!empty($product->images)) {

                $img = '<img src="' . $product->images[0]->url_thumbnail . '" style="width: 100px;height: 100px;" />';
            } else {

                $img = '<img src="' . asset('/images/placeholder.png') . '" style="width: 100px;height: 100px;" />';
            }

            $data[] = "<div>{$img}</div>";
        }
        if (in_array('Product Name', $columns)) {

            $data[] = '<div>' . $product->name . '</div>';
        }
        if (in_array('Product Description', $columns)) {
            $data[] = '<div>' . mb_strimwidth($product->description, 0, 25, "...") . '</div>';
        }
        if (in_array('Product Quantity', $columns)) {
            $data[] = '<div><input type="number"  class="qty" min="1" step="1" value="1" /></div>';
        }
        if (in_array('Product Price', $columns)) {
            $data[] = '<div><span class="amount">' . format_price($product->price,$currency) . '</span></div>';
        }
        if (in_array('Cart Actions', $columns)) {
            if ( !empty($product->option_set_id) ) {
                $options = $this->get_product_options($product);
                $data[] = '<div class="cwpt-add-to-cart-wrapper product-' . $product->id . '" >' . $options . '<a data-id="' . $product->id . '" class="btn btn-primary add-to-cart"  >Add to Cart</a></div>';
            } else {

                $data[] = '<div class="cwpt-add-to-cart-wrapper product-' . $product->id . '" ><a data-id="' . $product->id . '" class="btn btn-primary add-to-cart"  >Add to Cart</a></div>';
            }
        }
     

        return $data;
    }
    
    /**
     * Get variable product options html
     * @param aaray $product
     * @return string
     */
    function get_product_options($product) {
        $options = '';
        
        foreach ($product->options as $option) {
            
            $option_id = $option->id;
            $options .= "<select class='product-attr' id='$option_id' name='$option_id' > 
                            <option value='' >{$option->name}</option>";
            foreach ($option->option_values as $option_value) {
                $options .= "<option value='{$option_value->id}'>{$option_value->label}</option>";
            }
            $options .= "</select>";
        }

        return $options;
    }
    
    /**
     * Get currency for store.
     * @param Request $request
     */
    function get_currency( $request ){
        
        $bigcom_c = new BigCommerceController();
        $result = $bigcom_c->proxy_BigCommerce_API_Request($request, 'v2/currencies');
        
        $currencies = json_decode($result->content());
        if( !isset($currencies->errors) ){  
            Setting::update_setting('currency',  serialize($currencies[0])); 
        }
        
    }

}
