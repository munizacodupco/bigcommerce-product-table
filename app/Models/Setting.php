<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'settings';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get a setting by name and send default if not present.
     * @param string $name
     * @param mixed $default
     * @return type
     */
    static function get($name, $default = false) {
        $setting = self::where('name', $name)->first();
        if ($setting) {
            return $setting->value;
        }
        return $default;
    }
    
    /**
     * Update setting by name
     * @param string $name
     * @param mixed $value
     * @return boolean
     */
    static function update_setting($name, $value) {
        $setting = self::where('name', $name)->first();
        if ($setting) {
            $setting->value = $value;
        } else {
            $setting = new Setting();
            $setting->name = $name;
            $setting->value = $value;
        }
        $setting->save();
        return true;
    }
    
    /**
     * Get client id of the app.
     * @return string
     */
    static public function getAppClientId() {
        if (app()->environment('local')) {
            return self::get('bc_local_client_id');
        } else {
            return self::get('bc_app_client_id');
        }
    }
    
     /**
     * Get client secret of the app.
     * @param Request $request
     * @return string
     */
    static public function getAppSecret( $request) {
        if (app()->environment('local')) {
            return self::get('bc_local_secret');
        } else {
            return self::get('bc_app_secret');
        }
    }
    
    

}
