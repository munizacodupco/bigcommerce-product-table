$(document).ready(function () {

        let appUrl = (window.appUrl)?window.appUrl:'';
        if (!window.initialized) {
                $.ajax({
                        url:  appUrl+"/product-table?store_hash="+window.storeHash,
                        success: (function (response) {
                               $('#product-table-wrapper').html(response);
                        })
                });
                window.initialized = 1;
        }
});
