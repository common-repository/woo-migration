<?php

    /*
     Plugin Name: LitExtension: Migrate to Woocommerce
     Plugin URI: http://litextension.com/woocommerce-migration-tool.html
     Description: Allows migration products, customers, orders, passwords and other data from your store to WooCommerce automatically
     Version: 1.0.0
     Author: LitExtension
     Author URI: http://litextension.com/
     License: GPLv2
     */
    
    /*
     Copyright (C) 2017 LitExtension
     
     This program is free software; you can redistribute it and/or
     modify it under the terms of the GNU General Public License
     as published by the Free Software Foundation; either version 2
     of the License, or (at your option) any later version.
     
     This program is distributed in the hope that it will be useful,
     but WITHOUT ANY WARRANTY; without even the implied warranty of
     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     GNU General Public License for more details.
     
     You should have received a copy of the GNU General Public License
     along with this program; if not, write to the Free Software
     Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
     */


defined('ABSPATH') or die();

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'core.php';

class LeCaMg
{    
    const DEMO_MODE = false;
    const DEV_MODE = false;
    const VERSION = '1.0.1';
    const TABLE_IMPORT = 'lecm_import';
    const TABLE_USER = 'lecm_user';
    const TABLE_RECENT = 'lecm_recent';
    const TABLE_UPDATE = 'lecm_update';
    const LECAMG_SETTING = 'LECAMG_SETTING';
    const PLUGIN_WOOCOMMERCE = 'woocommerce/woocommerce.php';
    const WOO_TAX_SETTING = 'woocommerce_tax_classes';

    protected static $_instance = null;
    protected $_setting = array(
        'taxes' => 4,
        'manufacturers' => 4,
        'categories' => 4,
        'products' => 4,
        'customers' => 4,
        'orders' => 4,
        'reviews' => 4,
        'delay' => '0.05',
        'retry' => 30,
        'prefix' => '',
        'license' => ''
    );

    public function __construct() {
        
    }
    
