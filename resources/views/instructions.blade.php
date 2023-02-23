<div class="form-group row">
    <p> To setup product table Create a new webpage, go to page Content, click "HTML" button on the right then copy and pasta the code from below. Now press Save & Exit.</p>                                                                               
    <textarea id="instruction-text" cols="83" rows="10" disabled="disabled">
<div id="product-table-wrapper"></div>
<script defer  type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script type="text/javascript" src="{{URL::to('/')}}/js/jquery.dataTables.min.js" defer="defer"></script>
<script type="text/javascript">
                window.appUrl = "{{URL::to('/')}}";
                window.storeHash = '{{$store_hash}}';
                </script>
<script defer  type="text/javascript" src="{{URL::to("/")}}/js/load.js"></script>
    </textarea>
</div>
                                                                               