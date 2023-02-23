jQuery(document).ready(function ($) {

        window.productTable = {};

        get_currencies();

        if ($('#product-table-wrapper').length > 0) {
                showProductTable();
        }

        $(document).on('change', EnableDisableBulk);
        $('.add_all_to_cart_button').on('click', BulkAddToCart);
        $('#cwpt_product_selection').on('click', selectAll);
        $(document).on('click','.add-to-cart', SingleAddToCart);
       

});

/**
 * Display product table with data on page
 * @returns null
 */
function showProductTable() {
        let table = $('#product-table').DataTable({
                
            processing: true,
            serverSide: true,
            ajax: {
                url: window.appUrl + '/get_products?store_hash='+window.storeHash,
            },
            initComplete: function( settings, json ) { 
                if( typeof json.product_images !== 'undefined'){
                  product_images = json.product_images;
                }
            },
            oLanguage : {
                "sLengthMenu" : "Show Entries _MENU_ ",
                "sSearch": "Search _INPUT_",
                "sSearchPlaceholder": "Search"
            },
            columnDefs: [{
                    'targets': 'price_col',
                    'render': function( data, type, row, meta ) {

                        if (type == "sort" || type == "type") {
                                data = jQuery( data ).find( '.amount' ).text();
                                return parseFloat( data.replace( /[^0-9.\s]/gi, '' ).replace( /[_\s]/g, '' ) );
                        } else {
                                return data;
                        }
                    }
                }]
         
        });
          
        $('.product-table-loader').hide();
        $('#product-table-container').removeAttr('hidden');
        return;

}

/**
 * Create new cart using selected products.
 * @param {string} url
 * @param {array} cartItems
 * @returns {Promise}
 */
function createCart(url, cartItems) {
        return fetch(url, {
                method: "POST",
                credentials: "same-origin",
                headers: {
                        "Content-Type": "application/json"},
                body: JSON.stringify(cartItems)
        })
                .then(response => response.json());
}
/**
 * Get current currencies used by store to display amount.
 * @returns {null}
 */
function get_currencies() {
        let appUrl = (window.appUrl) ? window.appUrl : '';
        $.ajax({
                url: appUrl + "/bc-api/v2/currencies?store_hash="+window.storeHash,
                success: function (response) {
                        window.productTable.currency = response[0];
                },
                error: function (error) {
                        console.log(error);
                }
        });
}

/**
 * Update product quantity of existing product in the cart from product table. 
 * @returns null
 */
function updateQuantity( ) {

        let qty_input = jQuery(this);
        let add_to_cart = qty_input.closest('tr').find('.add-to-cart');
        let href = add_to_cart.attr('href');
        let previous_qty = getQueryVariable('quantity', href);

        if (false === previous_qty) {
            href += "&quantity=" + qty_input.val();
        } else {
            href = href.replace("quantity=" + previous_qty, "quantity=" + qty_input.val());
        }
        add_to_cart.attr('href', href);
        return;

}

/**
 * Find and return query parameter from the provided query string.
 * @param string variable
 * @param string query
 * @returns string|boolen
 */
function getQueryVariable(variable, query = '') {
        var vars = query.split('&');
        for (var i = 0; i < vars.length; i++) {
                var pair = vars[i].split('=');
                if (decodeURIComponent(pair[0]) == variable) {
                        return decodeURIComponent(pair[1]);
                }
        }
        return false;
}

/**
 * Enable or Disable bulk add to cart button based on or more products are selected.
 * @returns null
 */
function EnableDisableBulk() {
        let check_products = $('.cwpt-multiselect_checkbox:checkbox:checked');

        if (check_products.length > 0) {
                jQuery('.add_all_to_cart_button').removeAttr('disabled');
        } else {
                jQuery('.add_all_to_cart_button').attr('disabled', 'disabled');
        }
}

/**
 * Add products to cart in Bulk using Storefront API.
 * @returns null
 */
function BulkAddToCart() {

        let check_products = jQuery('.cwpt-multiselect_checkbox:checkbox:checked');
        let lineItems = [];
        let total_qty = 0;
        let is_valid = true;
        
        check_products.each(function (index, value) {

                let parent =  $(value.closest('tr'));
                let product_quantity = 1;
                if( parent.find('.qty').length > 0 ){
                      product_quantity =   parseInt(parent.find('.qty').val())
                }
                
                if( jQuery(value).val() == 'on'){
                    return;  
                }
                
                let attrs = parent.find('.product-attr');
       
                let options = [];
                attrs.each(function( index, value){
                        let option_val =  jQuery(value).val();
                        if(option_val == ''){

                               alert("One or more product options are missing.")
                               is_valid =  false;
                               return false;
                        }
                       options.push({'optionId':parseInt($(value).attr('id')),'optionValue':parseInt(option_val)});

               });
               
                if (product_quantity) {
                        total_qty+=product_quantity;
                        lineItems.push(
                                {
                                        "quantity": product_quantity,
                                        "product_id": jQuery(value).val(),
                                        "optionSelections" : options
                                });
                }

        });
        if(lineItems.length < 1 || !is_valid){
            return ;
        }
        PTaddToCart(lineItems,total_qty);


}

/**
 * "Select All" checkbox.
 * @returns null
 */
function selectAll() {
        $('.cwpt-multiselect_checkbox:checkbox').prop('checked', this.checked);
}

/**
 * Get current cart of the user ( If exists ).
 * @param string url
 * @returns object
 */
function getCart(url ) {
        return fetch(url, {
                method: "GET",
                credentials: "same-origin",
                headers: {
                        "Content-Type": "application/json"}
        })
                .then(response => response.json());
}

/**
 * Add items to existing cart of the user.
 * @param string url
 * @param array cartItems
 * @returns object
 */
