<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store_Setting extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'store_settings';
    
    /**
     * Default values of colors.
     * @var array 
     */
    public static $defaults = [
        'button_color'=> '#000000',
        'header_color'=> '#000000',
        'pagination_color'=> '#ad9fb5',
        'bulk_add_color'=> '#000000',
    ];
    
    /**
     * Update last access of the product table , for tracking purpose.
     */
    public function update_last_access(){
        
        $now =  \Carbon\Carbon::now();
        $this->last_access = $now;
        $this->save();
     
    } 
}
