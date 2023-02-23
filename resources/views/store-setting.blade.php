<div class="form-group row">
        <label for="table_columns[]" class="col-md-4 col-form-label text-md-right">{{ __('Table columns ') }}</label>

        <div class="col-md-6">
                
                <select name="table_columns[]" class="form-control" multiple="multiple">
                        <option value="Product Image" {{ in_array('Product Image',$table_columns )? 'selected':''}} >Product Image</option>
                        <option value="Product Name" {{ in_array('Product Name',$table_columns )? 'selected':''}} >Product Name</option>
                        <option value="Product Description" {{ in_array('Product Description',$table_columns )? 'selected':''}}  >Product Description</option>
                        <option value="Product Quantity" {{ in_array('Product Quantity',$table_columns )? 'selected':''}}  >Product Quantity</option>
                        <option value="Product Price" {{ in_array('Product Price',$table_columns )? 'selected':''}}  >Product Price</option>
                        <option value="Cart Actions" {{ in_array('Cart Actions',$table_columns )? 'selected':''}} >Cart Actions</option>

                </select>
        </div>
</div>
<div class="form-group row">
        
        <label for="button_color" class="col-md-4 col-form-label text-md-right">{{ __('Button Color') }}</label>

        <div class="col-md-6">
                <input type="text" name="button_color" class="input-group form-control colorpicker" value="{{ (isset($button_color))?$button_color:'' }}" autocomplete="off"  />
        </div>
</div>
<div class="form-group row">
        <label for="header_color" class="col-md-4 col-form-label text-md-right">{{ __('Header Color') }}</label>

        <div class="col-md-6">
                <input type="text" name="header_color" value="{{ (isset($header_color))?$header_color:'' }}" class="form-control colorpicker" autocomplete="off" />
        </div>
</div>
<div class="form-group row">
        <label for="pagination_color" class="col-md-4 col-form-label text-md-right">{{ __('Pagination Button Color') }}</label>

        <div class="col-md-6">
                <input type="text" name="pagination_color" value="{{ (isset($pagination_color))?$pagination_color:'' }}" class="form-control colorpicker" autocomplete="off" />
        </div>
</div>
<div class="form-group row">
        <label for="bulk_add_color" class="col-md-4 col-form-label text-md-right">{{ __('Add Selected to Cart Button Color') }}</label>

        <div class="col-md-6">
                <input type="text" name="bulk_add_color" value="{{ (isset($bulk_add_color))?$bulk_add_color:'' }}" class="form-control colorpicker" autocomplete="off" />
        </div>
</div>

<div class="form-group row">
        <label for="product_categories" class="col-md-4 col-form-label text-md-right">{{ __('Product Categories') }}</label>

        <div class="col-md-6">
                <select name="product_categories[]" class="form-control categories" multiple="multiple">
                        @foreach ($category_options as $option_id => $option)
                        <?php echo show_options( $option, $categories ); ?>                          
                        @endforeach
                </select>
        </div>
</div>
<div class="form-group row mb-0">
        <div class="col-md-6 offset-md-4">
                <button type="submit" class="btn btn-primary">
                        {{ __('Save') }}
                </button>
        </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.3/js/bootstrap-colorpicker.min.js"></script>
<script>
$('.colorpicker').colorpicker({
    format: 'hex'
});

$(document).on('click', ".copy-text",function(){
    var copyText = document.getElementById("instruction-text");
    navigator.clipboard.writeText(copyText.value); 
    copyText.focus();
  copyText.select();
     document.execCommand('copy');
});

</script>
