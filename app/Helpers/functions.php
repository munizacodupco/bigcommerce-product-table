<?php


if( !function_exists( '_log') ){
    /**
    * Create log 
    * @param string|array $arMsg
    */
    function _log($arMsg,$file_name = 'requestlog_') {

        $stEntry = "";

        $arLogData['event_datetime'] = '[' . date('D Y-m-d h:i:s A') . '] [client ' . ']';

        if (is_array($arMsg)) {

            foreach ($arMsg as $key => $msg)
                $stEntry .= $arLogData['event_datetime'] . " " . " $key => " . "" . print_r($msg, 1) . "\r\n";
        } else {
            $stEntry .= $arLogData['event_datetime'] . " " . $arMsg . "\r\n";
        }

        $stCurLogFileName = $file_name . date('Ymd') . '.txt';
        $storagePath = storage_path() .DIRECTORY_SEPARATOR . 'logs'.DIRECTORY_SEPARATOR ;


        $fHandler = fopen($storagePath . $stCurLogFileName, 'a+');


        fwrite($fHandler, $stEntry);

        fclose($fHandler);
    }
}

if( !function_exists('format_price') ){
    
    /**
     * Format the price amount based on given store setting.
     * @param mixed $price
     * @param type $setting
     * @return type
     */
    function format_price( $price,$setting = []){

        if('left' ==  $setting->token_location ){
            return  $setting->token.''. number_format( $price, $setting->decimal_places,  $setting->decimal_token, $setting->thousands_token);
        }
        return number_format($price, $setting->decimal_places,  $setting->decimal_token, $setting->thousands_token).''. $setting->token;
    }

}

if( !function_exists('show_options') ){
    function show_options( $category, $selected_categories ,$prefix = ''){
        $selected = in_array($category->id,$selected_categories )? 'selected':'';
        $html = "<option  value='{$category->id}' {$selected} >{$prefix}{$category->name}</option>";
        if( !empty( $category->children) ){
            foreach( $category->children  as $child){
            $html .= show_options( $child, $selected_categories , $prefix.'&nbsp;&nbsp;&nbsp;' ) ; 
            }
        }
        return $html;
    }
}
if( !function_exists('pre_print') ){
    
    /**
     * Print given input using enclosed in <pre> tags.
     * @param mixed $value
     */
    function pre_print( $value ){
        echo '<pre>'.print_r($value,1).'</pre>';
    }
}