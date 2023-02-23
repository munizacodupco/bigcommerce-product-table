
<link rel="stylesheet" type="text/css" href="{{asset('/css/app.css')}}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
 <!--<script src="{{asset('js/jquery.dataTables.min.js')}}"></script>-->
<div class="product-table-loader" style="text-align: center;">
        <img src="{{asset('/images/loader.gif')}}" style="width:50px;" />
</div>

<div id="product-table-container" hidden="hidden">

        <table id="product-table" >
                <thead>
                        <tr>    
                                <th class="product_table_header nosort ">
                                <span class="cwpt_product_selection">
                                                <input type="checkbox" name="cwpt_product_selection" class="cwpt_product_selection" id="cwpt_product_selection" >
                                        </span>
                                </th>
                               
                                @foreach ($setting->table_columns as $column_name)
                                <th class="product_table_header {{ ($column_name == 'Product Price')?'price_col':'' }} " >
                                        <span> {{$column_name}}

                                        </span>
                                </th>
                                @endforeach
                               
                        </tr>
                </thead>    
                <tbody>
                    @php
                    $images = [];
                    @endphp 
                     
                    @foreach ($products as $product)
                        
                  
                    @php   
                        if( !empty($product['images'] ) ):                                            
                            $images[ $product['id']] = $product['images'][0]['url_thumbnail'];
                        else:
                            $images[$product['id']] = asset('/images/placeholder.png');

                        endif;
                    @endphp
                        
                        <tr>
                            <td>
                                        <div class="cwpt-selection-wrapper"><input type="checkbox" class="cwpt-multiselect_checkbox" value='{{$product['id']}}'>
                                        </div>
                                </td>
                                @if( in_array('Product Image',$setting->table_columns ) )
                                <td>
                                    <div>
                                        @if( !empty($product['images'] ) )
                                                <img src="{{$product['images'][0]['url_thumbnail']}}" style="width: 100px;height: 100px;" />

                                        @else
                                                <img src="{{asset('/images/placeholder.png')}}" style="width: 100px;height: 100px;" />

                                        @endif
                                    </div>
                                </td>  
                                @endif
                                @if( in_array('Product Name',$setting->table_columns ) )
                                <td><div><a href="{{$product['custom_url']['url']}}" >{{$product['name']}}</a></div></td>  
                                @endif
                                @if( in_array('Product Description',$setting->table_columns ) )
                                <td><div>{{mb_strimwidth($product['description'], 0, 25, "...")}}</div> </td>   
                                @endif
                                @if( in_array('Product Quantity',$setting->table_columns ) )
                                <td><div><input type="number"  class="qty" min="1" step="1" value="1" /></div>   </td>
                                @endif
                                @if( in_array('Product Price',$setting->table_columns ) )
                                <td><div ><span  class="amount">{{ format_price($product['price'])}}</span></div> </td>
                                @endif
                                @if( in_array('Cart Actions',$setting->table_columns ) )
                                <td>
                                     @if( $product['option_set_id']  )
                                        <div class="cwpt-add-to-cart-wrapper product-{{$product['id']}}" >
                                           
                                            <a href="{{$product['custom_url']['url']}}"  data-id="{{$product['id']}}" class="btn btn-primary"  >Select Options</a>
                                        </div>
                                    @else
                                        <div class="cwpt-add-to-cart-wrapper product-{{$product['id']}}" >
                                                <a data-id="{{$product['id']}}" class="btn btn-primary add-to-cart"  >Add to Cart</a>
                                        </div>
                                     @endif
                                </td>
                                @endif
                             
                        </tr>
                    @endforeach 
                </tbody>
        </table>

        <div class="cwpt_bottm_btns">
                <button class="btn btn-primary add_all_to_cart_button"  disabled>{{ __('Add Selected To Cart') }}</button>

        </div>
        <div id="uniqueID" class="modal modal--large" data-reveal>
            <a href="#" class="modal-close" aria-label="close" role="button">
                <span aria-hidden="true">&#215;</span>
            </a>
            <div class="modal-content"><div class="modal-header"><h1 class="modal-header-title">Header</h1></div><div class="modal-body">MOdal Body</div></div>
            <div class="loadingOverlay"></div>
        </div>
</div>
<div class="product-table-error"  hidden="hidden">
        <h3>Something went wrong, Try reloading the page</h3>
</div>
<template id="cart-item">
  <div class="pt-cart-item">
    <div class="upper">
         <img class="item-thumbnail" src="" />
    </div>  
    <div class="lower">
        <h4 class="item-title" ></h4>
        <p>
            <span class="item-qty"></span>x<span class="item-price"></span>
        </p>
    </div>
    
  </div>
</template>
<script type="text/javascript">
    var product_images = <?php echo json_encode($images); ?>;
</script>
<script type="text/javascript" src="{{asset('/js/app.js')}}"></script>
<script type="text/javascript" src="{{asset('/js/product-table.js')}}"></script>
<style>
    .product_table_header {
        background-color: {{$setting->header_color}} !important ;
    }
    
    .cwpt-add-to-cart-wrapper .btn.btn-primary {
        background: {{$setting->button_color}} !important ;
        border-color: {{$setting->button_color}} !important ;
    }
    
    #product-table-container .add_all_to_cart_button {
            background: {{$setting->bulk_add_color}} !important ;
            border-color: {{$setting->bulk_add_color}} !important ;
        }
    #product-table-container .paginate_button  {
            background: {{$setting->pagination_color}} !important ;
            border-color: {{$setting->pagination_color}} !important ;
        }
</style>        