function updateCart(url, cartItems) {
    
        return fetch(url, {
                method: "POST",
                credentials: "same-origin",
                headers: {
                        "Content-Type": "application/json"},
                body: JSON.stringify(cartItems),
        })
                .then(response => response.json());
}

/***
 * Show all product options of the bigcommerce product
 * @param int product_id
 * @returns null
 */
function showOptionSet( product_id ){
        let appUrl = (window.appUrl) ? window.appUrl : '';
        $.ajax({
                url: appUrl + "/bc-api/v3/catalog/products/"+product_id+"/options",
                success: function (response) {
                        let options = response.data;
                        options.forEach(function( currentValue  , index ){
                            getDropDownList(currentValue,product_id);                         
                        });            
                },
                error: function (error) {
                        console.log(error);
                }
        });
}

/**
 * Show dropdown for option values.
 * @param string option
 * @param int prepend_to product id
 * @returns int
 */
function getDropDownList(option, prepend_to) {
    
    var combo = $("<select class='product-attr' ></select>").attr("id", option.id).attr("name", option.id);
    
    combo.append("<option value='' > Select " + option.display_name + "</option>");
    $.each(option.option_values, function (i, el) {
        combo.append("<option value='"+el.id+ "' >" + el.label + "</option>");
    });
    combo.prependTo('.product-'+prepend_to);
 
}

/**
 * Add a single product to cart.
 * @returns null
 */
function SingleAddToCart(){
       let add_to_cart_btn = $(this);     
       let attrs = add_to_cart_btn.siblings('.product-attr');
       let is_valid = true;
       
        let options = [];
        attrs.each(function( index, value){
                let option_val =  $(value).val();
                if(option_val == ''){
                      
                        alert("One or more product options are missing.")
                        is_valid = false;
                        options = [];
                        return false;                     
                }
            options.push({'optionId':parseInt($(value).attr('id')),'optionValue':parseInt(option_val)});

       });
        if( (attrs.length > 0 && options.length < 1) || !is_valid){
            return ;
        }
        
        let id = add_to_cart_btn.data('id');
        let quantity =  1;
        
        if( add_to_cart_btn.closest('tr').find('.qty').length > 0 ){
                quantity = parseInt(add_to_cart_btn.closest('tr').find('.qty').val());
        }
        
        if( quantity > 0 ){
                let lineItems = [];
                lineItems.push({
                                "quantity": quantity,
                                "product_id": id,
                                "optionSelections": options
                                });
                PTaddToCart(lineItems,quantity);       
        }
        
}

/**
 * Add all formated line items to cart.
 * @param array lineItems
 * @param int count
 * @returns null
 */
function PTaddToCart( lineItems, count = 0){
        
        let api_url = (typeof storeUrl !== 'undefined') ? storeUrl : '';
       
        if( typeof window.cart  !== 'undefined' ){
            let cart_id  =  window.cart.id;
            updateCart(api_url + `/api/storefront/carts/${cart_id}/items`,{
                "lineItems": lineItems}).then(function (data) {
                    showAddToCartMessage( count,lineItems, data );     

                })
                .catch(function (error) {
                        console.error(error)
                });
                return;
        }
        getCart('/api/storefront/carts')
        .then(function (data) {
                       
            if(data.length > 0 ){
                window.cart = data[0];
                let cart_id  =  data[0].id;
                updateCart(api_url + `/api/storefront/carts/${cart_id}/items`,{
                    "lineItems": lineItems}).then(function (data) {

                            showAddToCartMessage( count,lineItems, data );  
                    })
                .catch(function (error) {
                    console.error(error);
                });
            }else{
                createCart(api_url + `/api/storefront/carts`, {
                        "lineItems": lineItems}
                )
                .then(function (data) {

                        showAddToCartMessage( count,lineItems, data );  
                })
                .catch(function (error) {
                        console.error(error);
                });
            }
        })
        .catch(function (error) {
                console.error(error);
        });        

}

/**
 * Show message when product(s) are successfully added to cart.
 * @param int count
 * @param array lineItems
 * @param array cartItems
 * @returns null
 */
function showAddToCartMessage( count,lineItems, cartItems ){
    
    let modalBody = '', header_message = '';
    
    if( count == 1){
        header_message = "Ok, "+count+" item is added to your cart. What's next?";
    }else{
        header_message = "Ok, "+count+" items were added to your cart. What's next?";
    }
    
    jQuery('#modal').addClass('bcpt');
    let modal_footer = '<div class="modal-footer"><a href="/cart.php" class="btn btn-primary">View Cart</a><button type="button" class="btn btn-secondary" data-dismiss="modal">Continue Shopping</button></div>';
    jQuery('#modal').html('<div class="modal-header"><h1 class="modal-header-title">'+header_message+'</h1><a class="modal-close"><span>&#215;</span></a></div><div class="modal-body"><div class="cart-items" >'+modalBody+'</div></div>'+modal_footer);
    jQuery('#modal').css('visibility','visible');
    
    lineItems.forEach(function(item,index){
     
        let foundItem = null;     
        foundItem = cartItems.lineItems.physicalItems.find(obj => {
              return obj.productId == item.product_id
        });

        cartItem.find('.item-thumbnail').attr('src', product_images[item.product_id]); // image
        cartItem.find('.item-title').text(foundItem.name); // title
        cartItem.find('.item-qty').text(item.quantity); // quantity
        cartItem.find('.item-price').text(foundItem.listPrice.toLocaleString('en-US', {style: 'currency', currency: window.productTable.currency.currency_code})); // price       
        cartItem.appendTo('.cart-items');      
    });
      
    jQuery('#modal').modal('show');
}

// On 'x' , close modal.
jQuery(document).on('click','.modal-close',function(){      
    jQuery('#modal').modal('hide');
});