    public static function getInstance() {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public static function getGlobal($name){
        global $$name;
        return $$name;
    }

    public static function path(){
        return plugin_dir_path(__FILE__);
    }

    public static function url(){
        return plugin_dir_url(__FILE__);
    }

    public static function getModel($name){
        $model = null;
        $cart_path = self::path() . 'models/' . str_replace('_', '/', $name) . '.php';
        if (file_exists($cart_path)) {
            $name_split = explode('_', $name);
            $upper = array_map('ucfirst', $name_split);
            $class = implode('', $upper);
            require_once $cart_path;
            $class_name = 'LeCaMg' . $class;
            $model = new $class_name();
        }
        return $model;
    }

    public static function convertVersion($v, $num){
        $digits = @explode(".", $v);
        $version = 0;
        if (is_array($digits)) {
            foreach ($digits as $k => $v) {
                if($k <= $num){
                    $version += (substr($v, 0, 1) * pow(10, max(0, ($num - $k))));
                }
            }
        }
        return $version;
    }

    public function init(){
        if(self::DEMO_MODE){
            session_start();
        }
        register_activation_hook(__FILE__, array(&$this, 'activation'));
        register_deactivation_hook(__FILE__, array(&$this, 'deactivation'));
        add_action('admin_init', array(&$this, 'registerSetting'));
        return $this;
    }
    
    public function run(){
        if (is_multisite()) {
            add_action( 'admin_menu', array(&$this, 'initMenu' ) );
        } else {
            add_action( 'admin_menu', array(&$this, 'initMenu' ) );
        }
        add_action('admin_enqueue_scripts', array(&$this, 'enqueueScripts'));
        add_action('wp_ajax_le_cart_migration', array('LeCaMgDisplay', 'displayAjax'));
        return $this;
    }

    public function enqueueScripts(){        
        wp_enqueue_style('le-cm-style', self::url() . 'assets/css/style.css');
        wp_enqueue_script('le-core-script', LeCaMg::url() . 'assets/js/jquery.core.js');
	    wp_enqueue_script('le-cm-script', LeCaMg::url() . 'assets/js/jquery.lecm.js');
    }
    
    public function activation(){
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        require_once ABSPATH . 'wp-admin/includes/schema.php';
        $db = self::getGlobal('wpdb');
        $db->show_errors(self::DEV_MODE);
        $queries = array(
            "CREATE TABLE IF NOT EXISTS `" . $db->prefix . self::TABLE_IMPORT . "` (`domain` VARCHAR(255), `type` VARCHAR(255), `id_src` VARCHAR(255), `id_desc` INT(11), `status` INT(5), `value` TEXT , INDEX (`domain`, `type`, `id_src`)) ENGINE=InnoDB DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `" . $db->prefix . self::TABLE_USER . "` (`id` INT(11) UNIQUE NOT NULL, `notice` TEXT) ENGINE=InnoDB DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `" . $db->prefix . self::TABLE_RECENT . "` (`domain` VARCHAR(255) UNIQUE NOT NULL, `notice` TEXT) ENGINE=InnoDB DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `" . $db->prefix . self::TABLE_UPDATE . "` (`domain` VARCHAR(255), `id_src` INT(11), `id_desc` INT(11), `value` TEXT , INDEX (`domain`, `id_src`)) ENGINE=InnoDB DEFAULT CHARSET=utf8",
            "ALTER TABLE " . $db->base_prefix . "users MODIFY user_pass VARCHAR(255)"
        );
        foreach($queries as $query){
            dbDelta($query);
        }
        add_option('LECAMG_VERSION', self::VERSION);
        add_option(self::LECAMG_SETTING, serialize($this->_setting));
    }
    
    public function deactivation(){
        /*require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        require_once ABSPATH . 'wp-admin/includes/schema.php';
        $db = self::getGlobal('wpdb');
        $db->show_errors(self::DEV_MODE);
        $queries = array(
            "DROP TABLE IF EXISTS `" . $db->prefix . self::TABLE_IMPORT . "`",
            "DROP TABLE IF EXISTS `" . $db->prefix . self::TABLE_USER . "`",
            "DROP TABLE IF EXISTS `" . $db->prefix . self::TABLE_RECENT . "`",
            "DROP TABLE IF EXISTS `" . $db->prefix . self::TABLE_UPDATE . "`",
        );
        foreach($queries as $query){
            $db->query($query);
        }*/
        delete_option('LECAMG_VERSION');
        delete_option(self::LECAMG_SETTING);
    }
    
    public function registerSetting(){
        register_setting(self::LECAMG_SETTING, self::LECAMG_SETTING);
    }

    public function initMenu(){
        $menu_exists = $this->menuPageExists();
        if(!$menu_exists){
            add_menu_page('LitExtension', 'LitExtension', 'manage_woocommerce', 'litextension', array(&$this, 'website'), self::url() . 'logo.png');
        }
        add_submenu_page('litextension', 'Cart Migration', 'Cart Migration', 'manage_woocommerce', 'cart-migration', array('LeCaMgDisplay', 'displayCartMigration'));
        add_submenu_page('litextension', 'Settings', 'Settings', 'manage_woocommerce', 'cart-migration-setting', array('LeCaMgDisplay', 'displaySetting'));
        //add_options_page('LitEx: Cart Migration', 'LitEx: Cart Migration', 'manage_options', 'cart-migration-setting', array('LeCaMgDisplay', 'displaySetting'));
        remove_submenu_page('litextension', 'litextension');
    }
        
    public function website(){
        echo "<div class='wrap'><iframe scrolling='auto' style='width: 100%; height:600px;'></iframe></div>";
    }

    public function menuPageExists($slug = 'litextension')
    {
        $menus = self::getGlobal('menu');
        $exists = false;
        foreach ($menus as $order => $menu) {
            if ($slug == $menu[2]) {
                $exists = true;
                break;
            }
        }
        return $exists;
    }

}

LeCaMg::getInstance()->init()->run();
