<?php

namespace client_code;

/* This file and the code within it are distributed under commercial license.
 * Permission must be granted from the 
 * original author / copyright holder of the file before 
 * accessing, using, compiling, interpreting, modifying and redistributing the code.
 * 
 * email: josephlanders@gmail.com - visit contact.txt in root folder for more
 */
?>
<?php

require_once($GLOBALS["sb_code_path"] . "/client_code/model/database/database.php");
require_once($GLOBALS["sb_code_path"] . "/shared_code/mail_queue/my_mail_queue.php");
require_once($GLOBALS["sb_code_path"] . "/client_code/model/cart/cart.php");
require_once($GLOBALS["sb_code_path"] . "/shared_code/paypal//paypal_express_checkout_third_party.php");
require_once($GLOBALS["sb_code_path"] . "/client_code/model/database/user.php");
require_once($GLOBALS["sb_code_path"] . "/shared_code/memcached_store/memcached_store.php");
require_once($GLOBALS["sb_code_path"] . "/client_code/model/utilities/quick_sort.php");
require_once($GLOBALS["sb_code_path"] . "/client_code/model/utilities/quick_sort_string.php");
require_once($GLOBALS["sb_code_path"] . "/client_code/model/utilities/quick_sort_i18n_string.php");
require_once($GLOBALS["sb_code_path"] . "/shared_code/configuration/configuration.php");
#require_once("session_store/memcached_session_handler.php");
#require_once("session_store/session_store.php");
require_once($GLOBALS["sb_code_path"] . "/client_code//model/helper_classes/page_button.php");
require_once($GLOBALS["sb_code_path"] . "/client_code//model/helper_classes/page_buttons.php");
#require_once("helper_classes/page_size_button.php");
require_once($GLOBALS["sb_code_path"] . "/client_code//model/helper_classes/page_size_buttons.php");
require_once($GLOBALS["sb_code_path"] . "/client_code//model/helper_classes/page.php");
require_once($GLOBALS["sb_code_path"] . "/client_code//model/helper_classes/sort_buttons.php");
require_once($GLOBALS["sb_code_path"] . "/client_code//model/helper_classes/quicknav.php");
require_once($GLOBALS["sb_code_path"] . "/client_code//model/helper_classes/search.php");
require_once($GLOBALS["sb_code_path"] . "/client_code//model/helper_classes/social_media.php");
require_once($GLOBALS["sb_code_path"] . "/client_code//model/helper_classes/shop.php");
require_once($GLOBALS["sb_code_path"] . "/client_code//model/helper_classes/tag.php");
require_once($GLOBALS["sb_code_path"] . "/client_code//model/helper_classes/brand.php");
require_once($GLOBALS["sb_code_path"] . "/client_code//model/tax/tax_calculator.php");
require_once($GLOBALS["sb_code_path"] . "/client_code//model/shipping/shipping.php");
require_once($GLOBALS["sb_code_path"] . "/client_code//model/shipping/shipping_method.php");
require_once($GLOBALS["sb_code_path"] . "/client_code//model/email_templates/email_templates.php");
require_once($GLOBALS["sb_code_path"] . "/shared_code/cookie_manager/cookie_manager.php");
require_once($GLOBALS["sb_code_path"] . "/shared_code/machine_configuration/machine_configuration.php");

# Required to get the admin_user
require_once($GLOBALS["sb_code_path"] . "/admin_code/model/database/admin_database.php");

class model {

    private $database = null;
    private $my_mail_queue = null;
    private $sitename = "ShopsBee Pty Ltd";
    public $shop = null;
    private $address_cache = null;
    public $cart = null;
    private $search_url = null;
    public $site_version = -1;
    public $site_id = -1;
    private $session_store = null;
    public $is_checkout = false;
    public $getpost_array = array();
    public $memcached_store = null;
    public $vars = array();
    public $smarty = null;
    private $cookie_manager = null;
    private $news = "";
    private $cookie_compression_and_encryption = true;

    # Never expire database results
    # 0 is forever? 
    private $database_expiry = 3600;

    #private $configuration_root = "";
    #private $site_name = "mogsanddogs.com.au"
    public $local_region = "";

    #$getpost_array, $memcached_store, $smarty

    public function __construct($fields) {
        #echo "ABC";       

        foreach ($fields as $key => $value) {
            $this->$key = $value;
        }

        #$this->getpost_array = $getpost_array;
        #$this->memcached_store = $memcached_store;
        #$this->smarty = $smarty;
        # Anything that needs initialising from a configuration file
        $this->init_from_config();
    }

    public function order_prefix() {
        #$hostname = gethostname();
        # For instance db-db-3
        # ZVM-3
        # eu-db-1
        #$array = explode("_", $hostname);
        # WE need to the use the DB hostname first part.
        $count = count($array);

        $last_element = $array[$count - 1];

        echo $last_element;
    }

    # TODO: This needs to be made more distributed.
    # The problem atm is we need the configuration on each machine that 
    # The site runs from.
    # Solution : Probably by making a memcached database configuration (for all sites)
    # Containing the sites Paypal config, database config and 

    private function init_from_config() {
        #var_dump($GLOBALS);
        #die();
        $site_id = $this->file_config_get_setting("site_id");
        $this->site_id = $site_id;

        #$site_version = $this -> config_get_setting("site_version");

        $version_config = $this->get_version_config();



        $site_version = $version_config->get_setting("site_version");

        $this->site_version = $site_version;

        # Note 0 can equal null
        #if ((gettype($site_id) == null) && ($site_id == null)) {
        if (gettype($site_id) === null) {
            echo "invalid site id";
        }





        #$shop_region = $this->file_config_get_setting("shop_region");
        $shop_region = $this->get_machine_region();

        #TODO: Remove hack and make into function (required for deploy_queue)
        if ($this->local_region != null) {
            $shop_region = $this->local_region;
        }

        $this->database = $this->start_database($shop_region);


        #$client_config = $this->get_client_config();
        //$all_fields = $client_config->get_settings();
        //$this->shop = new shop($all_fields);

        $all_fields = $this->config_get_settings();

        $this->shop = new \client_code\shop($all_fields);

        # Set the shop region to the file_config shop region 
        # which may also have been manually overridden / passed in by deploy script
        if ($this->shop->region == null) {
            $this->shop->region = $shop_region;
        }

        $region = $shop_region;

        $strip_keyword = false;
        $vars = $this->get_variables_from_site_config("url_format_keyword_", $strip_keyword);
        //var_dump($vars);
        foreach ($vars as $key => $var) {
            $GLOBALS[$key] = $var;
        }

        #$region = $this -> shop -> region;
        #var_dump($region);
        #$mq_config = array();
        #$mail_options = array();

        try {
            $mail_queue_fields = array("local_region" => $region,
                "remote_database_region" => $region);
            $this->my_mail_queue = new \mail_queue\my_mail_queue($mail_queue_fields);
        } catch (\Exception $ex) {
            echo "Mailer failed to initialise in client model, ignoring error " . $ex;
        }


        $real_cdn = "";
        if ($this->config_get_setting("developer_disable_cdn") == true) {
            
        } else {
            $real_cdn = $this->config_get_setting("aws_cdn_location");
        }

        $this->put_var("real_cdn", $real_cdn);

        $theme_dir = $this->config_get_setting("theme_dir");

        $cdn_with_theme_dir = "";
        if ($this->config_get_setting("developer_disable_cdn") == true) {
            #$static_cdn = "/";
            #echo "a";
        } else {
            $cdn_with_theme_dir .= "//";
            $cdn_with_theme_dir .= $this->config_get_setting("aws_cdn_location");
            #echo "b";
        }
        $cdn_with_theme_dir .= "/themes/" . $site_id . "/" . $theme_dir; #. "/";
        #var_dump($cdn_with_theme_dir);
        #a.cloudfront.net/themes/35/a/a/a";


        $this->put_var("cdn_theme", $cdn_with_theme_dir);        





        $cdn = "";
        if ($this->config_get_setting("developer_disable_cdn") == true) {
            
        } else {
            $cdn .= "//";
            $cdn .= $this->config_get_setting("aws_cdn_location");
        }



        #var_dump($cdn);




        $this->put_var("cdn", $cdn);



        #$this->put_var("theme_dir", $static_cdn);
        #$this->session_store = new session_store();
        #echo "GOT HERE";
        #$this -> init_from_database();
        #session_destroy();
        #echo "ABC";
        #echo "EXECUTED";
        $this->cart = new \client_code\cart($this);
        #echo "EXECUTED";

        $cart = $this->cart;

        #echo "EXECUTED";
        #var_dump($cart);

        if ($cart->currency == null) {
            $default_currency = $this->get_default_currency();
            if ($default_currency != null) {
                #$currencycode = $default_currency->code;
                #echo "no cart currency set so using default currency " . $currencycode;
                #TODO: Set the default currency to either the default currency or the users session currency
                #$cart->set_currency($currencycode);
                $cart->set_currency($default_currency);
            }
        }


        # TODO: We need to add the current locale to the paypal config as the checkout currency - otherwise we need to use the 
        # users current session set locale :P (see page_size code and reuse)
        #$locale_monetary = $this -> config_get_setting("locale_monetary");
        /*
          $currencies_default_currency = $this->config_get_setting("currencies_default_currency");

          # Can't write the config withoiut remove filename argh
          $client_config = $this->config_get_site_config();
          $client_config->filename = null;
          $client_config->set_setting("paypal_currency_code", $currencies_default_currency);

          if ($this->cart->currency != null) {
          #echo "using cart currency";
          $client_config->set_setting("paypal_currency_code", $this->cart->currency);
          }

          #$payee_email_address = $client_config->get_setting(paypal_payee_email_address");

          #$server_config = $this ->get_
          #$paypal_config = $this->get_machine_config("server_config", $region);
          #$server_paypal_config = $this->get_machine_config("server_config", $region);
          #$server_paypal_config->set_setting("paypal_currency_code", $currencies_default_currency);
          #$server_paypal_config->set_setting("payee_email_address", $payee_email_address);
         * 
         */

        $server_paypal_config = $this->get_machine_config("server_config", $region);

        #var_dump($server_paypal_config);

        $client_config = $this->config_get_site_config();

        $paypal_config = new \shared_code\configuration(null);
        $paypal_config->set_setting("paypal_currency_code", $client_config->get_setting("currencies_default_currency"));
        $paypal_config->set_setting("currencies_default_currency", $client_config->get_setting("currencies_default_currency"));
        $paypal_config->set_setting("paypal_live_or_sandbox", $client_config->get_setting("paypal_live_or_sandbox"));


        $use_paypal_express = $client_config->get_setting("use_paypal_express");
        $use_paypal_express_third_party = $client_config->get_setting("use_paypal_express_third_party");

        if ($use_paypal_express_third_party == true) {
            $paypal_live_or_sandbox = $client_config->get_setting("paypal_live_or_sandbox");

            if ($paypal_live_or_sandbox == "live") {
                $paypal_config->set_setting("paypal_api_user", $server_paypal_config->get_setting("paypal_api_user"));
                $paypal_config->set_setting("paypal_api_pwd", $server_paypal_config->get_setting("paypal_api_pwd"));
                $paypal_config->set_setting("paypal_api_signature", $server_paypal_config->get_setting("paypal_api_signature"));
                $paypal_config->set_setting("paypal_server_url", $server_paypal_config->get_setting("paypal_server_url"));
                $paypal_config->set_setting("paypal_checkout_url", $server_paypal_config->get_setting("paypal_checkout_url"));
            } else {
                $paypal_config->set_setting("paypal_api_user", $server_paypal_config->get_setting("paypal_api_user_sandbox"));
                $paypal_config->set_setting("paypal_api_pwd", $server_paypal_config->get_setting("paypal_api_pwd_sandbox"));
                $paypal_config->set_setting("paypal_api_signature", $server_paypal_config->get_setting("paypal_api_signature_sandbox"));
                $paypal_config->set_setting("paypal_server_url", $server_paypal_config->get_setting("paypal_server_url_sandbox"));
                $paypal_config->set_setting("paypal_checkout_url", $server_paypal_config->get_setting("paypal_checkout_url_sandbox"));
            }
            $paypal_config->set_setting("paypal_payee_email_address", $client_config->get_setting("paypal_receiver_email"));
            $paypal_config->set_setting("paypal_receiver_email", $client_config->get_setting("paypal_receiver_email"));

            #var_dump($paypal_config);


            $this->paypal = new \paypal_code\paypal_third_party($this, $paypal_config, "client_site");
        } elseif ($use_paypal_express == true) {
            $paypal_live_or_sandbox = $client_config->get_setting("paypal_live_or_sandbox");

            if ($paypal_live_or_sandbox == "live") {

                $paypal_config->set_setting("paypal_server_url", $server_paypal_config->get_setting("paypal_server_url"));
                $paypal_config->set_setting("paypal_checkout_url", $server_paypal_config->get_setting("paypal_checkout_url"));
            } else {
                $paypal_config->set_setting("paypal_server_url", $server_paypal_config->get_setting("paypal_server_url_sandbox"));
                $paypal_config->set_setting("paypal_checkout_url", $server_paypal_config->get_setting("paypal_checkout_url_sandbox"));
            }

            $paypal_config->set_setting("paypal_api_user", $client_config->get_setting("paypal_api_user"));
            $paypal_config->set_setting("paypal_api_pwd", $client_config->get_setting("paypal_api_pwd"));
            $paypal_config->set_setting("paypal_api_signature", $client_config->get_setting("paypal_api_signature"));
            $paypal_config->set_setting("paypal_receiver_email", $client_config->get_setting("paypal_receiver_email"));
            #var_dump($paypal_config);

            $paypal_config->set_setting("paypal_payee_email_address", "");
            $this->paypal = new \paypal_code\paypal_third_party($this, $paypal_config, "client_site");
        }


        $cart->refresh_products();
        #$order -> prefix = 
        #var_dump($this->cart->calculate());
        #var_dump($this -> cart -> get_count());
        #var_dump($this -> get_product_and_associated(1, true));  
        #$this -> create_stock_hold(4,3,3);
        #$this->hold_stock();
        #list($stock_holds, $count) = $this -> get_all_stock_holds();
#        var_dump($stock_holds);
    }

    public function start_database($shop_region) {

        $client_config = $this->get_client_config();

        $database_server_config = $this->get_machine_config("database", $shop_region);

        #$database_passwords_config = $this->get_machine_config("database_passwords", $shop_region);
        $database_passwords_config = new \shared_code\configuration(null);

        $all_sites_memcached_store = new \shared_code\memcached_store("is_database_up", "no_version", $shop_region);

        $database = new database($database_server_config, $database_passwords_config, $client_config, $all_sites_memcached_store);

        return $database;
    }

    public function get_client_config() {
        #$client_root = $GLOBALS["sb_client_site_path"];
        #$GLOBALS["sb_client_path"] =  $GLOBALS["sb_client_site_path"] . "/../";
# CHICKEN AND EGG PROBLEM!!!! set site id so we can get config
# from folder
#echo getcwd();
        $client_root = $GLOBALS["sb_client_site_path"];
#echo $this -> client_root;
# the configuration root never changes.
        $configuration_root = $client_root . '/configuration';

# Open database configuration
        $site_config_filename = $configuration_root . "/client_config.txt";

#echo $site_config_filename;
//open config. read in parameters.
        $site_config = new \shared_code\configuration($site_config_filename);

        return $site_config;
    }

    public function get_version_config() {

        #$client_root = $GLOBALS["sb_client_site_path"];
        #$GLOBALS["sb_client_path"] =  $GLOBALS["sb_client_site_path"] . "/../";
# CHICKEN AND EGG PROBLEM!!!! set site id so we can get config
# from folder
#echo getcwd();
        $client_root = $GLOBALS["sb_client_site_path"];



# Open database configuration
        $version_config_filename = $client_root . "/www/version.txt";

        $version_config = new \shared_code\configuration($version_config_filename);

        return $version_config;
    }

    public function get_machine_config($prefix, $region) {
        /*
          # Open the memcache config and get the server list for this server region
          $config_filename = "/etc/shopsbee/" . $prefix . "_" . $region . ".txt";
          //$config_filename = $GLOBALS["sb_code_path"] . '/machine_config' . "/" . $prefix . "_" . $region . ".txt";

          $config = new \shared_code\configuration($config_filename);

          return $config;
         * 
         */

        $machine_configuration_class = new \shared_code\machine_configuration();

        $config = $machine_configuration_class->get_machine_config($prefix, $region);

        return $config;
    }

    public function get_machine_region() {
        $machine_configuration_class = new \shared_code\machine_configuration();

        $local_region = $machine_configuration_class->get_machine_region();
        return $local_region;
    }

    public function server_config_get_setting($setting_name) {
        #$code_root = "/usr/local/shopsbee";
        #$shop = $this->get_shop();
        #$region = $shop->region;
        $region = $this->get_machine_region();

        $code_root = $GLOBALS["sb_code_path"];
        $global_config_filename = $code_root . "/machine_config/server_config_" . $region . ".txt";

        $global_config = new \shared_code\configuration($global_config_filename);

        $setting = $global_config->get_setting($setting_name);

        return $setting;
    }

    // Put template var
    public function put_var($key, $value) {
        $this->vars[$key] = $value;
    }

    public function database_create_payment($fields) {
        $this->database->add_payment($fields);
    }

    // Get tempalte var
    public function get_var($key) {
        $value = null;
        if (isset($this->vars[$key])) {
            $value = $this->vars[$key];
        }
        return $value;
    }

    public function &database_create_account($username, $password, $forename, $surname, $nickname, $accountstate) {

        $hashed_password = hash("sha512", $password, false);
        $password_version_hash = 0;
        try {
            $userid_in_array = $this->database->create_account($username, $hashed_password, $forename, $surname, $nickname, $accountstate, $password_version_hash);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $userid_in_array;
    }

    public function &database_check_user_exists($username) {
        $usercount = $this->database->check_user_exists($username);
        return $usercount;
    }

    public function session_get_user() {
        $user = null;
        if (isset($_SESSION["client_site_login"]["theme_user"]["user"])) {
            #echo $_SESSION["client_site_login"]["theme_user"];
            $user_serialised = $_SESSION["client_site_login"]["theme_user"]["user"];
            $user = unserialize($user_serialised);
        }

        #var_dump($_SESSION["client_site_login"]["theme_user"]);
        //var_dump($user);
        return $user;
    }

    public function session_set_user(user $user) {
        //echo "set the user";
        //var_dump($user);
        $_SESSION["client_site_login"]["theme_user"]["user"] = serialize($user);
    }

    public function attach_images_to_products($products, $allproductsimages) {
        #echo "attached";
        $variantid = 0;

        foreach ($products as $productid => $product) {
            if (isset($allproductsimages[$productid])) {
                #var_dump($allproductsimages[$productid]);
                $images = $allproductsimages[$productid];

                #var_dump($images);
                $images_clone = array_values($images);
                #var_dump($images);

                foreach ($images_clone as $key => $image) {
                    #var_dump($image);
                    if ($image->variantid != $variantid) {
                        unset($images_clone[$key]);
                    }
                }

                #var_dump($images_clone);

                $product->set_images($images_clone);
            }
        }
        return $products;
    }

    public function attach_images_to_variants($products, $allproductsimages) {
        #echo "attached";

        foreach ($products as $productid => $product) {
            if (isset($allproductsimages[$productid])) {
                #var_dump($allproductsimages[$productid]);
                $product_images = $allproductsimages[$productid];
                $images = array_values($product_images);

                //var_dump($images);
                #var_dump($images);
                #var_dump($images);

                $variants = $product->variants;
                foreach ($variants as $variantid => $variant) {

                    #$variant->set_images($images);
                    #$images_clone = clone ($images);
                    $images_clone = array_values($images);

                    foreach ($images_clone as $key => $image) {
                        if ($image->variantid != $variantid) {
                            unset($images_clone[$key]);
                        }
                    }

                    $variant->set_images($images_clone);
                }
            }
        }
        return $products;
    }

    public function attach_images_to_categories($categories, $allproductsimages) {
        #echo "attached";
        $variantid = 0;

        foreach ($categories as $categoryid => $category) {
            if (isset($allproductsimages[$categoryid])) {
                #var_dump($allproductsimages[$productid]);
                $images = $allproductsimages[$categoryid];

                #var_dump($images);
                $images_clone = array_values($images);
                #var_dump($images);
                #var_dump($images_clone);

                $category->set_images($images_clone);
            }
        }
        return $categories;
    }
    
    public function attach_images_to_collections($categories, $allproductsimages) {
        #echo "attached";
        $variantid = 0;

        foreach ($categories as $categoryid => $category) {
            if (isset($allproductsimages[$categoryid])) {
                #var_dump($allproductsimages[$productid]);
                $images = $allproductsimages[$categoryid];

                #var_dump($images);
                $images_clone = array_values($images);
                #var_dump($images);
                #var_dump($images_clone);

                $category->set_images($images_clone);
            }
        }
        return $categories;
    }

    private $products = null;

    public function get_all_products_and_associated($options_array) {
        $include_provisioning_products = false;
        $include_provisioning_variants = false;
        $remove_out_of_stock_products = false;
        $use_default_currency = false;

        #echo "EXECUTED AFTER";
        #$this -> cart = new cart($this);
        #var_dump($this -> cart);
        $cart = $this->cart;
        $shipping_address = $cart->get_shipping_address();
        #$shipping_address = null;

        $country = "";
        $state = "";

        if ($shipping_address != null) {
            $country = $shipping_address->country;
            $state = $shipping_address->state;
        }

        foreach ($options_array as $key => $value) {
            $$key = $value;
        }

        $products = array();
        $memcached_store = $this->memcached_store;

        # Check if we have it in memory before going to memcache
        # Note: stale cache issues can occur on updates - this applies to admin_code
        # May also occur when product levels change!
        #if ($this->products != null) {
        #$products_count = count($products);
        #return array($this->products, $products_count);
        #}

        if ($use_default_currency == true) {
            $cart_currency = $this->get_default_currency();
        } else {
            $cart_currency = $this->get_cart_currency();
        }

        #echo "RETRIEVED";
        #var_dump($cart_currency -> code);

        $cart_currency_code = "";
        $cart_currency_exchange_rate = 1.0;
        if ($cart_currency != null) {
            $cart_currency_code = $cart_currency->code;
            $cart_currency_exchange_rate = $cart_currency->exchange_rate;
        }

        #var_dump($cart_currency);

        $searchkeyvalues = array("allproducts" => "allproducts",
            "provisioning" => $include_provisioning_products,
            "vprovisioning" => $include_provisioning_variants,
            "remove_out_of_stock_products" => $remove_out_of_stock_products,
            "country" => $country,
            "state" => $state,
            "cart_currency_code" => $cart_currency_code
        );

        $found = false;
        try {
            $products = $this->memcached_store->get_search($searchkeyvalues);
            #$products = array();
            #var_dump($products);
#var_dump($products);
            #echo "found";
            $found = true;
        } catch (\Exception $e) {
            $found = false;
            #echo "not found";
        }


        if ($found == false) {

            $products = $this->database->get_all_products();
            $variants = $this->database->get_all_variants_and_specials();


            foreach ($variants as $variant) {
                $variant->price = $variant->price * $cart_currency_exchange_rate;
                $variant->specialprice = $variant->specialprice * $cart_currency_exchange_rate;
                $variant->purchaseprice = $variant->purchaseprice * $cart_currency_exchange_rate;

                if ($variant->special != null) {
                    $special = $variant->special;
                    $special->specialprice = $special->specialprice * $cart_currency_exchange_rate;
                }
            }

            #var_dump(array_keys($variants));
            # This will be all the variants, including out of stock ones
            $this->store_special_expiry_times($variants);

            //var_dump($products);

            if ($include_provisioning_products == false) {
                foreach ($products as $key => $product) {
                    if ($product->is_provisioning == true) {
                        //echo $product -> id . "<br/>";
                        unset($products[$key]);
                        #echo "Removed product";
                    }
                }
            }

            foreach ($variants as $variant) {

                # Ignore provisioning variants using same var
                if ($include_provisioning_variants == false) {
                    #if ($include_provisioning_variants == false) {
                    #echo "ignoring" . $variant -> productid;
                    if ($variant->is_provisioning == true) {
                        //echo $product -> id . "<br/>";
                        continue;
                        #echo "Removed product";
                    }
                }

                $variant_productid = $variant->productid;

                if (isset($products[$variant_productid])) {
                    //Add the variant
                    $variants = $products[$variant_productid]->variants;
                    $variantid = $variant->variantid;
                    $variants[$variantid] = $variant;
                    $products[$variant_productid]->variants = $variants;
                }
            }

            if ($remove_out_of_stock_products == true) {
                foreach ($products as $key => $product) {
                    $in_stock = false;

                    if (($product->use_stocklevel == true) && ($product->hide_when_out_of_stock == true)) {
                        //If all variants are out of stock and use stocklevel is set... remove from listing
                        foreach ($variants as $variant) {
                            if ($variant->stocklevel > 0) {
                                $in_stock = true;
                                break;
                            }
                        }


                        if ($in_stock == false) {
                            unset($products[$key]);
                        }
                    }
                }
            }


            // Set the product price using lowest variant price (consequently this may not be
            // The lowest special price
            foreach ($products as $product) {
                $product_variants = $product->variants;

                if ($product->variants != array()) {

                    $keys = array_keys($product->variants);
                    $lpvariant = $product->variants[$keys[0]];
                    foreach ($product_variants as $product_variant) {
                        if ($product_variant->purchaseprice < $lpvariant->purchaseprice) {
                            $lpvariant = $product_variant;
                            #echo "setting lowest price as " . $lpvariant->purchaseprice;
                        }

                        if ($product_variant->purchaseprice == $lpvariant->purchaseprice) {
                            #var_dump($product_variant);
                            if (isset($product_variant->special) && ($product_variant->special->active == true)) {

                                $special1 = $product_variant->special->specialprice;
                                #echo $special1 . "a";
                                $special2 = null;
                                if (isset($lpvariant->special) && ($product_variant->special->active == true)) {
                                    $special2 = $lpvariant->special->specialprice;
                                    #echo $special1 . "b";
                                }
                                if (($special2 != null) && ($special1 < $special2)) {
                                    $lpvariant = $product_variant;
                                }

                                #echo $lpvariant -> special -> specialprice;
                            }
                        }
                    }




                    #var_dump($lpvariant);

                    $product->price = $lpvariant->price;
                    if ($lpvariant->special->active === true) {
                        $product->specialprice = $lpvariant->special->specialprice;
                    } else {
                        $product->specialprice = null;
                    }
                    $product->purchaseprice = $lpvariant->price;

                    #var_dump($product -> specialprice);

                    if (($product->specialprice !== NULL) && ($product->specialprice < $product->price)) {
                        $product->purchaseprice = $product->specialprice;
                    }
                }



                $product->variant_count = count($product->variants);
            }

            foreach ($products as $product) {
                $productid = $product->productid;
                $taxes = $this->get_product_taxes($productid);

                $product->taxes = $taxes;
            }


            $cart = $this->cart;
            $shipping_address = $cart->get_shipping_address();

            #var_dump($shipping_address);
            #$shipping_address = null;
            $coupon = null;

            $all_tax_rules = $this->get_all_taxes();

            #&product causes bug.
            foreach ($products as $product) {
                $tax_class = new tax_calculator($this);
                $tax_rules_that_apply = $product->taxes;

                $variants = $product->variants;
                $address = $shipping_address;

                foreach ($variants as $variant) {
#var_dump($this -> get_shipping_address());
                    # For subtotal based tax
                    #var_dump($products);
                    #$product = $tax_class->get_product_tax_before_coupons($product, $shipping_address, $coupon);
                    $amount = $variant->get_purchaseprice();

                    list($product_tax, $product_tax_matched_rules) = $tax_class->calculate_tax_on_amount($amount, $tax_rules_that_apply, $all_tax_rules, $address);

                    $variant->purchaseprice_unit_tax = $product_tax;
                    $variant->purchaseprice_unit_taxes = $product_tax_matched_rules;
                }

                $amount = $product->purchaseprice;

                list($product_tax, $product_tax_matched_rules) = $tax_class->calculate_tax_on_amount($amount, $tax_rules_that_apply, $all_tax_rules, $address);

                $product->purchaseprice_unit_tax = $product_tax;
                #$variant->unit_taxes = $product_tax_matched_rules;         
            }


            // Set the product weight using lowest variant weight
            foreach ($products as $product) {
                $product_variants = $product->variants;

                if ($product->variants != array()) {

                    $keys = array_keys($product->variants);
                    $lpvariant = $product->variants[$keys[0]];
                    foreach ($product_variants as $product_variant) {
                        if ($product_variant->weightinkg < $lpvariant->weightinkg) {
                            $lpvariant = $product_variant;
                            //echo "setting lowest price as " . $lpvariant->weightinkg;
                        }
                    }
                    $product->weightinkg = $lpvariant->weightinkg;
                }
            }

            $productoptions = $this->database->get_all_productoptions();

            #var_dump($productoptions);
            #var_dump($products);
            #var_dump(array_keys($products));
            #var_dump(count($productoptions));
            $i = 0;
            foreach ($productoptions as $option) {
                $i++;
                # Some products get unset due to provisioning etc

                $option_productid = $option->productid;

                $optionid = $option->optionid;

                #var_dump($products[$option_productid]);
                #if ($option->name != "") {
                if (isset($products[$option_productid])) {
                    $product = $products[$option_productid];

                    $options = $product->options;

                    $options[$optionid] = $option;

                    $product->options = $options;

                    #$products[$option_productid] -> 
                    # This doesn't re-key, just resorts it to proper order
                    # For some reason otherwise it refuses to 
                    # use 4,5,6 it uses 4,6,5 even tho DB provides 4,5,6! ARGH.

                    ksort($product->options);
                    #asort($product -> options);
                    #var_dump($product -> options);
                    #var_dump(count($product -> options));
                    #die();
                }
                #echo $i;
                #}
            }
            #echo $i;
            #die();

            foreach ($products as $product) {
                $variants = $product->variants;
                $productoptions = $product->options;
                foreach ($variants as $variant) {
                    $variant->options = $productoptions;
                    #foreach($variantoptions as $variant -> )
                }
            }

            #var_dump($products);


            $variantoptions = $this->database->get_all_variantoptions();

            #var_dump($productoptions);
            #var_dump($products);
            #var_dump(array_keys($products));
            foreach ($variantoptions as $option) {
                # Some products get unset due to provisioning etc

                $option_productid = $option->productid;
                $option_variantid = $option->variantid;

                $optionid = $option->optionid;
                $value = $option->value;

                #var_dump($products[$option_productid]);


                if (isset($products[$option_productid])) {
                    # Get the product
                    $product = $products[$option_productid];

                    # Get products variants
                    $variants = $product->variants;

                    # This case only occurs if there's a problem of some sort
                    # Get the variant
                    if (isset($variants[$option_variantid])) {
                        #  Get the variants options and append
                        $variant = $variants[$option_variantid];

                        $options = $variant->options;
                        #asort($options);
                        #$option -> name = "A";

                        $options[$optionid] = $option;

                        $variant->options = $options;

                        #var_dump($variant -> options);
                    }
                    #$products[$option_productid] -> 
                    #var_dump($product -> options);
                }
            }

            #$has_options = false;           

            foreach ($products as $product) {
                $has_options = false;
                $options = $product->options;

                $product->option_count = count($options);


                $options = $product->options;
                foreach ($options as $option) {
                    $option_name = $option->name;
                    if ($option_name != "") {
                        #echo "has name";
                        $has_options = true;
                        break;
                    }
                }

                #var_dump($options);

                $product->has_options = $has_options;

                #var_dump($product -> has_options);
            }

            $productmetadata = $this->database->get_all_productmetadata();

            #var_dump($productmetadatas);
            #var_dump($products);
            #var_dump(array_keys($products));
            #var_dump(count($productmetadatas));
            $i = 0;
            foreach ($productmetadata as $metadata) {
                $i++;
                # Some products get unset due to provisioning etc

                $metadata_productid = $metadata->productid;

                $metadataid = $metadata->metadataid;

                #var_dump($products[$metadata_productid]);
                #if ($metadata->name != "") {
                if (isset($products[$metadata_productid])) {
                    $product = $products[$metadata_productid];

                    $metadatas = $product->metadata;

                    $metadatas[$metadataid] = $metadata;

                    $product->metadata = $metadatas;

                    #$products[$metadata_productid] -> 
                    # This doesn't re-key, just resorts it to proper order
                    # For some reason otherwise it refuses to 
                    # use 4,5,6 it uses 4,6,5 even tho DB provides 4,5,6! ARGH.

                    ksort($product->metadata);
                    #asort($product -> metadatas);
                    #var_dump($product -> metadatas);
                    #var_dump(count($product -> metadatas));
                    #die();
                }
                #echo $i;
                #}
            }
            #echo $i;
            #die();

            foreach ($products as $product) {
                $variants = $product->variants;
                $productmetadata = $product->metadata;
                foreach ($variants as $variant) {
                    $variant->metadata = $productmetadata;
                    #foreach($variantmetadatas as $variant -> )
                }
            }

            #var_dump($products);


            $variantmetadata = $this->database->get_all_variantmetadata();

            #var_dump($productmetadatas);
            #var_dump($products);
            #var_dump(array_keys($products));
            foreach ($variantmetadata as $metadata) {
                # Some products get unset due to provisioning etc

                $metadata_productid = $metadata->productid;
                $metadata_variantid = $metadata->variantid;

                $metadataid = $metadata->metadataid;
                $value = $metadata->value;

                #var_dump($products[$metadata_productid]);


                if (isset($products[$metadata_productid])) {
                    # Get the product
                    $product = $products[$metadata_productid];

                    # Get products variants
                    $variants = $product->variants;

                    # This case only occurs if there's a problem of some sort
                    # Get the variant
                    if (isset($variants[$metadata_variantid])) {
                        #  Get the variants metadatas and append
                        $variant = $variants[$metadata_variantid];

                        $metadatas = $variant->metadata;
                        #asort($metadatas);
                        #$metadata -> name = "A";

                        $metadatas[$metadataid] = $metadata;

                        $variant->metadata = $metadatas;

                        #var_dump($variant -> metadatas);
                    }
                    #$products[$metadata_productid] -> 
                    #var_dump($product -> metadatas);
                }
            }

            foreach ($products as $product) {
                $productid = $product->productid;
                $comments = $this->get_comments_and_children($productid);
                $product->comments = $comments;
            }

            foreach ($products as $product) {
                $productid = $product->productid;
                list($reviews, $reviews_count) = $this->get_all_reviews_by_productid($productid);
                $product->reviews = $reviews;
            }





            #$cdn = "http://cb3.r.worldssl.net";
            $protocol = $this->get_protocol();
            $developer_disable_cdn = $this -> config_get_setting("developer_disable_cdn");
            $real_cdn = "";
            if ($developer_disable_cdn == true)
            {
               
            } else {
                $real_cdn = $this->config_get_setting("aws_cdn_location");
            }
            $site_id = $this->config_get_setting("site_id");


            $images = $this->database->get_all_images($protocol, $real_cdn, $site_id);

            $products = $this->attach_images_to_products($products, $images);

            $products = $this->attach_images_to_variants($products, $images);

            try {
                list($stored, $memcached_error_messages) = list($stored, $memcached_error_messages) = $memcached_store->store_search($searchkeyvalues, $products, $this->database_expiry);
            } catch (\Exception $e) {
                #echo $e->getMessage();
            }
        }


        /*
          # Currency
          $default_currency = $this->get_default_currency();
          $cart_currency = $this->get_cart_currency();

          $default_currency_code = $default_currency->code;

          #var_dump($cart_currency);

          if ($cart_currency != null) {
          $cart_currency_code = $cart_currency->code;

          if ($cart_currency_code != $default_currency_code) {
          $users_currency_multiplier = $cart_currency->exchange_rate;

          foreach ($products as $product) {
          $product->purchaseprice = $product->purchaseprice * $users_currency_multiplier;
          $variants = $product -> variants;
          foreach ($variants as $variant) {
          $variant->purchaseprice = $variant->purchaseprice * $users_currency_multiplier;
          $variant->price = $variant->price * $users_currency_multiplier;
          }
          }
          }
          } */


        list($stock_holds, $stock_hold_count) = $this->get_all_stock_holds();
        foreach ($stock_holds as $stock_hold) {
            #Products are keyed on ID hence as long as this remains this will work lol
            $productid = $stock_hold->productid;
            $variantid = $stock_hold->variantid;
            $number_held = $stock_hold->number_held;
            if (isset($products[$productid])) {
                $product = $products[$productid];
                $variants = $product->variants;
                if (isset($variants[$variantid])) {
                    $variant = $variants[$variantid];

                    $variant->stocklevel_with_holds = $variant->stocklevel_with_holds - $number_held;
                    $variant->stock_held = $variant->stock_held + $number_held;
                    if ($stock_hold->sessionid == session_id()) {
                        $variant->current_user_stock_held = $variant->current_user_stock_held + $number_held;
                        $variant->stocklevel_with_holds = $variant->stocklevel_with_holds + $number_held;
                        #echo "session IDs match " . $number_held;
                    } else {
                        
                    }
                }
            }
        }



        #var_dump($products);



        $products_count = count($products);
        return array($products, $products_count);
    }

    #$variants = $this->database->get_all_variants_and_specials();

    public function store_special_expiry_times($variants) {
        # Doesn't matter if we include provisioning stuff
        # Out of stock products... are a different problem
        #$products = $this -> get_all_products_and_associated(false, false, false);
        # Add all variants to 

        $now = $this->datetime_convert_date_string_from_machine_timezone_into_utc_datetime_text("NOW");

        $now = strtotime($now);

        $lowest_end_timestamp = null;

        # We want to know when the earliest future dated special will expire
        foreach ($variants as $variant) {
            $special = $variant->special;
            if (($special->end != null) && ($special->end != "00:00:00 00-00-00")) {
                $end = strtotime($special->end);


                //Ignore specials that have already expired.
                if ($now > $end) {
                    continue;
                } else {

                    if ($lowest_end_timestamp == null) {
                        $lowest_end_timestamp = $end;
                    }
                    if ($end < $lowest_end_timestamp) {
                        $lowest_end_timestamp = $end;
                    }
                }
            }
        }
        #echo "Lowest active end timestamp " . $lowest_end_timestamp;

        $lowest_start_timestamp = null;

        # We want to know when the earliest future dated special will start
        foreach ($variants as $variant) {
            $special = $variant->special;
            if (($special->start != null) && ($special->start != "00:00:00 00-00-00")) {
                $start = strtotime($special->start);

                //Ignore specials that have already started
                if ($now > $start) {
                    continue;
                } else {

                    if ($lowest_start_timestamp == null) {
                        $lowest_start_timestamp = $start;
                    }
                    if ($start < $lowest_start_timestamp) {
                        $lowest_start_timestamp = $start;
                    }
                }
            }
        }
        #echo "Lowest active start timestamp " . $lowest_start_timestamp;
        # Only update the configuration file if there has been a change - avoids us writing out hundreds of thousands of times
        $old_start_timestamp = $this->config_get_setting("nextspecialstart_utc");
        $old_end_timestamp = $this->config_get_setting("nextspecialend_utc");

        if ($old_start_timestamp != $lowest_start_timestamp) {
            $this->config_set_setting("nextspecialstart_utc", $lowest_start_timestamp);
        }
        if ($old_end_timestamp != $lowest_end_timestamp) {
            $this->config_set_setting("nextspecialend_utc", $lowest_end_timestamp);
        }
    }

    public function get_protocol() {
        $protocol = false;
        if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == "on")) {
            $protocol = "https";
        } else {
            $protocol = "http";
        }

        return $protocol;
    }

    public function database_get_last_10_customer_orders($userid) {
        $orders = $this->database->get_last_10_customer_orders($userid);
        return $orders;
    }

    public function get_products_in_category_and_children($categoryid) {
        # Get all the products (well.. not all anymore :~P)        
        $options_array = array("include_provisioning_products" => false, "include_provisioning_variants" => false, "remove_out_of_stock_products" => true);
        list($allproducts, $products_count) = $this->get_all_products_and_associated($options_array);

        /* allproducts */

        # Get all categories
        list($allcategories, $allcategories_count) = $this->get_all_categories();

        # Find the root category
        $parent_category = $this->get_category($categoryid);

        # Using the root category - build a tree instead of an array
        $parent_category = $this->get_category_children_of_parent_to_leaf($parent_category);

        $products_array = array();

        $products_array = $this->iterative_find_products_of_parent_and_children($parent_category, $allproducts, 0);

        $products_count = count($products_array);

        return array($products_array, $products_count);
    }

    public function get_root_category($categories) {
        $root_category = null;
        if (!empty($categories)) {
            foreach ($categories as $category) {
                if ($category->parentid == 0) {
                    $root_category = $category;
                    break;
                }
            }
        }

        return $root_category;
    }

    public function get_category_children_of_parent_to_leaf($category) {
        # Get the category     
        if ($category != null) {
            $categoryid = $category->categoryid;
            #TODO: refactor out the next line
            #$children = array();
            $children = $this->get_category_children($categoryid, 0);
            $category->set_children($children);


            $this->iterative_find_children($category, 0);
        }

        return $category;
    }

    public function iterative_find_children($root_category, $depth) {
        if ($root_category == null) {
            return;
        }
        $depth = $depth + 1;

        $categoryid = $root_category->id;
        #TODO: remove this next line / refactor
        $children = $this->get_category_children($categoryid, $depth);
        $root_category->set_children($children);

        if ($children != array()) {
            foreach ($children as $child) {
                $this->iterative_find_children($child, $depth);
            }
        }
    }

    public function &get_category_children($categoryid, $depth) {
        $children = array();
        if ($depth > 5) {

            return $children;
        }
        list($categories_in_array, $categories_count) = $this->get_all_categories();

        foreach ($categories_in_array as $cid => $category) {
            if ($category->parentid == $categoryid) {
                $children[$cid] = $category;
            }
        }

        return ($children);
    }

    public $categories = null;
    private $subscription_lists = array();

    public function get_all_categories() {

        # Check if we have it in memory before going to memcache
        # Do not use this in admin_code as it will produce stale results
        #if ($this->categories != null) {
        #return $this->categories;
        #}

        $categories = array();
        $memcached_store = $this->memcached_store;

        $searchkeyvalues = array("allcategories" => "allcategories");

        $found = false;
        try {
            $categories = $this->memcached_store->get_search($searchkeyvalues);

            $found = true;
        } catch (\Exception $e) {
            $found = false;
        }

        if ($found == false) {

            $categories = $this->database->get_all_categories();

            $protocol = $this->get_protocol();
                        $real_cdn = "";
            $developer_disable_cdn = $this -> config_get_setting("developer_disable_cdn");
            if ($developer_disable_cdn == true)
            {
               
            } else {
                $real_cdn = $this->config_get_setting("aws_cdn_location");
            }
            $site_id = $this->config_get_setting("site_id");

            $category_images = $this->database->get_all_category_images($protocol, $real_cdn, $site_id);

            $categories = $this->attach_images_to_categories($categories, $category_images);


            foreach ($categories as &$category_outer_loop) {
                $children = array();
                //Attach the children to the category so we can  get the child category images
                // $category = $this->get_category_children_of_parent_to_leaf($category);

                foreach ($categories as $cid => $category_inner_loop) {
                    if ($category_inner_loop->parentid == $category_outer_loop->categoryid) {
                        $children[$cid] = $category_inner_loop;
                        //var_dump($category_inner_loop -> images);
                    }
                }
                $category_outer_loop->set_children($children);                
            }                        


            try {
                list($stored, $memcached_error_messages) = $memcached_store->store_search($searchkeyvalues, $categories, $this->database_expiry);
            } catch (\Exception $e) {
                #echo $e->getMessage();
                #   echo "Unable to store";
            }
        }

        $this->categories = $categories;

        $categories_count = count($categories);

        return array($categories, $categories_count);
    }

    public function get_category_and_parents_to_root($parentid) {
        $category_list = array();

        list($categories, $category_count) = $this->get_all_categories();

        $root_category = $this->get_root_category($categories);

        if ($root_category == null) {
            return $category_list;
        }

        $categoryid = $parentid;

        if (!isset($categoryid)) {
            $category_list[] = $root_category;
        }

        if (isset($categoryid)) {
            # Get the category and parents up 5 levels.
            for ($i = 0; $i < 5; $i++) {
                unset($category);

                $category = null;
                if (isset($categories["$categoryid"])) {
                    # DO NOT USE =& here - php crashes.

                    $category = $categories["$categoryid"];
                }
                if ($category == null) {
                    break;
                }

                $parentid = $category->parentid;


                # Set the categoryid we want to retrieve next
                $categoryid = $parentid;

                $category_list[] = $category;

                # If we're at the root category, exit
                if ($parentid == 0) {
                    break;
                }
            }
        }

        $category_list = array_reverse($category_list);

        return $category_list;
    }

    # Surely we already hve a function called this?

    public function get_products_and_specials_in_category($categoryid, $allproducts) {
        $foundproducts = array();

        if ($allproducts != null) {
            # Iteratate over products, finding products matching category
            foreach ($allproducts as $productid => $product) {
                if ($product->categoryid == $categoryid) {
                    $foundproducts[$productid] = $product;
                }
            }
        }
        return $foundproducts;
    }

    /*
     *  Traverse the tree, finding all products and specials
     */

    public function iterative_find_products_of_parent_and_children($root_category, $allproducts, $depth) {
        if ($root_category == null) {
            return array();
        }
        $products = array();

        # DO NOT SEARCH BELOW LEVEL 5
        # THIS WILL STOP INFINITE LOOPS
        if ($depth > 5) {
            return $products;
        }

        $children = $root_category->get_children();

        $categoryid = $root_category->id;

        $products_in_array = $this->get_products_and_specials_in_category($categoryid, $allproducts);

        if ($products_in_array != null) {
            $products = array_merge($products, $products_in_array);
        }

        if ($children != null) {

            # Find the products of the children and recursively combine
            foreach ($children as $child) {
                $depth++;
                $products_in_array = $this->iterative_find_products_of_parent_and_children($child, $allproducts, $depth);

                if ($products_in_array != null) {
                    $products = array_merge($products, $products_in_array);
                }
            }
        }

        return $products;
    }

    public function get_product_and_associated($productid, $options_array) {
        $product = null;
        #var_dump($productid);
        #die();
        $include_provisioning_variants = false;
        $use_default_currency = false;

        foreach ($options_array as $key => $value) {
            $$key = $value;
        }

        $include_provisioning_products = true;

        $options_array = array("include_provisioning_products" => $include_provisioning_products, "include_provisioning_variants" => $include_provisioning_variants, "remove_out_of_stock_products" => false
            , "use_default_currency" => $use_default_currency);
        list($products, $products_count) = $this->get_all_products_and_associated($options_array);

        #echo $productid;

        if ($products != array()) {
            if (isset($products[$productid])) {
                $product = $products[$productid];
            }
        }

        return ($product);
    }

    public function get_all_news() {

        # Check if we have it in memory before going to memcache
        #if ($this->news != null) {
        #return $this->news, $count;
        #}



        $news = array();
        $memcached_store = $this->memcached_store;


        $searchkeyvalues = array("allnews" => "allnews");

        $found = false;
        try {
            $news = $this->memcached_store->get_search($searchkeyvalues);

            $found = true;
        } catch (\Exception $e) {
            $found = false;
        }

        if ($found == false) {

            $news = $this->database->get_all_news();

            try {
                list($stored, $memcached_error_messages) = $memcached_store->store_search($searchkeyvalues, $news, $this->database_expiry);
            } catch (\Exception $e) {
                #echo $e->getMessage();
                #   echo "Unable to store";
            }
        }

        $this->news = $news;
        $news_count = count($news);

        return array($news, $news_count);
    }

    public function sanitise_string($string) {
        return filter_var($string, FILTER_SANITIZE_STRING);
    }

    public function validate_integer($int) {
        return filter_var($int, FILTER_VALIDATE_INT);
    }

    public function validate_hashcode_sha1($code) {
        $code = filter_var($code, FILTER_SANITIZE_STRING);

        if ((is_bool($code)) && ((bool) $code == false)) {
            return false;
        }

        /* Not strictly necessary, but check the string is a 40 byte hashcode */
        /* Note: Users can already work out we are using SHA-1 from their activation emails */
        if (!preg_match("/^[A-Fa-f0-9]{1,40}$/", $code)) {
            return false;
        }

        return $code;
    }

    public function &get_category($categoryid) {
        list($categories, $categories_count) = $this->get_all_categories();

        $foundcategory = null;
        if (isset($categories[$categoryid])) {
            $foundcategory = $categories[$categoryid];
        }
        return ($foundcategory);
    }

    public function &get_category_by_url($url) {
        list($categories, $categories_count) = $this->get_all_categories();

        # Simple iterate is fine for the categories.
        #$prefix = "/shop/";
        $prefix = "";
        $url = $prefix . $url;
        $foundcategory = null;
        foreach ($categories as $category) {
            $decodedurl = $category->decodedurl;
            if ($decodedurl == $url) {
                $foundcategory = $category;
                break;
            }
        }

        return ($foundcategory);
    }
   

    public function &get_product_by_url($url) {

        $options_array = array("include_provisioning_products" => false, "include_provisioning_variants" => false, "remove_out_of_stock_products" => false);
        list($products, $products_count) = $this->get_all_products_and_associated($options_array);
        #TODO: make a memcached hashed search
        # Simple iterate until I have time to make a memcached hash search
        # Probably unnecessary

        $prefix = "";
        $url = $prefix . $url;

        $foundproduct = null;

        foreach ($products as $product) {
            $decodedurl = $product->decodedurl;

            if ($decodedurl == $url) {
                $foundproduct = $product;
                break;
            }
        }

        return ($foundproduct);
    }

    public function &get_collection_by_url($url) {
        list($collections, $collection_count) = $this->get_all_collections();


        $prefix = "";
        # Simple iterate is fine for the categories/collections.
        $url = $prefix . $url;
        $foundcollection = null;
        foreach ($collections as $collection) {
            $decodedurl = $collection->decodedurl;
            if ($decodedurl == $url) {
                $foundcollection = $collection;
                break;
            }
        }

        return ($foundcollection);
    }

    # TODO: Use memcached

    public function get_all_coupons() {
        //return $this->database->get_all_coupons();
        # Check if we have it in memory before going to memcache
        #if ($this->news != null) {
        #return $this->news, $count;
        #}



        $coupons = array();
        $memcached_store = $this->memcached_store;


        $searchkeyvalues = array("allcoupons" => "allcoupons");

        $found = false;
        try {
            $coupons = $this->memcached_store->get_search($searchkeyvalues);

            $found = true;
        } catch (\Exception $e) {
            $found = false;
        }

        if ($found == false) {

            $coupons = $this->database->get_all_coupons();

            try {
                list($stored, $memcached_error_messages) = $memcached_store->store_search($searchkeyvalues, $coupons, $this->database_expiry);
            } catch (\Exception $e) {
                #echo $e->getMessage();
                #   echo "Unable to store";
            }
        }

        $this->coupons = $coupons;
        $coupons_count = count($coupons);

        return array($coupons, $coupons_count);
    }

    public function do_tag_filter($products_array, $tag) {
        # Tag must have a value
        # Calling function already does this
        /*
          if ($tag == null)
          {
          echo "returning as no tag";
          return;
          } */

        foreach ($products_array as $products_array_key => &$product) {

            $product_tags = $product->tags;
            #If there are no tags, remove this product from the array
            if ($product_tags == array()) {
                unset($products_array[$products_array_key]);
                continue;
            }

            $found = false;
            $found_key = null;

            foreach ($product_tags as $product_tags_key => &$product_tag) {
                if ($product_tag == $tag) {
                    $found = true;
                    $found_key = $product_tags_key;
                    break;
                }
            }


            if ($found == false) {
                unset($products_array[$products_array_key]);
            }
        }

        #reindex array - important
        $products_array = array_values($products_array);


        return $products_array;
    }

    public function get_product_array_tags($products_array, $base_url, $prefix) {
        $category_tags = array();

        foreach ($products_array as $product) {

            $product_tags = $product->tags;
            # merge the product tags with the category tags
            # array merge is useless, only merges keys
            # $category_tags = array_merge($category_tags, $product_tags);
            foreach ($product_tags as $product_tag) {
                # find the tag in the other array (category tags)
                $found = false;
                foreach ($category_tags as $category_tag) {
                    if ($category_tag->name == $product_tag) {
                        $category_tag->count++;
                        $found = true;
                        break;
                    }
                }

                if ($found == false) {
                    $category_tags[] = new tag($product_tag);
                }
            }
        }

        //sort it into descending count
        foreach ($category_tags as $key => $category_tag) {
            $category_tag->set_comparator($category_tag->count);
        }

        $quick_sort = new \client_code\quick_sort();
        $category_tags = $quick_sort->sort($category_tags);

        #Set the tag URLs            
        foreach ($category_tags as $key => $category_tag) {
            $tag_text = ($category_tag->name);
            $tag_encoded = rawurlencode($tag_text);
            $category_tag->url = $base_url . "tag=" . $tag_encoded;
        }

        return $category_tags;
    }

    public function get_brand_array_tags($products_array, $base_url, $prefix) {
        $product_brands = array();

        foreach ($products_array as $product) {

            $brand = $product->brand;
            # merge the product tags with the category tags
            # array merge is useless, only merges keys
            # $category_tags = array_merge($category_tags, $product_tags);
            # try to find the brand in the list, otherwise add it
            # Ignore where there is no brand name in the product
            if ($brand == null) {
                continue;
            }
            $found = false;
            foreach ($product_brands as $product_brand) {

                #echo "searching for " . $product_tag;
                # find the tag in the other array (category tags)

                if ($product_brand->name == $brand) {
                    $product_brand->count++;
                    $found = true;
                    break;
                }
            }

            if ($found == false) {
                $product_brands[] = new brand($brand);
            }
        }

        //sort it into descending count

        foreach ($product_brands as $key => $product_brand) {
            $product_brand->set_comparator($product_brand->count);
        }

        $quick_sort = new \client_code\quick_sort();
        $product_brands = $quick_sort->sort($product_brands);



        #Set the tag URLs            
        foreach ($product_brands as $key => $product_brand) {
            #$category_tag->url = $request_root
            $brand_text = ($product_brand->name);
            $brand_encoded = rawurlencode($brand_text);
            $product_brand->url = $base_url . "brand=" . $brand_encoded;
        }

        return $product_brands;
    }

    #Remove from the products array based on tag

    public function do_brand_filter($products_array, $brand) {
        # Tag must have a value
        #var_dump($tag);
        # Calling function already does this
        /*
          if ($tag == null)
          {
          echo "returning as no tag";
          return;
          } */

        foreach ($products_array as $products_array_key => &$product) {

            $product_brand = $product->brand;

            #If there are no tags, remove this product from the array
            if ($product_brand == null) {
                unset($products_array[$products_array_key]);

                continue;
            }



            $found = false;
            $found_key = null;

            if ($product_brand == $brand) {
                $found = true;
                $found_key = $brand;
                continue;
            }



            if ($found == false) {

                unset($products_array[$products_array_key]);
            }
        }

        #reindex array - important
        $products_array = array_values($products_array);


        return $products_array;
    }

    public function get_products_and_specials_in_category_and_children_paged($categoryid, $sort_type, $sort_order, $brand, $tag, $current_page, $page_size) {
        list ($products_array, $products_count) = $this->get_products_in_category_and_children($categoryid);

        if ($brand != null) {
            $products_array = $this->do_brand_filter($products_array, $brand);
            $products_count = count($products_array);
        }

        #filter by tag
        if ($tag != null) {
            $products_array = $this->do_tag_filter($products_array, $tag);
            $products_count = count($products_array);
        }

        #sort here
        if ($sort_type != null) {
            $products_array = $this->dosort($products_array, $sort_type, $sort_order);
        }

        $page_count = $this->get_page_count($products_count, $page_size);

        # reindex array to ensure array keys start from 0 before paginating (as DB keys start from 1)
        #$products_array = array_values($products_array);


        $page_products_array = $this->get_page_of_stuff($products_array, $products_count, $current_page, $page_size);

        return array($page_products_array, $products_count, $page_count);
    }

    // Special note about this function, it produces the correct results
    // on arrays that start with 0, 1, 2, 3, 4 , 5 and are consecutive
    // Otherwise you must reindex the array keys!!
    public function get_page_of_stuff($products_array, $products_count, $current_page, $page_size) {
        if ($current_page == null) {
            $current_page = 1;
        }

        #$page_products_array = #
        # Only return the products for this page - make an array with ref to the above
        # Get the xth element of the array
        $page_products_array = array();
        $start = ($current_page - 1) * $page_size;
        for ($i = $start; $i < $start + $page_size; $i++) {
            #Avoid going out of bounds please!
            if ($i >= $products_count) {
                break;
            }
            # By reference
            $page_products_array[$i] = & $products_array[$i];
        }
        return $page_products_array;
    }

    public function get_page_count($products_count, $page_size) {
        $page_count = ceil($products_count / $page_size);
        return $page_count;
    }

    public function get_default_page_size($page_name) {
        # We need the unique site ID for the memcache key
        $pagesize = $this->config_get_setting("pagesize_" . $page_name);

        $pagesize = (int) $pagesize;

        if ($pagesize == 0) {
            //$pagesize = 1;
            $pagesize = 20;
        }

        return $pagesize;
    }

    public function session_set_search_page($search_url) {
        $_SESSION["client_site"]["search_url"] = $search_url;
    }

    public function cart_is_empty() {
        return $this->cart->is_empty();
    }

    public function cart_reset_cart() {
        $result = false;
        if ($this->session_is_logged_in()) {
            $result = $this->cart->reset_cart();
        } else {
            $result = $this->cart->reset_cart_full();
        }
        return $result;
    }

    public function cart_set_billing_address($address) {
        return $this->cart->set_billing_address($address);
    }

    public function cart_get_billing_address() {
        return $this->cart->get_billing_address();
    }

    public function cart_add_product($product, $variantid) {
        return $this->cart->add_product($product, $variantid);
    }

    public function cart_remove_product($productid, $variantid) {
        return $this->cart->remove_product($productid, $variantid);
    }

    public function cart_get_cart_parcels() {
        $cart_contents = $this->cart->get_parcels();
        return ($cart_contents);
    }

    public function cart_set_shipping_address($address) {
        return $this->cart->set_shipping_address($address);
    }

    public function cart_get_shipping_address() {
        return $this->cart->get_shipping_address();
    }

    public function &database_create_activation($userid, $username, $hash) {
        $result = $this->database->create_activation($userid, $username, $hash);

        return ($result);
    }

    public function &database_activate($userid, $hash_hexed, $chars_to_match) {
        $result = $this->database->activate($userid, $hash_hexed, $chars_to_match);

        return ($result);
    }

    public function &login($username, $password, $keepmeloggedin) {

        #SHA512 is 128 Bytes or 64 Binary
        $hashed_password = hash("sha512", $password, false);
        #var_dump($hashed_password);
        #$hashed_password = pack("H*", $hashed_password);

        $old_sessionid = session_id();

        $user = $this->database->login($username, $hashed_password);

        if ($user != null) {
            session_regenerate_id(false);

            #$dt = new \DateTime("now");
            #$timestamp = $dt->getTimestamp();
            $timestamp = $this->datetime_convert_date_string_from_machine_timezone_into_utc_datetime_timestamp("now");
            $user->login_time = $timestamp;

            //echo ("Old session id is: " . session_id());
            #$this->session_start_user_session($user);
            //echo ("New session id is: " . session_id());
            $this->session_set_user($user);
            //echo ("Stored new session ID in cookie");
            //$this->session_set_cookie();
            $_SESSION["client_site_login"]["theme_user"]["keep_me_logged_in"] = $keepmeloggedin;


            $safe_user = new user(array());
            $safe_user->forename = $user->forename;
            $safe_user->surname = $user->surname;
            $safe_user->userid = $user->userid;
            $_SESSION["client_site"]["unencrypted_user"] = $safe_user;

            #$this->set_login_cookie();
            #$this->cookie_manager->save_login_data("shopsbee_login", "client_site_login");
        } else if ($user == null) {
            throw new \Exception("LOGIN_FAILURE");
            //throw new \Exception("Could not log you in, check username and password""");
        }

        $new_sessionid = session_id();

        $this->update_stock_hold_sessionids($old_sessionid, $new_sessionid);

        return ($user);
    }

    public function &database_login($username, $password) {

        #var_dump($password);
        #SHA512 is 128 Bytes or 64 Binary
        $hashed_password = hash("sha512", $password, false);
        #var_dump($hashed_password);
        #$hashed_password = pack("H*", $hashed_password);

        $user = $this->database->login($username, $hashed_password);

        #var_dump($username);

        return ($user);
    }

    /*
      public function set_login_cookie() {
      $auth_cookie_array = array();
      $expiry_time = 300;

      $user_string = $_SESSION["client_site_login"]["theme_user"]["user"];
      $user = \unserialize($user_string);
      $keepmeloggedin = $_SESSION["client_site_login"]["theme_user"]["keep_me_logged_in"];

      $auth_cookie_array["user"]["user"] = $user_string;
      $auth_cookie_array["user"]["keep_me_logged_in"] = $keepmeloggedin;
      $expiry = new \DateTime("now");


      $salt = "SHAREDSECRET :~P" . $user->userid;
      if ($keepmeloggedin == true) {
      $expiry->add(new \DateInterval('P1D'));
      $expiry_time = 86400;
      } else {
      $expiry->add(new \DateInterval('PT5M'));
      $expiry_time = 300;
      }

      $auth_cookie_array["user"]["expiry"] = $expiry;

      $payload = $user_string . $expiry->getTimestamp() . $salt;
      $calculated_hash = hash("sha512", $payload, false);
      $auth_cookie_array["user"]["hash"] = $calculated_hash;

      $this -> cookie_manager->set_cookie_data("shopsbee_login", $auth_cookie_array, $expiry_time, true);

      #$_SESSION["client_site"] = array_merge($_SESSION["client_site"], $auth_cookie_array);
      } */

    public function generate_page_buttons($total_pages, $current_page, $request_root, $prefix, $arg_count) {
        $firstpage = null;
        $currentpage = null;
        $middlepages = array();
        $lastpage = null;

        $array = null;

        if ($arg_count == 0) {
            $prefix = "?";
        } else if ($arg_count > 0) {
            $prefix = "&";
        }
        #$lastpage = 0;
        # (1) 2 3 4 5 6 7 8 9
        # 1    2 (3)   4 5 6  9
        # 1    2  3   (4)  5 6  7   9
        # 1    3  4  5   (6)  7  8   9
        // Draw first page number

        if ($total_pages > 0) {
            $number = 1;
            #$url = $request_root . "&pagenumber=1";
            $url = $request_root;
            $firstpage = new page_button($number, $url);
            $number = $current_page;
            $url = $request_root . $prefix . "p=$current_page";
            $currentpage = new page_button($number, $url);
        }

        $width = 3;
        if ($total_pages > 0) {
            $last = $total_pages;
        } else {
            $last = 1;
        }
        $size = ($width * 2) + 1;
        $startpage = 2;
        $first = 1;

        # If the current page is within 1 width of last page, 
        # Fix start page in position - 20, 21, 22 
        # For instance 1 (17 18 19 20 21 22) 23
        if ($current_page >= $last - ($width + 1)) {
            $startpage = $last - (2 * $width);
            $size--;
            # Fix the position at the start 
            # 1 (2 3 4 5 6 7) 8
        } else if ($current_page <= $first + ($width + 1)) {
            $startpage = 2;
            $size--;
        } else {
            ## Otherwise the start page is as below
            $startpage = $current_page - $width;
        }

        # Start middle section from at least page 2
        if ($startpage <= 2) {
            $startpage = 2;
        }

        $page = $startpage;
        for ($i = 0; $i < $size; $i++) {
            if ($page <= 1) {
                #echo $page;
                $page++;
                continue;
            }
            if ($page >= $last) {
                break;
            }

            $number = $page;
            $url = $request_root . $prefix . "p=$page";

            $middlepages[] = new page_button($number, $url);


            $page++;
        }

        // If first page isn't the last - draw last page number
        if ($last != 1) {

            $number = $last;
            $url = $request_root . $prefix . "p=$last";

            $lastpage = new page_button($number, $url);
        }

        $pages = new page_buttons($firstpage, $middlepages, $lastpage, $currentpage, $total_pages);

        //var_dump($pages);

        return $pages;
    }

    public function session_start_user_session(user $user) {
        #echo "start user session called in session store";
        session_regenerate_id(true);

        if (session_id() == "") {
            session_start();
        }
    }

    public function session_is_logged_in() {
        if (isset($_SESSION["client_site_login"]["theme_user"]) && isset($_SESSION["client_site_login"]["theme_user"]["user"]) && ($_SESSION["client_site_login"]["theme_user"]["user"] != null)) {
            return true;
            #var_dump("loggedIn");
        }
        return false;
    }

    public function session_logout() {
        //$sessionid = session_id();

        $this->delete_stock_holds();


        unset($_SESSION["client_site_login"]["theme_user"]);

        //Clear cart sensitive data
        unset($_SESSION["client_site"]["cart"]["billing_address"]);
        unset($_SESSION["client_site"]["cart"]["shipping_address"]);
        unset($_SESSION["client_site"]["payment_gateways"]["paypal"]);
        unset($_SESSION["client_site"]["cart"]["email"]);
        unset($_SESSION["client_site"]["unencrypted_user"]);
        #unset($_SESSION["client_site_login"]["cart"]["shipping_address"]);
        #$this -> cookie_manager ->set_cookie_data("shopsbee_login", "", -60000);
        #$this->cookie_manager->delete_login_cookie("shopsbee_login"); #set_cookie_data("shopsbee_login", "", -60000);
        session_destroy();
        /*
          // Unset all of the session variables.
          $_SESSION["client_site"] = array();

          // If it's desired to kill the session, also delete the session cookie.
          // Note: This will destroy the session, and not just the session data!
          if (ini_get("session.use_cookies")) {
          $params = session_get_cookie_params();
          setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]
          );
          }
          session_destroy(); */
    }

    public function mq_queue_mail($email) {
        $email->from_siteid = $this->site_id;
        $sent = false;
        $error_messages = array();
        #var_dump($this -> site_id);
        if ($this -> config_get_setting("plan_name") == "Trial")
        {
            $error_messages[] = "Can't send mail from trial accounts";
        } else {
            list($sent, $error_messages) = $this->my_mail_queue->put($email);
        }
        
        
        
        
        return array($sent, $error_messages);;
    }

    public function mq_send_mail($email) {
        $email->from_siteid = $this->site_id;
        
        $sent = false;
        $error_messages = array();
        #var_dump($this -> site_id);
        if ($this -> config_get_setting("plan_name") == "Trial")
        {
            $error_messages[] = "Can't send mail from trial accounts";
        } else {
            list($sent, $error_messages) = $this->my_mail_queue->send_mail($email);
        }
                                
        return array($sent, $error_messages);;        
    }

    public function get_site_name() {
        return $this->sitename;
    }

    public function session_get_userid() {
        $userid = null;
        $user = $this->session_get_user();
        if ($user != null) {
            $userid = $user->get_userid();
        }
        return $userid;
    }

    public function &database_get_users_addresses($userid) {
        $addresses_as_array = null;
        try {
            $addresses_as_array = $this->database->get_users_addresses($userid);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return ($addresses_as_array);
    }

    public function get_users_default_shipping_address($userid) {
        $addresses = $this->database_get_users_addresses($userid);
        $defaultaddress = null;
        foreach ($addresses as $address) {
            $defaultship = $address->defaultship;
            if ($defaultship == 1) {
                #echo "default is:" .  $address["addressid"];
                $defaultaddress = $address;
                # break;
            }
        };

        return $defaultaddress;
    }

    public function get_users_default_billing_address($userid) {
        $addresses = $this->database_get_users_addresses($userid);
        #var_dump($addresses);
        $defaultaddress = null;
        foreach ($addresses as $address) {
            #$defaultship = $address->defaultbill;
            #Note: Defaultbill is not used - only defaultship
            $defaultship = $address->defaultship;
            if ($defaultship == 1) {
                #echo "default is:" .  $address["addressid"];
                $defaultaddress = $address;
                # break;
            }
        };

        return $defaultaddress;
    }

    public function database_set_default_address($userid, $addressid) {
        $result = false;
        try {
            $result = $this->database->set_default_address($userid, $addressid);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $result;
    }

    public function database_create_address($userid, $address_array) {
        $result = null;
        $address = new address($address_array);
        try {
            $result = $this->database->create_address($userid, $address);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $result;
    }

    public function database_delete_address($userid, $addressid) {
        $result = false;
        try {
            $result = $this->database->delete_address($userid, $addressid);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $result;
    }

    public function database_update_address($userid, $addressid, $address_array) {
        $result = null;
        $address = new address($address_array);
        try {
            $result = $this->database->update_address($userid, $addressid, $address);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $result;
    }

    public function delete_address($userid, $addressid) {
        $cart = $this->cart;

        $result = false;
        try {
            $result = $this->database_delete_address($userid, $addressid);

            /* Check the address in the cart first */
            $shipping_address = $cart->get_shipping_address();
            if ($shipping_address != null) {
                $id = $shipping_address->addressid;
                if ($id == $addressid) {
                    $cart->unset_shipping_address();
                }
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $result;
    }

    public function get_address($userid, $addressid) {
        $address = null;

        if ($addressid == null) {
            return null;
        }
        ### get the addresses and display everything
        $addresses_as_array = null;
        try {
            $addresses_as_array = $this->database_get_users_addresses($userid);
        } catch (\Exception $e) {
            //There's no error messages created by this function
        }

        if ($addresses_as_array != null) {
            $address = $addresses_as_array[$addressid];
        }

        return $address;
    }

    public function cart_calculate() {
        return ($this->cart->calculate());
    }

    public function cart_get_subtotal() {
        return ($this->cart->subtotal);
    }

    public function cart_get_product_total_with_shipping() {
        return ($this->cart->product_total_with_shipping);
    }

    public function cart_get_shipping() {
        return ($this->cart->shipping);
    }

    public function cart_get_tax() {
        return ($this->cart->tax);
    }

    public function cart_get_product_total() {
        return ($this->cart->product_total);
    }

    public function cart_get_total_before_discounts() {
        return ($this->cart->total_before_discounts);
    }

    public function cart_add_coupon_to_cart($code) {
        $error_message = "";
        $success_message = "";
        $error = false;

        # Remove existing coupon from cart
        $this->cart->remove_coupon();


        # get coupon info from database, add to cart
        $coupon = $this->database->get_coupon($code);
        #$coupon = null;
        # calculate cart before apply.
        $this->cart_calculate();
        #$product_total_with_shipping = $this->cart_get_product_total_with_shipping();
        $cart = $this->cart;
        $product_total = $cart->product_total;

        if ($coupon != null) {
            $orderminimumspend = $coupon->orderminimumspend;
            $code = $coupon->code;

            //The coupon start and end are stored relative to UTC
            // BUT the user is in another time zone... 
            // So these will be a different offset from GMT
            // I.e. SERVER is Australia
            //      USER is London
            //      
            //      Required USER time is 2013-07-12 00:00:00
            //      Stored time  is 2013-07-12 09:00:00
            //      
            //      SERVER TIME OF EVENT is STORED TIME
            //      USER TIME is STORED TIME minus TIMEZONE OFFSET
            //      
            //      therefore stored $start is SERVER TIME
            //      stored $end is SERVER TIME
            //      now should be SERVER TIME
            $start = strtotime($coupon->start);
            $end = strtotime($coupon->end);
            #$now = strtotime("NOW");
            /*
              echo "START COUPON";
              echo $now . "<br/>";
              echo $start . "<br/>";
              echo $end . "<br/>";
              echo "END COUPON"; */

            $active_state = $coupon->get_active_state();

            //var_dump($active_state);

            if ($active_state == "COUPON_ACTIVE") {

                if ($product_total >= $orderminimumspend) {
                    $result = $this->cart->add_coupon($coupon);
                    if ($result == true) {
                        $success_message = "COUPON_VALID";
                    }
                } else {
                    $error = true;
                    #TODO: Translate error messages to generic code
                    $error_message = "COUPON_INSUFFICIENT_SPEND";
                    #$error_message = "coupon " . $code . " requires order total must be over $" . $orderminimumspend . " after coupon discounts";
                }
            }

            if ($active_state == "COUPON_PREACTIVE") {
                $error_message = "COUPON_STARTS_IN_FUTURE";
                $error = true;
            }

            if ($active_state == "COUPON_POSTACTIVE") {
                $error_message = "COUPON_EXPIRED";
                $error = true;
            }

            if ($active_state == "COUPON_DISABLED") {
                $error_message = "COUPON_DISABLED";
                $error = true;
            }
        } else {
            $error = true;
            $error_message = "COUPON_INVALID";
            #$error_message = "Invalid coupon code " . $code;
        }

        # If no error .. recalculate cart
        #if ($error == false) {
        #Calculate cart totals
        $this->cart_calculate();
        $product_total_with_shipping = $this->cart_get_product_total_with_shipping();
        #}
        #echo $error;

        return array($error, $coupon, $error_message, $success_message);
    }

    public function cart_remove_coupon_from_cart() {
        $retrn = $this->cart->remove_coupon();
        $this->cart_calculate();

        return array($retrn, "COUPON_REMOVED");
    }

    public function cart_get_coupon() {
        return ($this->cart->get_coupon());
    }

    public function cart_reset_coupons() {
        return ($this->cart->reset_coupons());
    }

    public function cart_get_total() {
        return ($this->cart->total);
    }

    public function cart_get_total_without_shipping() {
        return ($this->cart->get_cart_total_without_shipping());
    }

    public function cart_product_plus_one($productid) {
        return ($this->cart->product_plus_one($productid));
    }

    public function cart_product_minus_one($productid) {
        return ($this->cart->product_minus_one($productid));
    }

    public function paypal_set_express_checkout($total, $currency_code) {
        return ($this->paypal->set_express_checkout($total, $currency_code));
    }

    public function paypal_express_checkout_redirect() {
        return ($this->paypal->express_checkout_redirect());
    }

    public function paypal_get_express_checkout_details() {
        return ($this->paypal->get_express_checkout_details());
    }

    public function paypal_do_express_checkout_itemised($orders) {
        return ($this->paypal->do_express_checkout_itemised($orders));
    }

    public function paypal_set_express_checkout_itemised($total, $subtotal, $products) {
        return ($this->paypal->set_express_checkout_itemised($total, $products));
    }

    public function paypal_do_express_checkout_payment($order_id, $total, $currency_code) {
        return ($this->paypal->do_express_checkout_payment($order_id, $total, $currency_code));
    }

    public function paypal_get_customer_details() {
        return ($this->paypal->get_customer_details());
    }

    public function save_session_to_cookie() {
        #$this->cookie_manager->save_session_to_cookie();
    }

    public function cart_get_cart_count() {
        return ($this->cart->get_count());
    }

    public function cart_set_payment_method($string) {
        return ($this->cart->set_payment_method($string));
    }

    public function cart_get_payment_method() {
        return ($this->cart->get_payment_method());
    }

    public function cart_set_page($page) {
        $_SESSION["client_site"]["cart"]["page"] = $page;
    }

    public function cart_get_page() {
        $s = 0;
        if (isset($_SESSION["client_site"]["cart"]["page"])) {
            $s = $_SESSION["client_site"]["cart"]["page"];
        }

        return $s;
    }

    public function redirect_after_postback($url, $redirect_time) {
        #$this->cookie_manager->save_session_to_cookie();
        #header("HTTP/1.1 307 Temporary Redirect", true, 307);
        #header("HTTP/1.0 302 Found", true, 302);
        #header("Content-Length: 0", true); 
        #var_dump(strlen("Location: " . $url));
        #die();
        header("HTTP/1.1 303 See Other");
        header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        #header("HTTP/1.0 302 Moved Temporarily");
        #$string = "Location: " . $url;
        #header($string, true, 303);
        //
                    //
        header("Location: " . $url);
        exit();

        #header("Location: " . $url, true, 302);
#header("Status: 302"); 
#header('Location: $url", true, 302); 
#header("Content-Length: 0", true); 
#header("Connection: close", true); 
#echo str_repeat("\r\n", 128); // for IE 
    }

    # Bugs in firefox/chrome mean we have problems with redirects, IE9 works perfectly.
    # IMPORTANT NOTES - 303 on a postback then a redirect does not work with firefox correctly 
    #                        - even with randomised URL and no-cache settings - the cached wrong page is loaded
    #                 - 307 does not work with firefox at all - constant prompts about posting data
    #                 - 302 Moved temporarily with randomised URL works for postback then redirect

    public function redirect_nopostback($url, $redirect_time) {
        #$this->cookie_manager->save_session_to_cookie();
        #header("HTTP/1.1 307 Temporary Redirect", true, 307);
        #header("HTTP/1.0 302 Found", true, 302);
        #header("Content-Length: 0", true); 
        #var_dump(strlen("Location: " . $url));
        #die();
        #header("HTTP/1.1 303 See Other");
        header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header("HTTP/1.0 302 Moved Temporarily");
        #$string = "Location: " . $url;
        #header($string, true, 303);
        //
                    //
        header("Location: " . $url);
        exit();

        #header("Location: " . $url, true, 302);
#header("Status: 302"); 
#header('Location: $url", true, 302); 
#header("Content-Length: 0", true); 
#header("Connection: close", true); 
#echo str_repeat("\r\n", 128); // for IE 
    }

    public function redirect_refresh($url, $redirect_time) {
        #$this->cookie_manager->save_session_to_cookie();
        header("Cache-Control: no-cache, no-store");
        header("Pragma: no-cache");
        header('refresh:' . $redirect_time . ';url=' . $url);
        exit();
    }

    public function session_set_login_redirect_page($page) {
        $_SESSION["client_site"]["login_redirectpage"] = $page;
    }

    public function session_get_login_redirect_page() {
        $page = null;
        if (isset($_SESSION["client_site"]["login_redirectpage"])) {
            $page = $_SESSION["client_site"]["login_redirectpage"];
        }
        return $page;
    }

    #TODO: use memcached / get_all_coupons then find the coupon is much more efficient

    public function get_coupon_by_id($couponid) {

        $coupons = $this->get_all_coupons();

        $foundcoupon = null;
        foreach ($coupons as $coupon) {
            #var_dump($coupon -> couponid);
            #var_dump($couponid);
            if ($coupon->couponid == $couponid) {
                #echo "found";
                $foundcoupon = $coupon;
            }
        }

        #$coupon = $this->database->get_coupon($code);

        return $foundcoupon;
    }

    #TODO: use memcached / get_all_currencies then find the currency is much more efficient

    public function get_currency_by_id($currencyid) {

        list($currencies, $currency_count) = $this->get_all_currencies();

        $foundcurrency = null;
        foreach ($currencies as $currency) {
            #var_dump($currency -> currencyid);
            #var_dump($currencyid);
            if ($currency->currencyid == $currencyid) {
                #echo "found";
                $foundcurrency = $currency;
            }
        }

        #$currency = $this->database->get_currency($code);

        return $foundcurrency;
    }

    #TODO: use memcached / get_all_coupons then find the coupon is much more efficient

    public function get_coupon_by_code($code) {

        $coupons = $this->get_all_coupons();

        $foundcoupon = null;
        foreach ($coupons as $coupon) {
            #var_dump($coupon -> couponid);
            #var_dump($couponid);
            #if ($coupon -> code == $code)
            if (strnatcasecmp($coupon->code, $code) === 0) {
                #echo "found";
                $foundcoupon = $coupon;
            }
        }

        #$coupon = $this->database->get_coupon($code);

        return $foundcoupon;
    }

    #TODO: use memcached / get_all_currencies then find the currency is much more efficient

    public function get_currency_by_code($code) {

        list($currencies, $currency_count) = $this->get_all_currencies();

        $foundcurrency = null;
        foreach ($currencies as $currency) {
            #var_dump($currency -> currencyid);
            #var_dump($currencyid);
            #if ($currency -> code == $code)
            #var_dump($currency -> code);
            #var_dump($code);
            if (strnatcasecmp($currency->code, $code) === 0) {
                #echo "found";
                $foundcurrency = $currency;
            }
        }

        #$currency = $this->database->get_currency($code);

        return $foundcurrency;
    }

    #TODO: use memcached / get_all_currencies then find the currency is much more efficient

    public function get_default_currency() {

        list($currencies, $currency_count) = $this->get_all_currencies();

        $foundcurrency = null;
        foreach ($currencies as $currency) {
            #var_dump($currency -> currencyid);
            #var_dump($currencyid);
            #if ($currency -> code == $code)
            #var_dump($currency -> code);
            #var_dump($code);
            if ($currency->is_primary == true) {
                #echo "found";
                $foundcurrency = $currency;
            }
        }

        #$currency = $this->database->get_currency($code);

        return $foundcurrency;
    }

    public function get_cart_currency() {
        return $this->cart->currency;
    }

    public function create_order_object() {
        $cart = $this->cart;

        $error_messages = array();

        $note = "";
        $email = "";

        # Essential products for an order
        $billing_address = new \client_code\address(array());
        $shipping_address = new \client_code\address(array());
        $payer_note = null;
        $biller_email = null;
        $coupon = null;
        $payment_method = null;
        $total = 0;
        $shipping = 0;
        $tax = 0;
        $tax_rate = 0;


        #Parcel contains products, products contain specials
        #Parcels is the array of parcels
        $parcels = $cart->get_parcels();
        #$parcels = $cart -> get_parcels();
        $coupon = $cart->get_coupon();

        #var_dump($coupon);
        $total = $cart->total;
        $shipping = $cart->shipping;
        $tax = $cart->tax;
        $product_total = $cart->product_total;
        $subtotal = $cart->subtotal;

        # Create an order object and initialise
        $order = new order(array());
        #$order->create_order(
        #       $parcels, $billing_address, $shipping_address, $note, $email, $coupon, $payment_method, $shipping, $tax, $total);

        $order->parcels = $parcels;
        #JL products edit
        #$order->products = $cart->products;

        $order->billing_address = $billing_address;

        $order->coupon = $coupon;

        $order->shipping_address = $shipping_address;

        $order->note = $note;

        $order->email = $email;

        $order->payment_method = $payment_method;
        $order->total = $total;
        $order->shipping = $shipping;
        $order->tax = $tax;
        $order->subtotal = $subtotal;
        $order->product_total = $product_total;

        $order->taxes = $cart->taxes;
        $order->shipping_taxes = $cart->shipping_taxes;

        $order->coupon_applied_value = $cart->coupon_applied_value;
        if ($coupon != null) {
            $order->couponcode = $coupon->code;
        }

        $currency = $cart->currency;
        $currencycode = $currency->code;
        $order->currency = $currencycode;


        #var_dump($order);
        //$userid = $this->session_get_userid();
        $cart->order = $order;

        return array($order, $error_messages);
    }

    public function update_order_object_with_payment_address_details($order) {
        $paypal = $this->paypal;
        $cart = $this->cart;
        $payment_method = $cart->get_payment_method();
        #$order = $cart -> order;
        $error_messages = array();

        $retrieved = false;
        $billing_address = null;
        $shipping_address = null;

        # if the cart payment method is paypal express then use the
        # shipping information from paypal / paypal object
        # Method used when user is EITHER LOGGED IN OR NOT LOGGED IN
        if ($payment_method == "PAYPAL_EXPRESS") {
            list($paypal_checkout_details, $retrieved, $paypal_customer_details_error_messages) = $paypal->get_customer_details();

            $error_messages = array_merge($error_messages, $paypal_customer_details_error_messages);

            ## Assign relevant variables.
            ## Paypal doesn't assign the billers address.
            $billing_address = new address(null);
            $billing_address->forename = $paypal_checkout_details->biller_firstname;
            $billing_address->surname = $paypal_checkout_details->biller_lastname;
            $billing_address->title = $paypal_checkout_details->biller_title;

            $shipping_address = new address(null);
            $shipping_address->forename = $paypal_checkout_details->shipto_name;
            $shipping_address->addressline1 = $paypal_checkout_details->shipto_street;
            $shipping_address->addressline2 = $paypal_checkout_details->shipto_street2;
            $shipping_address->suburb = $paypal_checkout_details->shipto_city;
            $shipping_address->state = $paypal_checkout_details->shipto_state;
            $shipping_address->postcode = $paypal_checkout_details->shipto_zip;
            $shipping_address->country = $paypal_checkout_details->shipto_countryname;
            $shipping_address->daytimephonenumber = $paypal_checkout_details->shipto_phonenum;

            $email = $paypal_checkout_details->biller_email;
            $note = $paypal_checkout_details->payer_note;
        } elseif ($payment_method == "PAYPAL_CART_METHOD") {

            list($paypal_checkout_details, $retrieved, $paypal_customer_details_error_messages) = $paypal->get_customer_details();

            $error_messages = array_merge($error_messages, $paypal_customer_details_error_messages);

            #var_dump($paypal_checkout_details);
            #$billing_address = new address(null);
            #$billing_address->set_forename($paypal_checkout_details->get_biller_firstname());
            #$billing_address->set_surname($paypal_checkout_details->get_biller_lastname());
            #$billing_address->set_title($paypal_checkout_details->get_biller_title());
            #$email = $paypal_checkout_details->get_biller_email();

            $shipping_address = $cart->get_shipping_address();
            $billing_address = $cart->get_billing_address();
            $email = $cart->get_email();



            $note = $paypal_checkout_details->payer_note;
            $note .= "\n";
            $note .= $cart->get_note();
        } elseif ($payment_method == "BANK_DEPOSIT") {

            $retrieved = true;
            $billing_address = $cart->get_billing_address();
            $shipping_address = $cart->get_shipping_address();

            #TODO: Should set to users email address
            $email = $cart->get_email();

            #No payer note allowed under standard checkout.
            $note = $cart->get_note();
        }

        #$cart -> order = $order;

        if ($retrieved == false) {
            $error_messages["create_order_object"] = "CHECKOUT_PAYPAL_GET_CUSTOMER_DETAILS_FAILURE";
            return array(null, $error_messages);
        }

        if (($billing_address == null) && ($shipping_address == null)) {
            //Billing address is null, rectify, transaction cancelled
            $error_messages["create_order_object"] = "CHECKOUT_NO_BILLING_OR_SHIPPING_DETAILS";
            return array(null, $error_messages);
        }

        if ($billing_address == null) {
            //Billing address is null, rectify, transaction cancelled
            $error_messages["create_order_object"] = "CHECKOUT_ORDERCONFIRM_NO_BILLING_ADDRESS";
            return array(null, $error_messages);
        }

        if ($shipping_address == null) {
            $error_messages["create_order_object"] = "CHECKOUT_ORDERCONFIRM_NO_SHIPPING_ADDRESS";
            #$error_message = "Shipping address is null, rectify, transaction cancelled";
            return array(null, $error_message);
        }

        return array($order, $error_messages);
    }

    public function database_create_order($userid, $order) {
        $database = $this->database;

        list($orderid, $start) = $database->create_order($userid, $order);

        return array($orderid, $start);
    }

    public function database_update_order_address($order) {
        $database = $this->database;

        $result = $database->update_order_address($order);

        return $result;
    }

    public function &database_change_password($username, $password) {
        #SHA512 is 128 Bytes or 64 Binary
        $hashed_password = hash("sha512", $password, false);
        $password_version_hash = 0;
        #$hashed_password = pack("H*", $hashed_password);
        $result = $this->database->change_password($username, $hashed_password, $password_version_hash);

        return ($result);
    }

    public function database_user_change_password($userid, $old_password, $new_password) {

        #SHA512 is 128 Bytes or 64 Binary
        $hashed_old_password = hash("sha512", $old_password, false);
        #$hashed_old_password = pack("H*", $hashed_old_password);
        #SHA512 is 128 Bytes or 64 Binary
        $hashed_password = hash("sha512", $new_password, false);

        $password_version_hash = '0';
        #$hashed_password = pack("H*", $hashed_password);
        list($result, $error_message) = $this->database->user_change_password($userid, $hashed_old_password, $hashed_password, $password_version_hash);

        return (array($result, $error_message));
    }

    public function &database_create_subscription($username, $userid, $subscription, $unsubscribehash) {
        $result = $this->database->create_subscription($username, $userid, $subscription, $unsubscribehash);
        return ($result);
    }

    public function &database_deactivate_subscription($username, $userid, $subscription) {
        $result = $this->database->deactivate_subscription($username, $userid, $subscription);
        return ($result);
    }

    public function &database_deactivate_subscriptions($username, $userid) {
        $result = $this->database->deactivate_subscriptions($username, $userid);
        return ($result);
    }

    public function &database_get_subscriptions($userid, $username) {

        $result = $this->database->get_subscriptions($userid, $username);
        return ($result);
    }

    // Counts the occurances of the needles - each needle counts once
    public function needles_in_haystack($haystack, $needles) {
        $count = 0;
        $pos = false;

        foreach ($needles as $needle) {
            # Case insensitive match
            $pos = stripos($haystack, $needle);
            #$pos = mb_stripos($haystack, $needle);
            // Note the special use of !== to do a boolean match as
            // != will match integer position 0!
            if ($pos !== false) {
                $count++;
            }
        }

        return $count;
    }

    # The sort types are price, name, popularity, dateadded
    # The sort order is asc, desc

    public function dosort($products_array, $sort_type, $sort_order) {
        $model = $this;

        # Don't sort if there's no sort type!
        if ($sort_type == null) {
            return $products_array;
        }

        # Now sort the pages
        if ($sort_type == "date") {
            foreach ($products_array as $key => $product) {
                $utctimezone = new \DateTimeZone("UTC");
                $start_date = new \DateTime($product->dateadded);
                $start_date->setTimezone($utctimezone);
                $date_added_as_timestamp = $start_date->getTimestamp();


                $product->set_comparator($date_added_as_timestamp);
            }

            $quick_sort = new \client_code\quick_sort();
            $products_array = $quick_sort->sort($products_array);
        }


        # Now sort the pages
        if ($sort_type == "popular") {
            # THis requires products are keyed by prouctid
            list($orders, $order_count) = $model->get_all_orders();

            $new_products_array = array();
            # Re-key array
            foreach ($products_array as $product) {
                $new_products_array[$product->productid] = $product;
            }

            #$all_products = clone($all_products);


            foreach ($orders as $order) {
                //echo "order";
                //echo "abc";
                $parcels = $order->parcels;
                //var_dump($parcels);
                foreach ($parcels as $parcel) {
                    //echo "parcel";
                    $products = $parcel->products;
                    foreach ($products as $product) {
                        $productid = $product->productid;

                        #$product = $this -> get_product_in_array($productid,$products_array);
                        # If product gets deleted then we have a problem!
                        if (isset($new_products_array[$productid])) {
                            //echo "abc";

                            $orderedqty = $product->orderedqty;

                            $new_products_array[$productid]->total_ordered += $orderedqty;
                            #echo $productid;
                            #echo $orderedqty;
                            #echo "<br/>";
                            #$all_products[$productid]->set_comparator($all_products[$productid]->get_comparator() + $orderedqty);
                            $new_products_array[$productid]->set_comparator($orderedqty);
                        } else {
                            #echo "no" .$productid . "<br/>";
                        }
                    }
                }
            }
            foreach ($products_array as $key => $product) {
                #echo $product -> get_comparator();
            }
            #$product->set_comparator($product->purchaseprice);
            #
            $products_array = array_values($new_products_array);

            #Now sort from0 - 


            $quick_sort = new \client_code\quick_sort();
            $products_array = $quick_sort->sort($products_array);
        }

        # Now sort the pages
        if ($sort_type == "price") {
            foreach ($products_array as $key => $product) {
                $product->set_comparator($product->purchaseprice);
            }

            $quick_sort = new \client_code\quick_sort();
            $products_array = $quick_sort->sort($products_array);
        }

        # Now sort the pages
        if ($sort_type == "name") {
            foreach ($products_array as $key => $product) {
                $product->set_comparator($product->name);
            }

            $client_locale = $this->get_client_locale();
            #echo $client_locale;
            if (strpos($client_locale, "en") !== false) {
                $quick_sort_string = new quick_sort_string();
                $products_array = $quick_sort_string->sort($products_array);
            } else {
                $quick_sort_i18n_string = new quick_sort_i18n_string();
                $products_array = $quick_sort_i18n_string->sort($products_array);
            }
        }

        if ($sort_type != null && $sort_order == "desc") {
            
        } else if (($sort_type != null) && ($sort_order == "asc")) {
            $products_array = array_reverse($products_array, false);
        }
        return $products_array;
    }

    /*
      public function get_product_in_array($productid,$product_array)
      {
      foreach($product_array as $product)
      {
      if ($productid == )
      }
      } */

    public function memcached_database_product_search_paged($searchfor, $search_type, $sort_type, $sort_order, $brand, $tag, $current_page, $page_size) {
        list($products_array, $products_count, $products_count_partial, $products_count_complete) = $this->memcached_database_product_search($searchfor, $search_type, $brand, $tag);

        if ($sort_type != null) {
            $products_array = $this->dosort($products_array, $sort_type, $sort_order);
        }

        $page_count = $this->get_page_count($products_count, $page_size);

        $page_products_array = $this->get_page_of_stuff($products_array, $products_count, $current_page, $page_size);

        return array($page_products_array, $products_count, $products_count_partial, $products_count_complete, $page_count);
    }

    public function memcached_database_product_search($searchfor, $search_type, $brand, $tag) {
        $products = array();
        $products_count = 0;

        /* Do not search the string if it only contains the delimiter 
         * CHR(32) space */
        # Do not search when string is empty
        if ((is_bool($searchfor)) && ($searchfor == null)) {
            $products_count = count($products);
            return array($products, $products_count, 0, $products_count);
        }

        # Do not search when string contains spaces only.
        if (preg_match("/^[\s]+$/", $searchfor)) {
            return array($products, $products_count, 0, $products_count);
        }

        # Sort the search terms to help memcache
        # Explode the strings
        $array = explode(" ", $searchfor);

        # Sort array by VALUE (edit: LOL what does that mean.) 
        # I think we're just rearranging the search terms in english ASCII sort order 
        # To always have them in a particular order to avoid redundant searches being stored.
        # Because word order has no relevance in our searches at present.
        # the new search is the same as search the new

        asort($array);
        # Recombine
        $searchfor = implode(" ", $array);

        list($sorted_products, $products_count, $products_count_partial, $products_count_complete) = $this->database_product_search($searchfor, $search_type, $brand, $tag);

        $products_and_count["products"] = $sorted_products;
        $products_and_count["count"] = $products_count;


        return array($sorted_products, $products_count, $products_count_partial, $products_count_complete);
    }

    public function database_product_search($searchfor, $search_type, $brand, $tag) {
        
        $products_found = array();
        $sorted_products = array();
        $products_count = 0;
        $products_count_partial = 0;
        $products_count_complete = 0;

        if ($searchfor == null) {
            return array(array(), 0, 0, 0);
        }

        $options_array = array("include_provisioning_products" => false, "include_provisioning_variants" => false, "remove_out_of_stock_products" => true);
        list($products_as_array, $products_count) = $this->get_all_products_and_associated($options_array);

        # DONT USE THIS COUNT AS IT IS INCORRECT
        #$products_count = count($products_as_array);
        #echo $products_count;
        # Note: it's easier to filter on search term, 
        # Filter on brand, tag etc 
        # then count the matches
        # even though it is less optimal 
        # rather than the old approach of filtering by search term and counting at same time

        $found = false;
        if (!empty($products_as_array)) {
            // Divide the search string into an array of words 
            $words = explode(" ", $searchfor);

            //var_dump($products_as_array);
            // Check if each word exists in 
            foreach ($products_as_array as $product) {
                //var_dump($product -> name);
                $productname = $product->name;
                
                if ($search_type == "complete")
                {
                    $productname .= " " . $product -> description;
                    $productname .= " " . $product -> brand;
                    
                    foreach($product -> tags as $tag)
                    {
                       $productname += " " . $tag;
                    } 
                    
                    foreach($product -> variants as $variant)
                    {
                        $productname .= " " . $variant -> name;
                        $productname .= " " . $variant -> price;
                        /*
                        if ($variant -> special != null)
                        {
                            
                        } */
                        
                    }
                }
                
                echo $productname;

                $count = $this->needles_in_haystack($productname, $words);
                // Note the special use of !== to do a boolean match as
                // != will match integer position 0!
                if ($count > 0) {
                    $found = true;
                    #Uncomment this when debugging the search
                    #echo $count;
                    $product->set_comparator($count);
                    $products_found[] = $product;
                }
            }
        }

        if ($brand != null) {
            $products_found = $this->do_brand_filter($products_found, $brand);
            $products_count = count($products_found);
        }

        #filter by tag
        if ($tag != null) {
            $products_found = $this->do_tag_filter($products_found, $tag);
            #Adjust product count
            #  $products_count = count($products_as_array);
        }

        // Divide the search string into words 
        $words = explode(" ", $searchfor);

        $word_count = count($words);

        /* Count the matches */
        foreach ($products_found as $product) {
            $productname = $product->name;

            $count = $this->needles_in_haystack($productname, $words);
            // Note the special use of !== to do a boolean match as
            // != will match integer position 0!
            if ($count > 0) {
                $found = true;
                #Uncomment this when debugging the search
                #echo $count;
                # 
                # exact match
                if ($word_count == $count) {
                    $products_count_complete++;
                    #partial match
                } else {
                    $products_count_partial++;
                }
            }
        }

        /* Don't sort if there are no products! */
        if ($found == true) {
            $quick_sort = new \client_code\quick_sort();
            $sorted_products = $quick_sort->sort($products_found);
        }

        $products_count = count($sorted_products);


        return array($sorted_products, $products_count, $products_count_partial, $products_count_complete);
    }

    public function session_get_search_page() {
        $url = null;
        if (isset($_SESSION["client_site"]["search_url"])) {
            $url = $_SESSION["client_site"]["search_url"];
        }
        return $url;
    }

    public function cart_update_qty($productid, $variantid, $qty) {
        #echo $productid;
        list($result, $error_messages) = $this->cart->update_qty($productid, $variantid, $qty);

        # We unset the shipping methods when the cart quantities change as the shipping price is no longer valid
        # TODO: However in future we may want to use the same shipping profile and recalculate using that, 
        # Code change is required to the shipping calculator code... we'd have to use the shipping rule ID as a reference to recalculate on
        # See notes in get_shipping_method function in cart.
        $this->cart->unset_shipping_method();



        return array($result, $error_messages);
    }

    public function english_number_format($number) {
        // english notation without thousands seperator
        $english_number_format = number_format((float) $number, 2, '.', '');
        // 1234.57
        return $english_number_format;
    }

    public function get_cart() {
        return $this->cart;
    }

    public function build_quicknav_product($productid) {
        $include_provisioning_variants = false;
        $options_array = array("include_provisioning_variants" => $include_provisioning_variants);
        $product = $this->get_product_and_associated($productid, $options_array);
        $categoryid = $product->categoryid;
        $quicknav = $this->build_quicknav($categoryid);

        # Append the product to the category list
        $categories = $quicknav->categories;


        $categories[] = $product;

        $quicknav->categories = $categories;

        $count = count($categories);
        $quicknav->count = $count;

        return $quicknav;
    }

    public function build_quicknav($active_categoryid) {
        /* Gets the category and its children in a tree */
        $category_list = $this->get_category_and_parents_to_root($active_categoryid);

        $count = count($category_list);

        $quicknav = new quicknav($category_list, $count);
        return $quicknav;
    }

    # unused

    public function &get_collection($collectionid) {
        list($collections, $collection_count) = $this->get_all_collections();

        $foundcategory = null;
        if (isset($collections[$collectionid])) {
            $foundcategory = $collections[$collectionid];
        }

        return ($foundcategory);
    }

    public function get_all_collections() {
        $memcached_store = $this->memcached_store;


        $searchkeyvalues = array("allcollections" => "allcollections");

        $found = false;
        try {
            $collections = $this->memcached_store->get_search($searchkeyvalues);

            $found = true;
        } catch (\Exception $e) {
            $found = false;
        }

        if ($found == false) {


            $collections = $this->database->get_all_collections_and_collectionproducts();           

            $protocol = $this->get_protocol();
            $real_cdn = "";
            $developer_disable_cdn = $this -> config_get_setting("developer_disable_cdn");
            if ($developer_disable_cdn == true)
            {
               
            } else {
                $real_cdn = $this->config_get_setting("aws_cdn_location");
            }
            $site_id = $this->config_get_setting("site_id");

            $collection_images = $this->database->get_all_collection_images($protocol, $real_cdn, $site_id);

            $collections = $this->attach_images_to_collections($collections, $collection_images);


            $options_array = array("include_provisioning_products" => false, "include_provisioning_variants" => false, "remove_out_of_stock_products" => true);
            list($products, $products_count) = $this->get_all_products_and_associated($options_array);


            # For each collection product, set the product in the collection
            foreach ($collections as $collection) {
                $collectionproducts = $collection->collectionproducts;
                $products_in_collection = $collection->products;
                foreach ($collectionproducts as $collectionproduct) {
                    $collectionproduct_productid = $collectionproduct->productid;

                    if (isset($products[$collectionproduct_productid])) {
                        $products_in_collection[$collectionproduct_productid] = $products[$collectionproduct_productid];
                    }
                }
                $collection->set_products($products_in_collection);
            }

            try {
                list($stored, $memcached_error_messages) = list($stored, $memcached_error_messages) = $memcached_store->store_search($searchkeyvalues, $collections, $this->database_expiry);
            } catch (\Exception $e) {
                #echo $e->getMessage();
                #   echo "Unable to store";
            }
        }

        $count = count($collections);
        return array($collections, $count);
    }

    public function get_all_collections_keyed_by_name() {
        # Keyed on ID
        list($collections, $collection_count) = $this->get_all_collections();

        $new_collections = array();
        foreach ($collections as $collection) {
            $name = $collection->name;
            $new_collections[$name] = $collection;
        }

        return $new_collections;
    }

    public function get_all_products_in_collection_paged($collectionid, $sort_type, $sort_order, $current_page, $page_size) {

        list($collections, $collection_count) = $this->get_all_collections();

        $products_array = array();
        if (isset($collections[$collectionid])) {
            $collection = $collections[$collectionid];
            $products_array = $collection->products;
        }

        # Reindex the array to fill in blank holes! so 0,1,2,3 instead of empty values
        $products_array = array_values($products_array);

        $products_count = count($products_array);


        #var_dump($products_count);
        #var_dump($sort_type);
        #sort here
        if ($sort_type != null) {
            $products_array = $this->dosort($products_array, $sort_type, $sort_order);
        }

        $page_count = $this->get_page_count($products_count, $page_size);
        #$page_products_array = $products_array;
        #var_dump($page_count);
        #var_dump($products_count);
        #var_dump($current_page);
        #var_dump($page_size);

        $page_products_array = $this->get_page_of_stuff($products_array, $products_count, $current_page, $page_size);
        #var_dump($page_products_array);

        return array($page_products_array, $products_count, $page_count);

        /*
          $products = array();
          if (isset($collections[$collectionid])) {
          $collection = $collections[$collectionid];
          $products = $collection->products;
          }

          $product_count = count($products);
          $page_count = 1;
          return array($products, $product_count, $page_count); */
    }

    public function get_news_paged($current_page, $page_size, $sort_type, $sort_order) {

        list($all_news, $count) = $this->get_all_news();

        #var_dump($all_news);

        $page_count = $this->get_page_count($count, $page_size);

        # reindex array to ensure array keys start from 0 before paginating (as DB keys start from 1)
        $all_news = array_values($all_news);

        $page_news_array = $this->get_page_of_stuff($all_news, $count, $current_page, $page_size);

        #var_dump($page_news_array);

        return array($page_news_array, $count, $page_count);
    }

    public function get_news($newsid) {
        list($news, $news_count) = $this->get_all_news();

        $foundnews = null;
        foreach ($news as $news_item) {
            if ($news_item->newsid == $newsid) {
                $foundnews = $news_item;
            }
        }

        #var_dump ($news);
        //var_dump($newsid);
        /*
          $foundnews = null;
          if (isset($news[$newsid])) {
          $foundnews = $news[$newsid];
          #var_dump($foundnews);
          } */
        return ($foundnews);
    }

    public function get_shop() {
        return $this->shop;
    }

    public function get_tax($taxid) {
        $found_tax = null;
        $all_taxes = $this->get_all_taxes();

        foreach ($all_taxes as $tax) {
            if ($tax->taxid == $taxid) {
                $found_tax = $tax;
                break;
            }
        }
        return $found_tax;
    }

    public function get_all_taxes() {

        $all_tax = array();
        $memcached_store = $this->memcached_store;


        $searchkeyvalues = array("tax" => "tax");

        $found = false;
        try {
            $all_tax = $this->memcached_store->get_search($searchkeyvalues);

            $found = true;
        } catch (\Exception $e) {
            $found = false;
        }

        if ($found == false) {

            $all_tax = $this->database->get_tax();
            $all_tax_rules = $this->database->get_tax_rules();

            #attach subrules to rules
            foreach ($all_tax_rules as $tax_rule) {
                $ruleid = $tax_rule->ruleid;

                $taxid = $tax_rule->taxid;
                if (isset($all_tax[$taxid])) {
                    $existingrule = $all_tax[$taxid];

                    # Get the subrules (if any)
                    $subrules = $existingrule->rules;

                    #Add the rule into the rules array
                    $subrules[$ruleid] = $tax_rule;

                    # Add the subrule into the rule.
                    $existingrule->rules = $subrules;

                    $all_tax[$taxid] = $existingrule;
                }
            }

            try {
                list($stored, $memcached_error_messages) = $memcached_store->store_search($searchkeyvalues, $all_tax, $this->database_expiry);
            } catch (\Exception $e) {
                #echo $e->getMessage();
                #   echo "Unable to store";
            }
        }


        return $all_tax;
    }

    public function get_all_shipping_taxes() {

        $all_tax = array();
        $memcached_store = $this->memcached_store;


        $searchkeyvalues = array("shippingtax" => "shippingtax");

        $found = false;
        try {
            $all_tax = $this->memcached_store->get_search($searchkeyvalues);

            $found = true;
        } catch (\Exception $e) {
            $found = false;
        }

        if ($found == false) {

            $all_tax = $this->database->get_shipping_tax();
            $all_tax_rules = $this->database->get_tax_rules();

            #attach subrules to rules
            foreach ($all_tax_rules as $tax_rule) {
                $ruleid = $tax_rule->ruleid;

                $taxid = $tax_rule->taxid;
                if (isset($all_tax[$taxid])) {
                    $existingrule = $all_tax[$taxid];

                    # Get the subrules (if any)
                    $subrules = $existingrule->rules;

                    #Add the rule into the rules array
                    $subrules[$ruleid] = $tax_rule;

                    # Add the subrule into the rule.
                    $existingrule->rules = $subrules;

                    $all_tax[$taxid] = $existingrule;
                }
            }

            try {
                list($stored, $memcached_error_messages) = $memcached_store->store_search($searchkeyvalues, $all_tax, $this->database_expiry);
            } catch (\Exception $e) {
                #echo $e->getMessage();
                #   echo "Unable to store";
            }
        }


        return $all_tax;
    }

    public function build_navigation($active_categoryid) {
        $model = $this;
        list($categories, $categories_count) = $model->get_all_categories();

        ### Controller components
        $root_category = $model->get_root_category($categories);

        ### get the categories parents ### 
        ### Until we reach the root ###
        // Get the categoryid into a local variable

        /* Gets the category and its children in a tree */
        $category_list = $model->get_category_and_parents_to_root($active_categoryid);

        $root_category = $model->get_category_children_of_parent_to_leaf($root_category);

        $keys = $this->get_keys($category_list);

        $stored_categories = array();

        $root_children = null;
        if ($root_category != null) {

            # If categoryid is 0, root is active category 
            if ($active_categoryid == -1) {
                $root_category->active = true;
            }

            $stored_categories = $this->store_category($stored_categories, $root_category, 0);

            $navigation_root_categoryid = $root_category->id;

            $root_children = $root_category->get_children();

            foreach ($root_children as $root_child) {
                $stored_categories = $this->iterative_store_children($active_categoryid, $stored_categories, $root_child, 1, 5, $keys);
            }
        }

        return $stored_categories;
    }

    function store_category($stored_categories, $category, $depth) {
        $category->set_depth = $depth;
        $stored_categories[] = $category;

        return $stored_categories;
    }

    #$category is the current category node visited

    function iterative_store_children($active_categoryid, $stored_categories, $category, $depth, $depthmax, $keys) {

        global $model;

        if ($category == null) {
            return $stored_categories;
        }

        if ($depth >= $depthmax) {
            return $stored_categories;
        }

        if ($active_categoryid == $category->id) {
            $category->active = true;
        }

        $stored_categories = $this->store_category($stored_categories, $category, $depth);


        $categoryid = $category->id;
        $category->depth = $depth;

        $category_children = $category->get_children();

        list ($products_array, $products_count) = $this->get_products_in_category_and_children($categoryid);

        $category->count = $products_count;

        //list ($products_array, $products_count) = $this->get_products_in_category_and_children($categoryid);

        $depth++;
        foreach ($category_children as $category) {

            // Do we want to print the children?
            $print_childs_children = true;
            if (($depth > 1) && (!in_array($categoryid, $keys))) {
                $print_childs_children = false;
            }


            if ($print_childs_children == true) {
                $stored_categories = $this->iterative_store_children($active_categoryid, $stored_categories, $category, $depth, $depthmax, $keys);
            }
        }

        return $stored_categories;
    }

    function get_keys($category_list) {
        $keys = array();
        if ($category_list != null) {
            foreach ($category_list as $category) {
                $keys[] = $category->id;
            }
        }

        return $keys;
    }

    public function get_all_shipping() {
        $shipping_providers = array();
        $memcached_store = $this->memcached_store;


        $searchkeyvalues = array("shipping" => "shipping");

        $found = false;
        try {
            $shipping_providers = $this->memcached_store->get_search($searchkeyvalues);

            $found = true;
        } catch (\Exception $e) {
            $found = false;
        }

        if ($found == false) {
            $shipping_providers = $this->get_all_shipping_providers_and_pricing();


            try {
                list($stored, $memcached_error_messages) = $memcached_store->store_search($searchkeyvalues, $shipping_providers, $this->database_expiry);
            } catch (\Exception $e) {
                #echo $e->getMessage();
                #   echo "Unable to store";
            }
        }


        return $shipping_providers;
    }

    public function get_all_shipping_providers_and_pricing() {
        $shipping_providers = array();

        $shipping_providers = $this->database->get_shipping_products();
        $shipping_groups = $this->database->get_shipping_groups();
        $shipping_countrystates = $this->database->get_shipping_groups_countrystates();

        #Attach countrystates to the groups
        foreach ($shipping_countrystates as $shipping_countrystate) {
            $cgid = $shipping_countrystate->groupid;
            $cgsid = $shipping_countrystate->groupsubid;
            if (isset($shipping_groups[$cgid])) {
                $group = $shipping_groups[$cgid];
                $countrystates = $group->countrystates;

                #TODO: Use countrystates id.
                #$countrystates[] = $shipping_countrystate;
                $countrystates[$cgsid] = $shipping_countrystate;

                $group->countrystates = $countrystates;
                $shipping_groups[$cgid] = $group;
            } else {
                # Ignore any orphaned countrystates with no groups.
            }
        }

        $pricing = $this->database->get_shipping_product_weight_rules();

        foreach ($pricing as $key => $price) {
            $id = $price->productid;

            if (isset($shipping_providers[$id])) {
                $existing_pricing = $shipping_providers[$id]->weight_rules;
                # just add the rule to the end of the array. 

                $existing_pricing[] = $price;
                $shipping_providers[$id]->weight_rules = $existing_pricing;
            }
        }

        $existing_price = null;

        #### VERIFY ALL THE RULES HAVE GROUPS THAT EXIST otherwise ECHO AN ERROR MESSAGE TO CONSOLE #####
        ### Add the groups to the rules (note one group can exist in many rules)
        foreach ($shipping_providers as $id => $shipping_provider) {
            $providername = $shipping_provider->providername;
            $providerproduct = $shipping_provider->productname;

            $existing_pricing = $shipping_provider->weight_rules;

            foreach ($existing_pricing as $existing_price) {

                $findgroupid = $existing_price->groupid;
                if (($findgroupid != null) && ($findgroupid != 0)) {
                    if (!isset($shipping_groups[$findgroupid])) {
                        echo "Can't find the group referenced by rule for " . $providername . " , " . $providerproduct;
                    } else {
                        # Attach the group to the rules.
                        # It's one group per weightrule
                        $existing_price->group = $shipping_groups[$findgroupid];
                    }
                }
            }
        }
        return $shipping_providers;
    }

    public function &get_all_shipping_groups() {
        $shipping_groups = $this->database->get_shipping_groups();

        $shipping_countrystates = $this->database->get_shipping_groups_countrystates();

        foreach ($shipping_countrystates as $shipping_countrystate) {
            $cgid = $shipping_countrystate->groupid;
            $cgsid = $shipping_countrystate->groupsubid;
            if (isset($shipping_groups[$cgid])) {
                $group = $shipping_groups[$cgid];
                $countrystates = $group->countrystates;
#$countrystates[] = $shipping_countrystate;
                $countrystates[$cgsid] = $shipping_countrystate;

                $group->countrystates = $countrystates;
                $shipping_groups[$cgid] = $group;
            } else {
# Ignore any orphaned countrystates with no groups.
            }
        }

        return $shipping_groups;
    }

    public function create_mail_template($templatename, $to, $custom_fields) {
        $email_templates = new email_templates($this, $this->smarty);

        $email = $email_templates->create_mail_template($templatename, $to, $custom_fields);

        return $email;
    }

    function sanitise($data, $whatToKeep) {

        $data = array_intersect_key($data, $whatToKeep);

        foreach ($data as $key => $value) {
            $data[$key] = $this->sanitise_by_type($data[$key], $whatToKeep[$key]);
        }

        return $data;
    }

    function sanitise_by_type($data, $type) {



        if ($type == "int") {
            $data = (int) $data;
        } elseif ($type == "arr") {

            #Sanitise the whole array as strings
            foreach ($data as &$value) {
                $value = $this->sanitise_string($value);
            }
            #Do nothing
        } else {
            //could be an array
            $data = $this->sanitise_string($data);

            if ($data == "") {
                $data = null;
            }
            /*
             *             if (is_array($data)) {
              foreach ($data as &$value) {
              $value = $this->sanitise_string($value);
              }
              } else {
              $data = $this->sanitise_string($data);

              if ($data == "") {
              $data = null;
              }
              }
             */
        }
        return $data;
    }

    # This function takes the postback arrays GET POST
    # and if the $keep doesn't exist -> it adds them as null values

    public function process_postback($keep) {
        #$get_array = $this->sanitise($_GET, $keep);
        #$post_array = $this->sanitise($_POST, $keep);
        #$merged = array_merge($get_array, $post_array);

        $merged = $this->getpost_array;


        // Always return null for empty fields that are wanted but 
        // not passed in to save us doing isset call.
        foreach ($keep as $key => $value) {
            if (!isset($merged[$key])) {
                $merged[$key] = null;
            }
        }

        return $merged;
    }

    # This function takes the postback arrays GET POST
    # and if the $keep doesn't exist -> it adds them as null values

    public function process_postback2($merged, $keep) {
        #$get_array = $this->sanitise($_GET, $keep);
        #$post_array = $this->sanitise($_POST, $keep);
        #$merged = array_merge($get_array, $post_array);
        #$merged = $this->getpost_array;
        // Always return null for empty fields that are wanted but 
        // not passed in to save us doing isset call.
        foreach ($keep as $key => $value) {
            if (!isset($merged[$key])) {
                $merged[$key] = null;
            }
        }

        return $merged;
    }

    public function get_countries_array() {
        #Relative to "client_code/model" folder
        $file = "../countries/master";
        $handle = $fopen($file, "r");

        $array = array();
        while (!feof($handle)) {
            $data = fread($handle, 4096);
            echo $data;
            # Put key values into array
            # ATM we just use the keys.
            $array[$data] = $data;
        }

        return $array;
    }

    // DEPRECATED
    public function get_country_options($country, $shop_country) {
        //Read the countries.txt and output
        $client_root = $GLOBALS["sb_client_site_path"] . "/www";
        $filename = $client_root . "/countries.txt";
        $handle = @fopen($filename, "r");
        if ($handle == NULL) {
            echo "Failed to open configuration file " . $filename;
            return;
        }

        $all = "";
        $empty_country = "";
        $empty_state = "";
        $choose_country = "";
        $choose_state = "";
        $sep = "";

        $str = "";
        while (($buffer = fgets($handle, 4096)) !== false) {
            #trim \n from file for WINDOWS we must trim \r\n
            #$buffer = rtrim($buffer,$this -> line_ending);
            $buffer = rtrim($buffer);
            if (mb_substr($buffer, 0, 1) == "#") {
                continue;
            }
            if ($buffer == "") {
                continue;
            }

            # 2 elements in a line of format 
            # Country=region|region|region
            # element 1 is country
            # element 2 is region
            $line_array = explode("=", $buffer);

            if ($line_array[0] == "default_country") {
                $default_country = $line_array[1];
                continue;
            }
            if ($line_array[0] == "default_state") {
                $default_state = $line_array[1];
                continue;
            }

            if ($line_array[0] == "all") {
                $all = $line_array[1];
                $all = "<option value='all'>" . $all . "</option>";
                continue;
            }

            if ($line_array[0] == "empty_country") {
                $empty_country = $line_array[1];
                $empty_country = "<option value=''>" . $empty_country . "</option>";
                continue;
            }

            if ($line_array[0] == "choose_country") {
                $choose_country = $line_array[1];
                if ($default_country == "choose_country") {
                    $choose_country = "<option value='' selected>" . $choose_country . "</option>";
                } else {
                    $choose_country = "<option value=''>" . $choose_country . "</option>";
                }
                continue;
            }

            if ($line_array[0] == "empty_state") {
                $empty_state = $line_array[1];
                $empty_state = "<option value=''>" . $empty_state . "</option>";
                continue;
            }

            if ($line_array[0] == "choose_state") {
                $choose_state = $line_array[1];
                $choose_state = "<option value=''>" . $choose_state . "</option>";
                continue;
            }

            #if ($line_array[0] == "sep") {
            #$sep = $line_array[1];
            #$empty = "<option value='separator'>" . $sep . "</option>";
            #continue;
            #}

            if ($line_array[0] == "Separator") {
                $sep = $line_array[1];
                $sep = "<option value='separator'>" . $sep . "</option>";
                $str .= "<option value=''>" . $line_array[1] . "</option>";

                continue;
            }

            if (($country != null) && ($line_array[0] == $country)) {
                $str .= "<option value=" . $line_array[0] . " selected='selected'>" . $line_array[0] . "</option>";
            } else {
                $str .= "<option value=" . $line_array[0] . ">" . $line_array[0] . "</option>";
            }
        }
        if (!feof($handle)) {
            echo "Error: unexpected fgets() fail\n";
        }
        fclose($handle);

        return array($str, $all, $empty_country, $empty_state, $choose_country, $choose_state, $sep);
    }

    public function get_all_countries() {
        $countries = $this->database->get_all_countries();

        foreach ($countries as $countrykey => $country) {
            $countryid = $country->countryid;
            $country->states = $this->database->get_states($countryid);
        }

        $country_count = count($countries);

        #var_dump($countries);
        #echo "<br/>";

        return array($countries, $country_count);
    }

    public function get_countries_structure() {

        $countries_structure = new \client_code\countries(array());
        list($countries_structure->countries, $countries_count) = $this->get_all_countries();

        $countries_structure->default_country = $this->config_get_setting("countries_default_country");
        $countries_structure->default_state = $this->config_get_setting("countries_default_state");
        $countries_structure->empty_country = $this->config_get_setting("countries_empty_country");
        $countries_structure->empty_state = $this->config_get_setting("countries_empty_state");
        $countries_structure->choose_country = $this->config_get_setting("countries_choose_country");
        $countries_structure->choose_state = $this->config_get_setting("countries_choose_state");
        $countries_structure->all = $this->config_get_setting("countries_all");
        $countries_structure->separator = $this->config_get_setting("countries_separator");

        #var_dump($this -> config_get_setting("countries_separator"));
        #var_dump($countries_structure -> separator);
        #var_dump($countries);
        #echo "<br/>";

        return $countries_structure;
    }

    public function get_country($countryid) {
        $countries_list = $this->get_countries_structure();

        #var_dump($countries_list);

        $countries = $countries_list->countries;

        #var_dump($countries);

        $found_country = null;
        foreach ($countries as $key => $country) {
            if ($key == $countryid) {
                $found_country = $country;
                break;
            }
        }
        #var_dump($found_country);
        return $found_country;
    }

    public function get_states($countryid) {
        #$states
    }

    #TODO: Only delete countries or states that are surplus to our list
    # We need to delete them save all because of the numbering system used.
    # However we could just overlay the numbers onto the list and delete anything with a 0 at the end of it.
    # 
    # 1 2 3 -1 4 5 6 
    # 

    public function save_countries_structure($countries) {
        #$model = $this -> model;

        $this->config_set_setting("countries_default_country", $countries->default_country);
        $this->config_set_setting("countries_default_state", $countries->default_state);
        $this->config_set_setting("countries_empty_country", $countries->empty_country);
        $this->config_set_setting("countries_empty_state", $countries->empty_state);
        $this->config_set_setting("countries_choose_country", $countries->choose_country);
        $this->config_set_setting("countries_choose_state", $countries->choose_state);
        $this->config_set_setting("countries_all", $countries->all);
        $this->config_set_setting("countries_separator", $countries->separator);


        /*
          $this->database->delete_all_countries_and_states();

          $country_count = count($countries);
          foreach ($countries->countries as $countrykey => $country) {
          $this->database_create_country($countrykey, $country->name, $country->code);
          $states = $country->states;
          foreach ($states as $statekey => $state) {
          $this->database->create_state($countrykey, $statekey, $state->name, "");
          }
          } */
    }

    public function array_element_move_down($countries, $country_previous_id, $distance) {

        $count = count($countries);
        #echo "count is " . $count;
        #var_dump($count

        if ($distance > $count) {
            $distance = $count;
        }

        if ($distance <= 0) {
            return true;
        }

        if ($country_previous_id > $count - 1) {
            return false;
        }

        $country_new_id = $country_previous_id - $distance;
        if ($country_new_id < 0) {
            $country_new_id = 0;
        }

        #if ($country_new_id == 0) {
        #return false;
        #}
        #var_dump($country_new_id);
        #0, 1, 2, 3, 4, INSERT (10 to 5),  5, 6, 7, 8, 9, CUT (10), 11, 12;
        #0, 1, 2, 3, 4, CUT (5), 6, 7, 8, 9, INSERT (5 to 10), 10, 11, 12;
        #0, 1, (INSERT 3 to 2)), 2, CUT 3
        #COPY 0, 1, 2, ( 3 )    (country previous id onwards)
        #COPY  2 and anything up to 3 (NOTHING MORE)   position 2 (country new id), length 1
        #COPY 0, 1    (everything up to new id minus 1, add 1 for length so new id)
        # ATTACH, 0, 1 to 3 to 2 

        $new_array = $countries;

        if ($country_new_id < $country_previous_id) {
            # Every element BEFORE the insert position
            # elements  11, 12
            # 10 + 1 = 11
            $after_previous = array();
            # 4 is not less than count, correct
            if ($country_previous_id + 1 < $count) {
                #echo "Cmd is A: " . ($country_previous_id + 1);
                $after_previous = array_slice($countries, $country_previous_id + 1);
            }

            # 10 - 5 = 5
            $length = $country_previous_id - $country_new_id;
            # elements 5, 6, 7, 8, 9
            # 5, $length - 1 = 5 - 1 = 4
            # 3 -2 = 1
            # No elements between before and after, correct
            $between_before_and_previous = array();
            if ($length > 0) {
                #echo "Cmd is B: " . $country_new_id . " " . ($length);
                $between_before_and_previous = array_slice($countries, $country_new_id, $length);
            }

            # elements 0, 1, 2, 3, 4
            # 0, 4
            #
            # 
            $before = array();
            if ($country_new_id > 0) {
                #echo "Cmd is C: " . ($country_new_id);
                $before = array_slice($countries, 0, $country_new_id);
            }

            #var_dump($before);
            # Rejoin the parts
            # 0,1,2,3,10,5,6,7,8,9,11,12
            #$new_array = clone $before;
            #combining 
            #echo "Combining as follows: ";
            #var_dump($before);
            $new_array = array_values($before);

            #var_dump($countries[$country_previous_id]);
            $new_array[] = $countries[$country_previous_id];

            #var_dump($between_before_and_previous);
            $new_array = array_merge($new_array, $between_before_and_previous);

            #var_dump($after_previous);
            $new_array = array_merge($new_array, $after_previous);
        }


        $countries = $new_array;
        #$countries = clone $new_array;
        #$temp = clone ($countries[$id]);
        #$countries[$id] = clone ($countries[$country_new_id]);
        #$countries
        # Put first element into temp variable
        # Move second element to first
        # Put first back in

        return $countries;
    }

    public function move_country_up($country_previous_id, $distance) {

        #var_dump($country_previous_id);
        #var_dump($distance);
        #var_dump($country_previous_id);
        #var_dump($distance);
        # Change the countries sort order by distance
        # If 
        list($countries, $count) = $this->get_all_countries();

        $con = $countries[$country_previous_id];
        $con->display_order = $con->display_order - 1;


        foreach ($countries as $country) {
            if ($country->countryid == $con->countryid) {
                #$this->database->update_country($country->countryid, $country->display_order, $country->name, $country->code);
                continue;
            }
            if ($con->display_order == $country->display_order) {
                $country->display_order = $country->display_order + 1;
            }
            if ($con->display_order > $country->display_order) {
                $country->display_order = $country->display_order - 1;
            }
        }

        foreach ($countries as $country) {
            $country->set_comparator($country->display_order);
        }

        $countries = array_values($countries);
        $quick_sort = new \client_code\quick_sort();
        $countries = $quick_sort->sort($countries);

        $countries = array_reverse($countries);

        foreach ($countries as $key => $country) {
            $this->database->update_country($country->countryid, $key, $country->name, $country->code);
        }

        /*
          $countries_structure = $this->get_countries_structure();

          $countries = $countries_structure->countries;

          $countries = $this->array_element_move_down($countries, $country_previous_id, $distance);

          $countries_structure->countries = $countries;

          $this->save_countries_structure($countries_structure);
         * 
         */
    }

    public function move_state_down($countryid, $state_previous_id, $distance) {
        #var_dump($country_previous_id);
        #var_dump($distance);
        #var_dump($country_previous_id);
        #var_dump($distance);
        # Change the countries sort order by distance
        # If 
        list($countries, $count) = $this->get_all_countries();

        $con = $countries[$countryid];
        #$con->display_order = $con->display_order + 1;


        $states = $con->states;
        $sta = $states[$state_previous_id];

        #foreach ($states as $state) {
        #if ($state->stateid == $sta->stateid) {

        $sta->display_order = $sta->display_order + 1;
        #}
        #}
        #var_dump($sta);

        foreach ($states as $state) {
            #echo "state id " . $state -> stateid . "<br/>";
            #echo "sort id was " . $state -> display_order  . "<br/>";

            if ($state->stateid == $sta->stateid) {
                #$sta->display_order += 10;
                #$this->database->update_state($state->stateid, $state->display_order, $state->name, $state->code);
                continue;
            }
            if ($sta->display_order == $state->display_order) {
                $state->display_order = $state->display_order - 1;
            }
            if ($sta->display_order < $state->display_order) {
                $state->display_order = $state->display_order + 1;
            }

            #echo "state id " . $state -> stateid . "<br/>";
            #echo "sort id is now " . $state -> display_order  . "<br/>";
        }

        foreach ($states as $state) {
            $state->set_comparator($state->display_order);
        }

        $states = array_values($states);
        $quick_sort = new \client_code\quick_sort();
        $states = $quick_sort->sort($states);

        $states = array_reverse($states);

        foreach ($states as $key => $state) {
            $this->database->update_state($state->stateid, $key, $state->name, $state->code);
        }

        /*
          $countries_structure = $this->get_countries_structure();

          $countries = $countries_structure->countries;

          $countries = $this->array_element_move_down($countries, $country_previous_id, $distance);

          $countries_structure->countries = $countries;

          $this->save_countries_structure($countries_structure);
         * 
         */


        /*
          #var_dump($country_previous_id);
          #var_dump($distance);


          $countries_structure = $this->get_countries_structure();

          #$countries = $countries_

          $country = $this->get_country($countryid);

          $states = $country->states;

          $countries = $countries_structure->countries;



          $states = $this->array_element_move_down($states, $state_previous_id, $distance);

          $country->states = $states;

          #var_dump($states);
          #die();
          #var_dump($countries);

          $countries[$countryid] = $country;

          $countries_structure->countries = $countries;

          #var_dump($countries);
          # Saving structure
          #die();
          $this->save_countries_structure($countries_structure);
         * 
         */
    }

    public function move_state_up($countryid, $state_previous_id, $distance) {

        list($countries, $count) = $this->get_all_countries();

        $con = $countries[$countryid];
        #$con->display_order = $con->display_order + 1;
        #var_dump($countryid);

        $states = $con->states;
        $sta = $states[$state_previous_id];

        $sta->display_order = $sta->display_order - 1;

        #var_dump($sta);
        #var_dump($states);

        foreach ($states as $state) {
            if ($state->stateid == $sta->stateid) {
                #$this->database->update_state($state->stateid, $state->display_order, $state->name, $state->code);
                continue;
            }
            if ($sta->display_order == $state->display_order) {
                $state->display_order = $state->display_order + 1;
            }
            if ($sta->display_order > $state->display_order) {
                $state->display_order = $state->display_order - 1;
            }
        }

        foreach ($states as $state) {
            $state->set_comparator($state->display_order);
        }

        $states = array_values($states);
        $quick_sort = new \client_code\quick_sort();
        $states = $quick_sort->sort($states);

        $states = array_reverse($states);

        foreach ($states as $key => $state) {
            $this->database->update_state($state->stateid, $key, $state->name, $state->code);
        }
        /*
          #var_dump($country_previous_id);
          #var_dump($distance);


          $countries_structure = $this->get_countries_structure();

          $country = $this->get_country($countryid);

          $states = $country->states;

          $countries = $countries_structure->countries;



          $states = $this->array_element_move_up($states, $state_previous_id, $distance);

          $country->states = $states;

          $countries[$countryid] = $country;

          $countries_structure->countries = $countries;

          $this->save_countries_structure($countries_structure);
         * 
         */
    }

    public function array_element_move_up($countries, $country_previous_id, $distance) {
        $count = count($countries);
        #echo "count is " . $count;
        #var_dump($count

        if ($distance > $count) {
            $distance = $count;
        }

        if ($distance <= 0) {
            return true;
        }

        if ($country_previous_id > $count - 1) {
            return false;
        }

        $country_new_id = $country_previous_id + $distance;
        if ($country_new_id < 0) {
            $country_new_id = 0;
        }

        #if ($country_new_id == 0) {
        #return false;
        #}
        #var_dump($country_new_id);
        # previous id  2
        # new ID 4
        #START 0, 1, 2, 3, 4, 5
        #END 0, 1, 3, 4, 2, 5
        #
        # A COPY 0,1
        # B Copy 3, 4,
        # C previous ID (2)
        # D COPY everything from new ID + 1 onwards
        # ATTACH  0, 1, 3, 4, 2, 5, 6, 7
        #         A,    B,    C, D
        # 

        $new_array = $countries;

        if ($country_new_id > $country_previous_id) {


            $before = array();
            if ($country_previous_id > 0) {

                # 0, 1
                #echo "Cmd is C: " . ($country_previous_id); # . " " . $length;
                $before = array_slice($countries, 0, $country_previous_id);
            }



            $length = $country_new_id - $country_previous_id;

            $between_before_and_previous = array();
            if ($length > 0) {
                # 3, 4
                #echo "Cmd is B: " . $country_previous_id + 1 . " " . ($length);
                $between_before_and_previous = array_slice($countries, $country_previous_id + 1, $length);
            }

            $after_previous = array();

            if ($country_new_id + 1 < $count) {
                # NOTHING
                #echo "Cmd is A: " . ($country_previous_id + 1);
                $after_previous = array_slice($countries, $country_new_id + 1);
            }

            #echo count($before);
            #var_dump($before);
            $new_array = array_values($before);

            #echo count($between_before_and_previous);
            #var_dump($between_before_and_previous);
            $new_array = array_merge($new_array, $between_before_and_previous);

            #var_dump($countries[$country_previous_id]);
            $new_array[] = $countries[$country_previous_id];


            #var_dump($after_previous);
            #echo count($after_previous);
            $new_array = array_merge($new_array, $after_previous);
        }


        $countries = $new_array;
        #$countries = clone $new_array;
        #$temp = clone ($countries[$id]);
        #$countries[$id] = clone ($countries[$country_new_id]);
        #$countries
        # Put first element into temp variable
        # Move second element to first
        # Put first back in

        return $countries;
    }

    public function move_country_down($country_previous_id, $distance) {

        #var_dump($country_previous_id);
        #var_dump($distance);
        # Change the countries sort order by distance
        # If 
        list($countries, $count) = $this->get_all_countries();

        $con = $countries[$country_previous_id];
        $con->display_order = $con->display_order + 1;


        foreach ($countries as $country) {
            if ($country->countryid == $con->countryid) {
                #$this->database->update_country($country->countryid, $country->display_order, $country->name, $country->code);
                continue;
            }
            if ($con->display_order == $country->display_order) {
                $country->display_order = $country->display_order - 1;
            }
            if ($con->display_order < $country->display_order) {
                $country->display_order = $country->display_order + 1;
            }
        }

        foreach ($countries as $country) {
            $country->set_comparator($country->display_order);
        }

        $countries = array_values($countries);
        $quick_sort = new \client_code\quick_sort();
        $countries = $quick_sort->sort($countries);

        $countries = array_reverse($countries);

        foreach ($countries as $key => $country) {
            $this->database->update_country($country->countryid, $key, $country->name, $country->code);
        }

        # 1.) Using the display_order as comparator, sort the countries into order
        # Once this is done, the new array positions become their new display_order,
        # Save this to disk using the update command

        /*
          $countries_structure = $this->get_countries_structure();

          $countries = $countries_structure->countries;

          $countries = $this->array_element_move_up($countries, $country_previous_id, $distance);

          $countries_structure->countries = $countries;

          $this->save_countries_structure($countries_structure);
          #$this->save_countries_sort_order($countries_structure);
         * 
         */
    }

    public function add_country($name) {

        $country = new \client_code\country(array());
        $country->name = $name;
        $country->code = "";

        $countrykey = 0;
        $countryid = $this->database_create_country($countrykey, $country->name, $country->code);


        $country->name = $name;

        $countryname = $country->name;

        list($countries, $countries_count) = $this->get_all_countries();


        foreach ($countries as $countrykey => &$con) {
            $countryid = $con->countryid;

            if ($con->name == $countryname) {
                #echo "updating " . $countrykey;

                foreach ($con->states as $statekey => $state) {
                    $stateid = $state->stateid;

                    $this->database->create_state($countryid, $stateid, $state->name, "");
                }
            }
        }

        #die();
        /*
          $countries_structure = $this->get_countries_structure();
          $countries = $countries_structure->countries;

          $country = new \client_code\country(array());

          $country->name = $name;

          $countryname = $country->name;

          # Find all countries with the same name and update states
          foreach ($countries as $countrykey => &$con) {
          if ($con->name == $countryname) {
          echo "updating " . $countrykey;

          $country->states = $con->states;
          break;
          }
          }

          # add to start of array;
          array_unshift($countries, $country);

          $countries_structure->countries = $countries;


          $this->save_countries_structure($countries_structure);
         * 
         *
         */
        return $countryid;
    }

    public function &database_create_country($sortid, $name, $code) {

        $countryid = null;
        try {
            $countryid = $this->database->create_country($sortid, $name, $code);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $countryid;
    }

    # If country already exists, return true

    public function is_country($name) {
        $countries_structure = $this->get_countries_structure();
        $countries = $countries_structure->countries;

        $found = false;
        foreach ($countries as $country) {
            if ($country->name == $name) {
                $found = true;
                break;
            }
        }
        return $found;
    }

    public function update_country($countryid, $newname) {
        $countries_structure = $this->get_countries_structure();

        $countries = $countries_structure->countries;

        $country = $countries[$countryid];

        #$country = new \client_code\country(array());
        #$country->name = $name;

        $countryname = $country->name;
        echo "searching for " . $countryname;

        # Find all countries with the same name and update states
        foreach ($countries as $countrykey => &$con) {
            if ($con->name == $countryname) {
                #echo "updating " . $countrykey;
                #$country -> states = $con -> states;
                #$name = $con -> name;
                #$code = $con -> code;
                $code = "";

                $this->database->update_country($countrykey, $countrykey, $newname, $code);
                ##break;
            }
        }
        #die();
        # add to start of array;
        #array_unshift($countries, $country);
        #$countries_structure->countries = $countries;
        #$this->save_countries_structure($countries_structure);
    }

    /*
      public function &database_update_country($countryid, $sortid, $name, $code) {

      try {
      $userid_in_array = $this->database->update_country($countryid, $sortid, $name, $code);
      } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
      }
      return $userid_in_array;
      } */

    public function add_state($countryid, $statename) {
        $sortid = 0;
        #var_dump($countryid);
        #die();
        $stateid = $this->database->create_state($countryid, $sortid, $statename, "");
        /*
          $countries_structure = $this->get_countries_structure();

          $countries = $countries_structure->countries;

          $country = $countries[$countryid];

          $countryname = $country->name;

          echo "searching for " . $countryname;

          # Find all countries with the same name and update states
          foreach ($countries as $countrykey => &$con) {
          if ($con->name == $countryname) {
          echo "updating " . $countrykey;

          $states = $con->states;

          $state = new \client_code\state(array());

          $state->name = $statename;

          # add to start of array;
          array_unshift($states, $state);

          $con->states = $states;
          $countries[$countrykey] = $con;
          }
          }

          #$countries[$countryid] = $country;

          $countries_structure->countries = $countries;

          $this->save_countries_structure($countries_structure);
         * 
         */

        return $stateid;
    }

    public function add_state_old($countryid, $statename) {
        $countries_structure = $this->get_countries_structure();

        $countries = $countries_structure->countries;

        $country = $countries[$countryid];

        $countryname = $country->name;

        $states = $country->states;

        $state = new \client_code\state(array());

        $state->name = $statename;

        # add to start of array;
        array_unshift($states, $state);

        $country->states = $states;

        $countries[$countryid] = $country;

        $countries_structure->countries = $countries;

        $this->save_countries_structure($countries_structure);
    }

    public function delete_state_old($countryid, $stateid) {
        $countries_structure = $this->get_countries_structure();

        $countries = $countries_structure->countries;

        $country = $countries[$countryid];

        $states = $country->states;

        if (isset($states[$stateid])) {
            unset($states[$stateid]);
        }

        $country->states = $states;

        $countries[$countryid] = $country;

        $countries_structure->countries = $countries;

        #Rekey array from 0
        $countries = array_values($countries);

        $this->save_countries_structure($countries_structure);
    }

    public function delete_state($countryid, $stateid) {


        list($countries, $countries_count) = $this->get_all_countries();

        #var_dump($countries);
        $country = $countries[$countryid];

        $countryname = $country->name;
        $states = $country->states;

        if (isset($states[$stateid])) {
            $statename = $states[$stateid]->name;
            #unset($states[$stateid]);
        }

        $country->states = $states;

        $this->database->delete_state($stateid);

        #echo "searching for " . $countryname;
        # Find all countries with the same name and update states
        foreach ($countries as $countrykey => &$con) {
            if ($con->name == $countryname) {
                #echo "updating " . $countrykey;

                $states = $con->states;

                #echo "searching state: " . $statename;
                foreach ($states as $stateid => &$state) {
                    if ($state->name == $statename) {
                        #echo "deleting state: " . $statename;
                        $this->database->delete_state($stateid);
                    }
                }
                #$con->states = $states;
            }
        }


        /*
          $countries_structure = $this->get_countries_structure();

          $countries = $countries_structure->countries;

          $country = $countries[$countryid];

          $countryname = $country->name;
          $states = $country->states;

          if (isset($states[$stateid])) {
          $statename = $states[$stateid]->name;
          #unset($states[$stateid]);
          }

          $country->states = $states;

          #echo "searching for " . $countryname;
          # Find all countries with the same name and update states
          foreach ($countries as $countrykey => &$con) {
          if ($con->name == $countryname) {
          #echo "updating " . $countrykey;

          $states = $con->states;

          #echo "searching state: " . $statename;
          foreach ($states as $stateid => &$state) {
          if ($state->name == $statename) {
          #echo "deleting state: " . $statename;
          unset($states[$stateid]);
          }
          }
          $con->states = $states;
          }
          }

          #$countries[$countryid] = $country;

          $countries_structure->countries = $countries;

          $this->save_countries_structure($countries_structure);
         * 
         */
    }

    public function delete_country($countryid) {
        $this->database->delete_country($countryid);
        /*
          $countries_structure = $this->get_countries_structure();

          $countries = $countries_structure->countries;
          unset($countries[$countryid]);

          #echo "unset";
          #Rekey array from 0
          $countries = array_values($countries);

          $countries_structure->countries = $countries;

          $this->save_countries_structure($countries_structure);
         */
        return true;
    }

    # Returns an array keyed on the country name of states

    //
    public function get_country_and_state_options($array) {
        $selected_country = "";
        $selected_state = "";
        $use_default_country = false;
        $use_default_state = false;
        $include_choose_country = false;
        $include_choose_state = false;
        $include_worldwide = false;
        $include_no_country = false;
        $include_no_state = false;
        $include_choose_country_separator = false;
        $include_worldwide_separator = false;
        $include_all_in_states = false;

        foreach ($array as $key => $value) {
            $$key = $value;

            #echo ${$key};
        }

        $countries_structure = $this->get_countries_structure();

        #$all = "";
        #$empty_country = "";
        #$empty_state = "";
        $choose_country = "";
        $choose_state = "";

        $choose_country = $this->get_error_message_string("COUNTRIES_CHOOSE_COUNTRY");
        $choose_state = $this->get_error_message_string("COUNTRIES_CHOOSE_STATE");
        $sep = $this->get_error_message_string("COUNTRIES_SEPARATOR");
        $all = $this->get_error_message_string("COUNTRIES_WORLDWIDE");
        $empty_country = $this->get_error_message_string("COUNTRIES_EMPTY_COUNTRY");
        $empty_state = $this->get_error_message_string("COUNTRIES_EMPTY_STATE");
        #$choose_country = $this -> get_
        #$sep = "";

        $states_options_array = array();

        $countries_options = "";
        #$found_countries = array("a" => "A");
        $found_countries = array();

        #$default_state = "";
        #$default_country = "";
        # Avoid selecting duplicates
        $found_selected = false;
        $countries = $countries_structure->countries;

        $default_country = $countries_structure->default_country;
        $default_state = $countries_structure->default_state;
        #$empty_country = $countries_structure->empty_country;
        #$empty_state = $countries_structure->empty_state;
        #$sep = $countries_structure->separator;
        #$all = $countries_structure->all;
        #var_dump($countries);

        $sep_option = "<option value=''>" . $sep . "</option>";

        #echo "hello";
        #var_dump($countries);
        $i = 0;
        foreach ($countries as $country) {
            #echo "abc";   
            ## For the case where no country is selected and no options are selected
            if ($i == 0) {
                #echo "abc";
                if ($include_worldwide == false && $include_choose_country == false) {
                    $selected_country = $country->name;

                    #echo $selected_country;
                }
            }
            $i++;


            # Keywords in the file, that are in english
            # all, empty_country, empty_state
            # choose_country, choose_state           

            if ($found_selected == false) {
                if (($selected_country != null) && ($country->name == $selected_country)) {
                    $countries_options .= "<option value='" . $country->name . "' selected='selected'>" . $country->name . "</option>";
                    $found_selected = true;
                } else {
                    if (($selected_country == null) && ($use_default_country == true) && ($default_country != null) && ($country->name == $default_country)) {
                        $countries_options .= "<option value='" . $country->name . "' selected='selected'>" . $country->name . "</option>";
                        $found_selected = true;
                    } else {
                        $countries_options .= "<option value='" . $country->name . "'>" . $country->name . "</option>";
                    }
                }
            } else {

                $countries_options .= "<option value='" . $country->name . "'>" . $country->name . "</option>";
            }

            $found_country = $country->name;

            if (in_array($found_country, $found_countries)) {
                #echo "Seen this country before, skipping " . $found_country;
                continue;
            } else {
                $found_countries[] = $found_country;
            }

            $states_list = $country->states;
            #echo count($states_list);
            #$states_list = array();
            #var_dump($states_list);







            $sep_option = "<option value=''>" . $sep . "</option>";




            if (count($states_list) > 0) {

                #echo count($states_list);
                $states_options_array[$country->name] = "";



                $choose_state_option = "<option value=''>" . $choose_state . "</option>";


                if ($include_choose_state == true) {
                    $states_options_array[$country->name] .= $choose_state_option;
                }

                $all_states_text_option = "<option value=''>All</option>";
                if ($include_all_in_states == true) {
                    $states_options_array[$country->name] .= $all_states_text_option;
                }
            }
            foreach ($states_list as $state) {
                $state = $state->name;
                #var_dump($state);
                #var_dump($default_state);
                #if ($state == $default_state) {




                if (($selected_state != null) && ($state == $selected_state)) {
                    $text = "<option value='" . $state . "' selected='selected'>" . $state . "</option>";
                    ;
                    #echo $text;
                    $states_options_array[$country->name] .= $text;
                    #var_dump($states[$line_array[0]][$state]);
                } elseif (($selected_state == null) && ($use_default_state == true) && ($default_state != null) && ($state == $default_state)) {
                    $text = "<option value='" . $state . "' selected='selected'>" . $state . "</option>";
                    #echo $text;
                    $states_options_array[$country->name] .= $text;
                } else {
                    $states_options_array[$country->name] .= "<option value='" . $state . "'>" . $state . "</option>";
                }
            }
        }




        # POST list formatting
        if ($include_worldwide == true) {

            if ($selected_country == "all") {
                $all_option = "<option value='all' selected>" . $all . "</option>";
            } else {
                $all_option = "<option value='all'>" . $all . "</option>";
            }
            #echo $all;
            #echo "got here";
            #$countries_options .= $sep;

            if ($include_worldwide_separator == true) {
                $countries_options = $all_option . $sep_option . $countries_options;
            } else {
                $countries_options = $all_option . $countries_options;
            }
        }

        $choose_country_option = "<option value=''>" . $choose_country . "</option>";

        #echo "merging";
        if ($include_choose_country == true) {
            #echo "merging";

            if ($include_choose_country_separator == true) {
                $countries_options = $choose_country_option . $sep_option . $countries_options;
            } else {
                $countries_options = $choose_country_option . $countries_options;
            }
        }

        //If not country was selected - set the states list to the default country - UNLESS use_default_country is false
        $states_country_array = array();
        if (($selected_country == "") && ($use_default_country == true)) {
            #list($default_country, $default_state) = $model->get_default_country_and_state();

            if (isset($states_options_array[$default_country])) {
                $states_country_array = $states_options_array[$default_country];
                #var_dump($states);
            }
        }


        #var_dump($states);
        #var_dump($states_options_array);
        #var_dump(array_keys($states_options_array));
        #var_dump($selected_country);

        if (isset($states_options_array[$selected_country])) {
            $states_country_array = $states_options_array[$selected_country];
            #var_dump($states);
        }
        #if (!feof($handle)) {
        #echo "Error: unexpected fgets() fail\n";
        #}
        #fclose($handle);
        #var_dump($states);

        return array($countries_options, $states_options_array, $states_country_array, $all, $empty_country, $empty_state, $choose_country, $choose_state, $sep);
    }

# Returns an array keyed on the country name of states

    public function get_default_country_and_state() {
        $default_country = $this->config_get_setting("countries_default_country");
        $default_state = $this->config_get_setting("countries_default_state");

        return array($default_country, $default_state);
    }

    public function convert_error_messages_code_array_to_strings($error_messages) {
        //var_dump($error_messages);
        foreach ($error_messages as $key => $error_messsage) {
            #TODO:  Is this worth optimising out the FOPEN? probably not.
            $found_string = $this->get_error_message_string($error_messsage);

            if ($found_string != null) {
                $error_messages[$key] = $found_string;
            }

            //Using smarty - Templatize each string
            #$error_messages[$key] = $this -> smarty->fetch('string:'.$error_messages[$key]);
            // Templatize each string
            require_once("templateview/templateview.php");

            $templateview = new templateview($this, $this->smarty);

            $error_messages[$key] = $templateview->draw_from_string($error_messages[$key]);
        }
        return $error_messages;
    }

    public function get_error_message_string($error_string) {
        $translation = null;
        $messages = $this->get_all_error_message_strings();

        #var_dump($error_string);
        #if (isset($messages[$error_string])) {
        if (array_key_exists($error_string, $messages) === true) {
            $translation = $messages[$error_string];
        } else {
            $translation = $error_string;
        }
        return $translation;
    }

    public function get_all_error_message_strings() {

        $client_root = $GLOBALS["sb_client_site_path"] . "/www";
        /*
          $filename = $client_root . "/themes/config/strings.txt";
         * 
         */
        #inited in client index
        #$theme_dir = $GLOBALS["theme_dir"];
        $site_id = $this->config_get_setting("site_id");
        $theme_dir = $this->config_get_setting("theme_dir");
        $filename = $client_root . "/themes/" . $site_id . "/" . $theme_dir . "/config/strings.txt";
        $config_error_messages = new \shared_code\Configuration($filename);
        $messages = $config_error_messages->get_settings();
        return $messages;
    }

    public function &database_update_order_state($orderid, $state) {
        $result = $this->database->update_order_state($orderid, $state);
        #$this->increment_version();
        return $result;
    }

    public function get_client_locale() {
        $locale = $this->config_get_setting("locale");

        return $locale;
    }

    public function get_machine_timezone() {
        $tz_machine = $this->server_config_get_setting("timezone");

        return $tz_machine;
    }

    public function create_base_url($fields, $append_postfix, $ignore_page_number_field = true) {


        $base_url = $_SERVER["SCRIPT_NAME"];
        $arg_count = 0;

        $i = 0;
        $prefix = "?";
        foreach ($fields as $key => $value) {
            if ($value == null) {
                continue;
            }

            // Ignore the existing page number 
            if (($key == "p") && ($ignore_page_number_field == true)) {
                continue;
            }

            $arg_count++;

            if ($i == 0) {
                $prefix = "?";
            } else {
                $prefix = "&";
            }

            $base_url .= $prefix . $key . "=" . $value;
            $i++;
        }

        if ($append_postfix == true) {
            if ($i == 0) {
                $prefix = "?";
            } else {
                $prefix = "&";
            }

            $base_url .= $prefix;
        }

        return array($base_url, $prefix, $arg_count);
    }

    public function &get_all_subscription_lists() {

        # Check if we have it in memory before going to memcache
        if ($this->subscription_lists != null) {
            return $this->subscription_lists;
        }



        $subscription_lists = array();
        $memcached_store = $this->memcached_store;


        $searchkeyvalues = array("allsubscriptionlists" => "allsubscriptionlists");

        $found = false;
        try {
            $subscription_lists = $this->memcached_store->get_search($searchkeyvalues);

            $found = true;
        } catch (\Exception $e) {
            $found = false;
        }

        if ($found == false) {

            $subscription_lists = $this->database->get_all_subscription_lists();

            try {
                list($stored, $memcached_error_messages) = $memcached_store->store_search($searchkeyvalues, $subscription_lists, $this->database_expiry);
            } catch (\Exception $e) {
                #echo $e->getMessage();
                #   echo "Unable to store";
            }
        }

        $this->subscription_lists = $subscription_lists;

        return ($subscription_lists);
    }

    public function get_all_subscribers() {
        $subscribers = $this->database->get_all_subscribers();

        $count = count($subscribers);

        return array($subscribers, $count);
    }

    public function get_subscriber_hash_or_generate_new_hash($email) {
        $unsubscribehash = null;
        $found = false;

        #Get the existing unsubscribe hash if any - reuse this hash
        list($subscribers, $count) = $this->get_all_subscribers();

        $found_subscriber = null;
        foreach ($subscribers as $subscriber) {
            if ($subscriber->username == $email) {
                $found_subscriber = $subscriber;
                $found = true;
                break;
            }
        }

        if ($found_subscriber != null) {
            $unsubscribehash = $subscriber->unsubscribehash;
        } else {
            $unix_timestamp = time();
            $newhash = hash("md5", $unix_timestamp);
            $unsubscribehash = pack("H*", $newhash);
        }

        return array($unsubscribehash, $found);
    }

    # This function takes a new array and keeps
    # matching keys from the $_GET and $_POST arrays
    # and if the $keep doesn't exist -> it adds them as null values
    # Hence some elements are lost from the $_GET $_POST if not specified in
    # $keep

    public function strip_postback($keep) {
        $getpost_array = $this->getpost_array;

        $new_array = array();

        foreach ($keep as $key => $value) {
            if (isset($getpost_array[$key])) {
                $new_array[$key] = $getpost_array[$key];
            } else {
                $new_array[$key] = null;
            }
        }

        return $new_array;
    }

    public function check_to_bool($check) {
        #var_dump($check);
        $bool = false;
        if ($check == "on") {
            $bool = true;
        }
        # Otherwise it could be "" or null so return false



        return $bool;
    }

    # delete_5_5=5_5
    # returns array of arrays of two numbers.

    function get_delete_list_2d() {
        $array = array();
        $getpost_array = $this->getpost_array;

        foreach ($getpost_array as $key => $value) {


            if (preg_match("/^[a-zA-Z]+_(\d+)_*(\d*)/", $key, $matches)) {

                $productid = $matches[1];

                # matches[2] returns string (0) "" if empty
                $variantid = $matches[2];

                $productid = (int) $productid;
                $variantid = (int) $variantid;

                #$id = (int) $id;
                $value = (int) $value;

                if ($value < 1) {
                    $value = 1;
                }

                /* Any value that is < 1 must be 1 */
                $array[] = array($productid, $variantid, $value);
            }
        }
        return $array;
    }

    function get_delete_list() {
        $list = array();
        $getpost_array = array_merge($_GET, $_POST);

        foreach ($getpost_array as $key => $value) {
            #"/^delete_(.*)/"
            if (preg_match("/^[a-zA-Z]+_(\d+)/", $key, $matches)) {

                $id = $matches[1];

                $id = (int) $id;
                $value = (int) $value;

                if ($value < 0) {
                    $value = 0;
                }

                /* Any value that is < 1 must be 1 */
                $list[$id] = $value;
            }
        }
        return $list;
    }

    function get_delete_list_using_keyword($string) {
        $list = array();
        $getpost_array = array_merge($_GET, $_POST);

        foreach ($getpost_array as $key => $value) {
            #"/^delete_(.*)/"
            if (preg_match("/^" . $string . "_(\d+)/", $key, $matches)) {

                $id = $matches[1];

                $id = (int) $id;
                #$value = (int) $value;
                $value = (string) $value;

                /*
                  if ($value < 0) {
                  $value = 0;
                  } */

                /* Any value that is < 1 must be 1 */
                $list[$id] = $value;
            }
        }
        return $list;
    }
    
    function get_delete_list_2d_using_keyword($string) {
        $array = array();
        $getpost_array = array_merge($_GET, $_POST);

        foreach ($getpost_array as $key => $value) {
            #"/^delete_(.*)/"
            if (preg_match("/^" . $string . "_(\d+)_*(\d*)/", $key, $matches)) {                
                $productid = $matches[1];

                # matches[2] returns string (0) "" if empty
                $variantid = $matches[2];

                $productid = (int) $productid;
                $variantid = (int) $variantid;

                #$id = (int) $id;
                $value = (int) $value;

                if ($value < 1) {
                    $value = 1;
                }

                /* Any value that is < 1 must be 1 */
                $array[] = array($productid, $variantid, $value);
            }
        }
        return $array;
    }

    function get_delete_list_string() {
        #var_dump($_GET);
        $list = array();
        $getpost_array = array_merge($_GET, $_POST);

        foreach ($getpost_array as $key => $value) {

            if (preg_match("/^delete_(.*)/", $key, $matches)) {

                $id = $matches[1];

                $id = (string) $id;
                $value = (string) $value;

                /*
                  if ($value < 1) {
                  $value = 1;
                  } */

                /* Any value that is < 1 must be 1 */
                $list[$id] = $value;
            }
        }
#        var_dump($list);
        return $list;
    }

    function get_delete_list_using_keyword_and_get_keynames($string) {
        $list = array();
        $getpost_array = array_merge($_GET, $_POST);

        foreach ($getpost_array as $key => $value) {

            #var_dump($key);
            #"/^delete_(.*)/"
            if (preg_match("/^" . $string . "_(\d+)_(\w+)/", $key, $matches)) {

                $id = $matches[1];
                $keyname = $matches[2];

                $id = (int) $id;
                #$value = (int) $value;
                $value = (string) $value;

                /*
                  if ($value < 0) {
                  $value = 0;
                  } */

                /* Any value that is < 1 must be 1 */
                if (isset($list[$id])) {
                    $list[$id][$keyname] = $value;
                } else {
                    $list[$id] = array();
                    $list[$id][$keyname] = $value;
                }
            }
        }
        return $list;
    }

    public function database_get_customer_orders_paged($customerid, $current_page, $page_size) {
        #list ($orders_array, $orders_count) = $this -> database->get_all_customer_orders($customerid);
        list ($orders_array, $orders_count) = $this->get_customer_orders($customerid);


        $page_count = $this->get_page_count($orders_count, $page_size);

        $page_orders_array = $this->get_page_of_stuff($orders_array, $orders_count, $current_page, $page_size);

        //var_dump($orders_array);

        return array($page_orders_array, $orders_count, $page_count);
    }

    // No point having all customers orders memcached
    // 1,000,000 customer orders of 1,000 bytes takes up 1 GB of space
    // We don't want to waste RAM with that.
    // Even if companies reach 100,000 orders, that's 100MB of ram per site
    // So we memcache per customerid    
    // Further notes - because we don't increment the site version after placing an order
    //   the customer orders list from memcache will be stale!
    //   option 1 - increment version (really? depends on how often orders are created)
    //   //           ideally we only want to increment/flush when stock level changes.
    //        
    //   option 2 - don't memcache customer data incl order data
    public function get_customer_orders($customerid) {
        $custorders = array();
        $custorders_count = 0;
        $memcached_store = $this->memcached_store;

        # Check if we have it in memory before going to memcache
        # Note: stale cache issues can occur on updates - this applies to admin_code
        #if ($this->products != null) {
        #$products_count = count($products);
        #return array($this->products, $products_count);
        #}

        $searchkeyvalues = array("custorders" => "custorders", "CID" => $customerid);

        $found = false;
        try {
            // purposefully left commented out.
            //list($custorders, $custorders_count) = $this->memcached_store->get_search($searchkeyvalues);
            //$found = true;
        } catch (\Exception $e) {
            $found = false;
        }


        if ($found == false) {

            $custorders = $this->database->get_customer_orders($customerid);

            //var_dump($custorders);

            foreach ($custorders as $order) {
                $order->orderstatename = $this->get_error_message_string($order->orderstatename);
            }

            foreach ($custorders as $order) {
                $orderid = $order->orderid;

                $order_taxes = $this->database->get_all_order_taxes();
                $found_rules = array();
                foreach ($order_taxes as $tax_item) {
                    if ($tax_item->orderid == $orderid) {
                        $found_rules[] = $tax_item;
                    }
                }

                $order->taxes = $found_rules;



                $order_shipping_taxes = $this->database->get_all_order_shipping_taxes();
                $found_rules = array();
                foreach ($order_shipping_taxes as $tax_item) {
                    if ($tax_item->orderid == $orderid) {
                        $found_rules[] = $tax_item;
                    }
                }

                $order->shipping_taxes = $found_rules;


                // Apply the taxes that apply to this order

                $parcels = $order->parcels;
                foreach ($parcels as $parcel) {
                    $parcelid = $parcel->parcelid;

                    foreach ($products as $product) {
                        $variants = $product->variants;
                        $productid = $product->productid;
                        foreach ($variants as $variant) {
                            $variantid = $variant->variantid;

                            $order_parcel_product_taxes = $this->database->get_all_order_parcel_product_taxes();
                            $found_rules = array();
                            foreach ($order_parcel_product_taxes as $tax_item) {
                                if (($tax_item->orderid == $orderid) && ($tax_item->parcelid = $parcelid) && ($tax_item->productid == $productid) && ($tax_item->variantid == $variantid)) {
                                    $found_rules[] = $tax_item;
                                }
                            }

                            #$variant->taxes = $found_rules;
                            $variant->subtotal_taxes = $found_rules;
                        }
                    }
                }
            }


            #echo count($custorders);

            $custorders_count = count($custorders);

            try {
                list($stored, $memcached_error_messages) = $memcached_store->store_search($searchkeyvalues, array($custorders, $custorders_count), $this->database_expiry);
            } catch (\Exception $e) {
                #echo $e->getMessage();
            }
        }

        #$custorders_count = count($custorders);
        return array($custorders, $custorders_count);
    }

    public function retrieve_cookie_data($cookie_name) {
        $data = array();

        $str_parts = array();

        $data = "";
        $i = 1;
        foreach ($_COOKIE as $key => $cookie) {
            $part_name = $cookie_name . $i;
            if (array_key_exists($part_name, $_COOKIE)) {

                $chunk = $_COOKIE[$part_name];

                # Stuffs gets urlencoded - no need for base64 which is 33% larger
                #LZF and BZIP2 would need to be compiled.. DOH!
                #$chunk = \lzf_decompress($chunk);
                #$chunk = bzuncompress($chunk);
                $chunk = gzuncompress($chunk);

                $data .= $chunk;
                $i++;
                continue;
            } else {
                break;
            }
        }

        if ($data != array()) {
            $crc32 = substr($data, 0, 4);

            #var_dump($crc32);
            $data = substr($data, 4);
            #substr

            $compare_crc32 = md5($data);
            #var_dump($compare_crc32);

            if ($crc32 == $compare_crc32) {
                $data = unserialize($data);
                #echo "cookie is ok";
            } else {
                $data = array();
                #echo "cookie is invalid CRC";
            }

            #$data = unserialize($data);
        } else {
            
        }

        return $data;
    }

    public function set_machine_locale() {
#var_dump(setlocale(LC_TIME, 'fr_FR.UTF8', 'fr.UTF8', 'fr_FR.UTF-8', 'fr.UTF-8'));
#$locale = "en_AU.UTF-8";
        $locale = null;
        $return = setlocale(LC_ALL, $locale);
        if ($return === false) {
            echo "Setting locale failed " . $locale;
        }

        return $locale;
    }

# Set the client locale();

    public function set_client_locale() {
        $model = $this;
# Read file, get the locale
        /*
          $client_root = $GLOBALS["sb_client_site_path"];
          $tz_config_filename = $client_root . "/configuration/client_config.txt";

          $tz_config = new \shared_code\configuration($tz_config_filename);
         * 
         */

        #$tz_config = $this -> config_get_site_config();
#$locale = $tz_config->get_setting("locale");
#setlocale(LC_ALL, $locale);
        $locale = $this->config_get_setting("locale_ctype");
        $return = setlocale(LC_CTYPE, $locale);
        if ($return === false) {
            echo "Setting locale failed " . $locale;
        }

        $locale = $this->config_get_setting("locale_collate");
        $return = setlocale(LC_COLLATE, $locale);
        if ($return === false) {
            echo "Setting locale failed " . $locale;
        }

        $locale = $this->config_get_setting("locale_time");
        $return = setlocale(LC_TIME, $locale);
        if ($return === false) {
            echo "Setting locale failed " . $locale;
        }

        $locale = $this->config_get_setting("locale_numeric");
        $return = setlocale(LC_NUMERIC, $locale);
        if ($return === false) {
            echo "Setting locale failed " . $locale;
        }

        $locale = $this->config_get_setting("locale_monetary");
        $return = setlocale(LC_MONETARY, $locale);
        if ($return === false) {
            echo "Setting locale failed " . $locale;
        }

        /* we can't get the currency from this as this requires instantiating a client model...
         * well the model always get instantiated before this function so, given enough time, we could work this out (maybe pass the model in
         */
        /*
          $default_currency = $client_model -> get_default_currency();
          $locale = $default_currency -> locale_monetary;
          $return = setlocale(LC_MONETARY, $locale);
          if ($return === false) {
          echo "Setting locale from failed " . $locale;
          }
         * 
         */

        $cart_currency = $model->get_cart_currency();
        
        if ($cart_currency == null)
        {
            echo "No default cart currency found";   
        } else {
        $locale = $cart_currency->locale_monetary;
        $return = setlocale(LC_MONETARY, $locale);
        if ($return === false) {
            echo "Setting locale from failed " . $locale;
        }
        }

        $GLOBALS["locale"] = $locale;

        return $locale;
    }

    public function get_all_orders() {
        $orders = $this->database->get_all_orders();

        foreach ($orders as $order) {
            $order->orderstatename = $this->get_error_message_string($order->orderstatename);
        }

        $orders_count = count($orders);

        foreach ($orders as $order) {
            $orderid = $order->orderid;
            $parcels = $this->database->get_order_parcels($orderid);
            $order->parcels = $parcels;

            # re-index on 0 ?  this is done in the prior function
            #$order->parcels = array_keys($order -> parcels);
        }

        foreach ($orders as $order) {
            $orderid = $order->orderid;
            $parcels = $order->parcels;
            foreach ($parcels as $parcel) {
                $parcelid = $parcel->parcelid;
                #echo $parcelid;
                $products = $this->database->get_order_parcel_products($orderid, $parcelid);
                #var_dump($products);
                #die();
                $parcel->products = $products;
            }
        }

        foreach ($orders as $order) {
            $orderid = $order->orderid;
            $payment = $order->payment;
            #$paymentid = $payment -> paymentid;
            #echo $parcelid;
            $payments = $this->database->get_all_payments();
            $foundpayment = null;
            foreach ($payments as $payment) {
                if ($payment->orderid == $orderid) {
                    $foundpayment = $payment;
                }
            }
            #var_dump($products);
            #die();
            $order->payment = $foundpayment;
        }

        # for each order, get the orders parcels
        # for each parcel, get the products
        #var_dump($orders);

        return array($orders, $orders_count);
    }

    public function &get_order($orderid) {
        list($orders, $count) = $this->get_all_orders();

        #var_dump($orders);
        #var_dump(array_keys($orders));
        $order = null;
        if (isset($orders[$orderid])) {
            $order = $orders[$orderid];
        }
        return $order;
    }

    public function &get_image($productid, $variantid, $imageid) {
        $include_provisioning_variants = true;
        $options_array = array("include_provisioning_variants" => $include_provisioning_variants);
        $product = $this->get_product_and_associated($productid, $options_array);

        #$imageid = (int) $imageid;
        // Retrieves any product images (images associated with only the product)
        if ($variantid == 0) {

            $images = $product->images;

            $foundimage = null;
            foreach ($images as $image) {
                if ($image->variantid == $variantid) {
                    if ($image->imageid == $imageid) {
                        $foundimage = $image;
                    }
                }
            }
        }

        // Retrieves any variant images (images associated with only the variant)
        if ($variantid != 0) {

            $variants = $product->variants;

            $variant = $variants[$variantid];
            $images = $variant->images;

            $foundimage = null;
            foreach ($images as $image) {
                if ($image->imageid == $imageid) {
                    $foundimage = $image;
                }
            }
        }

        return $foundimage;
    }

    #TODO: Cache this result

    public function get_all_payments() {
        $all_payments = $this->database->get_all_payments();

        foreach ($all_payments as $payment) {
            $payment->paymentstatename = $this->get_error_message_string($payment->paymentstatename);
        }
        /*
          list ($orders_array, $orders_count) = $this->get_all_orders();

          # Must get all payments from all the orders
          $all_payments = array();
          foreach($orders_array as $order)
          {
          $payment = $order -> payment;

          if ($payment != null)
          {
          #Notes - each order should only have 1 payment!
          #foreach($payments as $payment)
          #{
          $all_payments[] = $payment;
          #}
          }
          } */
        $count = count($all_payments);

        return array($all_payments, $count);
    }

    #TODO: Cache this result

    public function is_used_transactionid($transactionid) {
        list($all_payments, $count) = $this->get_all_payments();
        #var_dump($all_payments);

        $found = false;
        foreach ($all_payments as $payment) {
            #var_dump($payment);
            if ($payment->transactionid == $transactionid) {
                #echo "found";
                $found = true;
            }
        }
        return $found;
    }

    public function get_people_also_bought($productid) {
        $all_products = $this->get_associated_products();
        #var_dump($all_products);
        $a = $all_products[$productid]->associated_products;
        #var_dump($all_products[$productid]);
        #var_dump($a);
        #echo "productid is " . $productid;

        foreach ($a as $b) {
            #echo "found product id is: ";
            #echo $b -> productid;
            #echo $b -> total_ordered;
        }

        return $a;
    }

    function array_clone($array) {
        return array_map(function($element) {
            return ((is_array($element)) ? call_user_func(__FUNCTION__, $element) : ((is_object($element)) ? clone $element : $element
                            )
                    );
        }, $array);
    }

    public function get_associated_products() {
        $model = $this;
        #products are ordered by         
        #return array(array(), 0);

        $options_array = array("include_provisioning_products" => false, "include_provisioning_variants" => false, "remove_out_of_stock_products" => true);
        list($all_products, $products_count) = $this->get_all_products_and_associated($options_array);

        #list($all_products, $products_count) = $this->get_all_products_and_associated($options_array);

        list($orders, $order_count) = $model->get_all_orders();

        #$all_products = clone($all_products);
        # iterat



        foreach ($orders as $order) {
            //echo "order";
            //echo "abc";
            $parcels = $order->parcels;
            //var_dump($parcels);
            foreach ($parcels as $parcel) {
                //echo "parcel";
                $outer_products = $parcel->products;
                $inner_products = $outer_products;


                #$inner_products = array_merge(array(), $outer_products);
                ## Cheap array clone, doesn't preserve IDs
                #$inner_products = $this -> array_clone($outer_products);
                #$products2 = clone $parcel -> products;
                foreach ($outer_products as $outer_product) {
                    $outer_productid = $outer_product->productid;

                    #echo "outer " . $outer_productid . "<br/>";

                    foreach ($inner_products as $inner_product) {
                        $inner_productid = $inner_product->productid;

                        #echo "inner " . $inner_productid . "<br/>";

                        if ($inner_productid == $outer_productid) {
                            #echo "skipping on " . $inner_productid . " = " . $outer_productid . "<br/>";
                            continue;
                        } else {
                            #echo "accepted " . $inner_productid . " = " . $outer_productid . "<br/>";
                            #continue;
                        }
                        # If product gets deleted then we have a problem!
                        if (isset($all_products[$outer_productid])) {
                            #continue;
                            $associated_products = $all_products[$outer_productid]->associated_products;


                            if (isset($associated_products[$inner_productid])) {
                                $assoc_prod_inner = $associated_products[$inner_productid];
                            } else {
                                #$assoc_prod_inner = $inner_product;
                                # We need the full product information (image locations) which aren't in the $order -> product
                                $assoc_prod_inner = $all_products[$inner_productid];
                            }


                            #echo "looking for: " . $inner_productid . "<br/>";
                            #echo "found: " . $assoc_prod_inner -> productid  . "<br/>";
                            //echo "abc";

                            $orderedqty = $assoc_prod_inner->orderedqty;

                            $assoc_prod_inner->total_ordered += $orderedqty;
                            #echo $productid;
                            #echo $orderedqty;
                            #$all_products[$productid]->set_comparator($all_products[$productid]->get_comparator() + $orderedqty);
                            $assoc_prod_inner->set_comparator($orderedqty);

                            $associated_products[$inner_productid] = $assoc_prod_inner;

                            #var_dump($associated_products -> productid);
                            # rekey the array to 0
                            $associated_products = array_values($associated_products);
                            $quick_sort = new \client_code\quick_sort();
                            $associated_products = $quick_sort->sort($associated_products);

                            # Sort here
                            $all_products[$outer_productid]->associated_products = $associated_products;
                        }
                    }
                }
            }
        }

        # rekey the array to 0
        #$all_products = array_values($all_products);
        #$quick_sort = new \client_code\quick_sort();
        #$all_products = $quick_sort->sort($all_products);

        foreach ($all_products as $key => $product) {
            #echo $product->get_comparator();
            #echo $key;
            #echo $product -> id;
            #echo "product id: " . $product->id . "has been ordered " . $product->total_ordered . " times";
            #die();
        }

        return $all_products;
    }

    public function get_popular_products($date_object_from = null, $date_object_to = null) {

        #$date_timestamp_from = $date_object_from -> getTimestamp();
        #$date_timestamp_to = $date_object_to -> getTimestamp();
        $model = $this;
        $date_timestamp_from = 0;
        $date_timestamp_to = 0;
        if ($date_object_from != null) {
            $date_timestamp_from = $date_object_from->getTimestamp();
        }
        if ($date_object_to != null) {
            $date_timestamp_to = $date_object_to->getTimestamp();
        }
        #products are ordered by                

        $options_array = array("include_provisioning_products" => false, "include_provisioning_variants" => false, "remove_out_of_stock_products" => true);
        list($all_products, $products_count) = $this->get_all_products_and_associated($options_array);

        # Already keyed on productid       

        list($orders, $order_count) = $model->get_all_orders();

        if (($date_timestamp_from != null) && ($date_timestamp_to != null)) {
            foreach ($orders as $key => $order) {
                $orderdate_string = $order->orderdate;
                //$orderdate = new \DateTime($orderdate_string);            
                //var_dump($orderdate);

                $utctimezone = new \DateTimeZone("UTC");
                $orderdate = new \DateTime($orderdate_string);
                $orderdate->setTimezone($utctimezone);
                $orderdate_timestamp = $orderdate->getTimestamp();


                if (($orderdate_timestamp >= $date_timestamp_from) && ($orderdate_timestamp <= $date_timestamp_to)) {
                    
                } else {
                    unset($orders[$key]);
                }
            }
        }

        #$all_products = clone($all_products);
        #var_dump($orders);
        foreach ($orders as $order) {
            //echo "order";
            //echo "abc";
            $parcels = $order->parcels;
            //var_dump($parcels);
            foreach ($parcels as $parcel) {


                //echo "parcel";
                $products = $parcel->products;
                foreach ($products as $product) {
                    $productid = $product->productid;
                    //var_dump($productid);
                    # If product gets deleted then we have a problem!
                    if (isset($all_products[$productid])) {
                        //echo "abc";

                        $orderedqty = $product->orderedqty;

                        $all_products[$productid]->total_ordered += $orderedqty;

                        //var_dump($orderedqty);
                        #echo $productid;
                        #echo $orderedqty;
                        #$all_products[$productid]->set_comparator($all_products[$productid]->get_comparator() + $orderedqty);
                        //$all_products[$productid]->set_comparator($orderedqty);
                        $all_products[$productid]->set_comparator($all_products[$productid]->total_ordered);
                    }
                }
            }
        }

        # rekey the array to 0
        $all_products = array_values($all_products);
        $quick_sort = new \client_code\quick_sort();
        $all_products = $quick_sort->sort($all_products);

        foreach ($all_products as $key => $product) {
            #echo $product->get_comparator();
            #echo $key;
            #echo $product -> id;
            #echo "product id: " . $product->id . "has been ordered " . $product->total_ordered . " times";
            #die();
        }

        #var_dump($all_products);

        return $all_products;
    }

    public function get_popular_products_by_amount($date_object_from = null, $date_object_to = null) {

        #$date_timestamp_from = $date_object_from -> getTimestamp();
        #$date_timestamp_to = $date_object_to -> getTimestamp();
        $model = $this;
        $date_timestamp_from = 0;
        $date_timestamp_to = 0;
        if ($date_object_from != null) {
            $date_timestamp_from = $date_object_from->getTimestamp();
        }
        if ($date_object_to != null) {
            $date_timestamp_to = $date_object_to->getTimestamp();
        }
        #products are ordered by                

        $options_array = array("include_provisioning_products" => false, "include_provisioning_variants" => false, "remove_out_of_stock_products" => true);
        list($all_products, $products_count) = $this->get_all_products_and_associated($options_array);

        # Already keyed on productid       

        list($orders, $order_count) = $model->get_all_orders();

        if (($date_timestamp_from != null) && ($date_timestamp_to != null)) {
            foreach ($orders as $key => $order) {
                $orderdate_string = $order->orderdate;
                //$orderdate = new \DateTime($orderdate_string);            
                //var_dump($orderdate);

                $utctimezone = new \DateTimeZone("UTC");
                $orderdate = new \DateTime($orderdate_string);
                $orderdate->setTimezone($utctimezone);
                $orderdate_timestamp = $orderdate->getTimestamp();


                if (($orderdate_timestamp >= $date_timestamp_from) && ($orderdate_timestamp <= $date_timestamp_to)) {
                    
                } else {
                    unset($orders[$key]);
                }
            }
        }

        #$all_products = clone($all_products);
        #var_dump($orders);
        foreach ($orders as $order) {
            //echo "order";
            //echo "abc";
            $parcels = $order->parcels;
            //var_dump($parcels);
            foreach ($parcels as $parcel) {


                //echo "parcel";
                $products = $parcel->products;
                foreach ($products as $product) {
                    $productid = $product->productid;
                    //var_dump($productid);
                    # If product gets deleted then we have a problem!
                    if (isset($all_products[$productid])) {
                        //echo "abc";
                        //$orderedqty = $product->orderedqty;
                        $purchaseprice = $product->purchaseprice;

                        #$all_products[$productid]->total_ordered += $orderedqty;
                        $all_products[$productid]->purchaseprice += $purchaseprice;

                        //var_dump($orderedqty);
                        #echo $productid;
                        #echo $orderedqty;
                        #$all_products[$productid]->set_comparator($all_products[$productid]->get_comparator() + $orderedqty);
                        $all_products[$productid]->set_comparator($all_products[$productid]->purchaseprice);
                    }
                }
            }
        }

        # rekey the array to 0
        $all_products = array_values($all_products);
        $quick_sort = new \client_code\quick_sort();
        $all_products = $quick_sort->sort($all_products);

        foreach ($all_products as $key => $product) {
            #echo $product->get_comparator();
            #echo $key;
            #echo $product -> id;
            #echo "product id: " . $product->id . "has been ordered " . $product->total_ordered . " times";
            #die();
        }

        #var_dump($all_products);

        return $all_products;
    }

    public function get_popular_products_by_category($categoryid, $date_object_from = null, $date_object_to = null) {
        #$date_timestamp_from = $date_object_from -> getTimestamp();
        #$date_timestamp_to = $date_object_to -> getTimestamp();
        $model = $this;
        $date_timestamp_from = 0;
        $date_timestamp_to = 0;
        if ($date_object_from != null) {
            $date_timestamp_from = $date_object_from->getTimestamp();
        }
        if ($date_object_to != null) {
            $date_timestamp_to = $date_object_to->getTimestamp();
        }
        #products are ordered by                
        #$options_array = array("include_provisioning_products" => false, "include_provisioning_variants" => false, "remove_out_of_stock_products" => true);
        #list($all_products, $products_count) = $this->get_all_products_and_associated($options_array);

        list($all_products_old, $products_count) = $model->get_products_in_category_and_children($categoryid);

        $all_products = array();
        # Re-key on productid
        foreach ($all_products_old as $key => $product) {
            $productid = $product->productid;
            $all_products[$productid] = $product;
        }



        list($orders, $order_count) = $model->get_all_orders();

        if (($date_timestamp_from != null) && ($date_timestamp_to != null)) {
            foreach ($orders as $key => $order) {
                $orderdate_string = $order->orderdate;
                //$orderdate = new \DateTime($orderdate_string);            
                //var_dump($orderdate);

                $utctimezone = new \DateTimeZone("UTC");
                $orderdate = new \DateTime($orderdate_string);
                $orderdate->setTimezone($utctimezone);
                $orderdate_timestamp = $orderdate->getTimestamp();


                if (($orderdate_timestamp >= $date_timestamp_from) && ($orderdate_timestamp <= $date_timestamp_to)) {
                    
                } else {
                    unset($orders[$key]);
                }
            }
        }

        #$all_products = clone($all_products);


        foreach ($orders as $order) {
            //echo "order";
            //echo "abc";
            $parcels = $order->parcels;
            //var_dump($parcels);
            foreach ($parcels as $parcel) {
                //echo "parcel";
                $products = $parcel->products;
                foreach ($products as $product) {
                    $productid = $product->productid;
                    # If product gets deleted then we have a problem!
                    if (isset($all_products[$productid])) {
                        //echo "abc";

                        $orderedqty = $product->orderedqty;

                        # are the products in productID listing?
                        $all_products[$productid]->total_ordered += $orderedqty;
                        #echo $productid;
                        #echo $orderedqty;
                        #echo "<br/>";
                        #$all_products[$productid]->set_comparator($all_products[$productid]->get_comparator() + $orderedqty);
                        //$all_products[$productid]->set_comparator($orderedqty);
                        $all_products[$productid]->set_comparator($all_products[$productid]->total_ordered);
                    }
                }
            }
        }

        # rekey the array to 0
        $all_products = array_values($all_products);
        $quick_sort = new \client_code\quick_sort();
        $all_products = $quick_sort->sort($all_products);

        foreach ($all_products as $key => $product) {
            #echo $product->get_comparator();
            #echo $key;
            #echo $product -> id;
            #echo "product id: " . $product->id . "has been ordered " . $product->total_ordered . " times";
            #die();
        }

        return $all_products;
    }

    public function get_popular_products_by_collection($collectionid, $date_timestamp_from = null, $date_timestamp_to = null) {
        $model = $this;

        $date_timestamp_from = 0;
        $date_timestamp_to = 0;
        /*
        if ($date_object_from != null) {
            $date_timestamp_from = $date_object_from->getTimestamp();
        }
        if ($date_object_to != null) {
            $date_timestamp_to = $date_object_to->getTimestamp();
        } */
        #products are ordered by                
        #$options_array = array("include_provisioning_products" => false, "include_provisioning_variants" => false, "remove_out_of_stock_products" => true);
        #list($all_products, $products_count) = $this->get_all_products_and_associated($options_array);

        $collection = $model->get_collection($collectionid);

        # Already keyed on productid       

        $all_products = $collection->products;


        list($orders, $order_count) = $model->get_all_orders();

        if (($date_timestamp_from != null) && ($date_timestamp_to != null)) {
            foreach ($orders as $key => $order) {
                $orderdate_string = $order->orderdate;
                //$orderdate = new \DateTime($orderdate_string);            
                //var_dump($orderdate);

                $utctimezone = new \DateTimeZone("UTC");
                $orderdate = new \DateTime($orderdate_string);
                $orderdate->setTimezone($utctimezone);
                $orderdate_timestamp = $orderdate->getTimestamp();


                if (($orderdate_timestamp >= $date_timestamp_from) && ($orderdate_timestamp <= $date_timestamp_to)) {
                    
                } else {
                    unset($orders[$key]);
                }
            }
        }

        #$all_products = clone($all_products);


        foreach ($orders as $order) {
            //echo "order";
            //echo "abc";
            $parcels = $order->parcels;
            //var_dump($parcels);
            foreach ($parcels as $parcel) {
                //echo "parcel";
                $products = $parcel->products;
                foreach ($products as $product) {
                    $productid = $product->productid;
                    # If product gets deleted then we have a problem!
                    if (isset($all_products[$productid])) {
                        //echo "abc";

                        $orderedqty = $product->orderedqty;

                        $all_products[$productid]->total_ordered += $orderedqty;
                        #echo $productid;
                        #echo $orderedqty;
                        #$all_products[$productid]->set_comparator($all_products[$productid]->get_comparator() + $orderedqty);
                        //$all_products[$productid]->set_comparator($orderedqty);
                        $all_products[$productid]->set_comparator($all_products[$productid]->total_ordered);
                    }
                }
            }
        }

        # rekey the array to 0
        $all_products = array_values($all_products);
        $quick_sort = new \client_code\quick_sort();
        $all_products = $quick_sort->sort($all_products);

        foreach ($all_products as $key => $product) {
            #echo $product->get_comparator();
            #echo $key;
            #echo $product -> id;
            #echo "product id: " . $product->id . "has been ordered " . $product->total_ordered . " times";
            #die();
        }

        return $all_products;
    }

    public function &database_change_stocklevel($productid, $variantid, $stocklevel) {
        $result = $this->database->change_stocklevel($productid, $variantid, $stocklevel);
        $this->increment_version();
        return $result;
    }

    public function increment_version() {
        $client_root = $GLOBALS["sb_client_site_path"];

# the configuration root never changes.
        $configuration_root = $client_root . '/configuration/';

# Open database configuration
        $version_config_filename = $client_root . "/www/version.txt";

        $version_config = new \shared_code\configuration($version_config_filename);
        $site_version = $version_config->get_setting("site_version");
        $site_version++;

        if ($site_version > 65535) {
            $site_version = 1;
        }

        $handle = fopen($version_config_filename, "w");

        if ($handle != null) {
            fwrite($handle, "site_version=" . $site_version . "\n");
        }

        $this->site_version = $site_version;

        $this->memcached_store->site_version = $site_version;

        #$variants = $this->database->get_all_variants_and_specials();
        #$this -> store_special_expiry_times($variants);
        #die();
    }

    public function add_productoption($productid) {
        $result = $this->database->add_productoption($productid);
        $this->increment_version();
        return $result;
    }

    public function delete_productoption($productid, $optionid) {
        $result = $this->database->delete_productoption($productid, $optionid);
        $this->increment_version();
        return $result;
    }

    public function update_productoption($productid, $optionid, $name) {
        $result = $this->database->update_productoption($productid, $optionid, $name);
        $this->increment_version();
        return $result;
    }

    public function update_variantoption($productid, $variantid, $optionid, $value) {
        $result = $this->database->update_variantoption($productid, $variantid, $optionid, $value);
        $this->increment_version();
        return $result;
    }

    public function create_variantoption($productid, $variantid, $optionid, $value) {
        $result = $this->database->create_variantoption($productid, $variantid, $optionid, $value);
        $this->increment_version();
        return $result;
    }

    public function delete_variantoption($productid, $variantid, $optionid) {
        $result = $this->database->delete_variantoption($productid, $variantid, $optionid);
        $this->increment_version();
        return $result;
    }

    public function add_productmetadata($productid, $name) {
        $result = $this->database->add_productmetadata($productid, $name);
        $this->increment_version();
        return $result;
    }

    public function delete_productmetadata($productid, $metadataid) {
        $result = $this->database->delete_productmetadata($productid, $metadataid);
        $this->increment_version();
        return $result;
    }

    public function update_productmetadata($productid, $metadataid, $name, $value) {
        $result = $this->database->update_productmetadata($productid, $metadataid, $name, $value);
        $this->increment_version();
        return $result;
    }

    public function update_variantmetadata($productid, $variantid, $metadataid, $value) {
        $result = $this->database->update_variantmetadata($productid, $variantid, $metadataid, $value);
        $this->increment_version();
        return $result;
    }

    public function create_variantmetadata($productid, $variantid, $metadataid, $value) {
        $result = $this->database->create_variantmetadata($productid, $variantid, $metadataid, $value);
        $this->increment_version();
        return $result;
    }

    public function delete_variantmetadata($productid, $variantid, $metadataid) {
        $result = $this->database->delete_variantmetadata($productid, $variantid, $metadataid);
        $this->increment_version();
        return $result;
    }

    # $list is array of (optionid => optionvalue)
    # This function is complicated :~)
    # Given a list of selected options,
    # It will return the list of "next" options to be displayed (option ID + min value (1+))
    # 
    # It can't be performed on the database due to the variable number of joins etc
    # which requires a recurring function
    # In short
    /*
     * Consider scenario with option1 Name="Size" and Value="5"  AND option2 Name="Colour" and Value="Red"

      Option ID=1                    Option ID=2               Option ID=3
      NAME VALUE VARIANTID           NAME VALUE VARIANTID
      SIZE  5     1                COLOUR  Red      1
      SIZE  5     2                COLOUR  Green    2
      SIZE  6     3                COLOUR  Blue     3


      SIZE = 5                     Colour = Red
      SIZE  5     1                COLOUR  Red      1
      SIZE  5     2

      We need to find the Variants that have these option names + values,
      this requires a recurring loop (something SQL can't do) over the
      variants and their variantoptions and
      - make a simple array of matching variants by variantid array(1,2,3,4);

      Essentially if you were to do this using DB:
      SELECT * FROM VARIANTOPTIONS WHERE NAME="Size" AND "VALUE"=5          A
      SELECT * FROM VARIANTOPTIONS WHERE NAME="Colour" AND VALUE="Green"    B
      SELECT * FROM VARIANTOPTIONS WHERE NAME="Width" AND VALUE=52          C

      JOIN A to B to C on variantID

      NAME VALUE VARIANTID   NAME   VALUE VARIANTID
      SIZE 5     1           COLOUR Red   1


      Now we have this, we just need to join with the variantoptions at option 4 (for instance)
      by finding out which variantoptions in the productoptions list have not been used (no selected value passed in)
      This will give us available values for option 4 that match criteria

      # We have selected options
      option1=1 name="Size" value="Red"

      FILTER using options passed in (optionid, value) on all variantoptions to get the list of variants that match these options.
      # For databases this is difficult because - One predicates (requires) the other - an AND statement may also work but has to
      # Be crafted carefully
      1.) We find all variantoptions with optionid=1 and value=red this gives us variantids

      JOIN the variantids with UNIQUE variantoptions with id=2 (next highest option ID) and return
      2.) We then find all variantoptions for optionid=2 and remove anything NOT in 1.) and return them to the user.


      1.) We find all variantoptions with (optionid=1 and value=red) AND (optionid=2 AND value="12") this gives us variantids

      2.) We then find all variantoptions for optionid=3 and remove anything NOT in 1.) and return them to the user.


     */

    public function get_first_option($product) {
        #$options_by_optionid = array();
        # We get all variantoptions into a single list keyed on optionID
        # SO if we have 5 variants and 5 optionIDs, we have 25 variantoptions;
        # We're basically changing it from being variant -> option to  1,2,3,4,5
        #     to 1,1,1,1,1  2,2,2,2,2 3,3,3,3,3  4,4,4,4,4 5,5,5,5,5
        $productoptions = $product->options;
        $variants = $product->variants;

        $matched_variantoptions = array();

        //We need to  be sure somehow that we're  getting the lowest keyed option
        foreach ($productoptions as $key => $productoption) {




#            $matched_variantoptions = array();

            $current_optionid = $productoption->optionid;
            #$current_optionid++;
            foreach ($variants as $variant) {
                $variantoptions = $variant->options;
                # See if variantoption[1] exists
                if (isset($variantoptions[$current_optionid])) {
                    $variant_option = $variantoptions[$current_optionid];
                    #$new_value = $variant_option->value;
                    # Use the String "key"  as value to avoid searching the array blah blah
                    #$values[$new_value] = $variant_option;
                    $matched_variantoptions[] = $variant_option;
                }
            }
            #var_dump($values);
            #echo "<br/>";
            #echo "<br/>";
            #foreach ($values as $value)
            #{
            #echo $value -> value;
            #echo $value -> variantid;
            #echo $value -> value;
            #}
            #echo "<br/>";
            #echo "<br/>";
            #$options_by_optionid[$current_optionid] = $matched_variantoptions;

            break;
        }


        # If there are no filter
        #if ($list == array())
        #{
        return $matched_variantoptions;
        #}
    }

    public function get_product_options_by_optionid($product, $list) {
        $options_by_optionid = array();
        /* For each product(OPTION (by id)) 
         *  Get the value of the variant(Option with matching name)
         *    and place into a 2D array of format:
         *     array(optionid => array2);
         *              array2(variantoption (for variantID 1), variantoption  (for variantID 2), value  (for variantID 3));
         */

        # We are changing the $product -> variant -> options  (options by variantID)
        #   a new array keyed on optionID -> options (options by OptionID)
        #
        # Final result is like so:
        #
        #  OptionID   Name   Value   VariantID
        # $options_by_optionid[1] =
        #  1          Colour Red       1
        #  1          Colour Red       2
        #  1          Colour Blue      3
        #
        # $options_by_optionid[2] =
        #  2          Size   12        1
        #  2          Size   14        2
        #  2          Size   16        3
        #
        # $options_by_optionid[3] =
        #  3          Size   52        1
        #  3          Size   54        2
        #  3          Size   52        3
        $productoptions = $product->options;
        $variants = $product->variants;
        foreach ($productoptions as $productoption) {

            #Ignore options with blank name
            if ($productoption->name == "") {
                continue;
            }


            $matched_variantoptions = array();

            $current_optionid = $productoption->optionid;
            #$current_optionid++;
            foreach ($variants as $variant) {
                $variantoptions = $variant->options;
                # See if variantoption[1] exists
                if (isset($variantoptions[$current_optionid])) {
                    $variant_option = $variantoptions[$current_optionid];
                    #$new_value = $variant_option->value;
                    # Use the String "key"  as value to avoid searching the array blah blah
                    #$values[$new_value] = $variant_option;
                    $matched_variantoptions[] = $variant_option;
                }
            }
            #var_dump($values);
            #echo "<br/>";
            #echo "<br/>";
            #foreach ($values as $value)
            #{
            #echo $value -> value;
            #echo $value -> variantid;
            #echo $value -> value;
            #}
            #echo "<br/>";
            #echo "<br/>";
            $options_by_optionid[$current_optionid] = $matched_variantoptions;

            /*
              # If there are no filter
              if ($list == array()) {
              #echo "ret";
              return array($current_optionid, $matched_variantoptions);
              }
             *
             */
        }

        return $options_by_optionid;
    }

    public function get_first_product_option_by_optionid($product, $list) {

        $productoptions = $product->options;
        $variants = $product->variants;
        $matched_variantoptions = array();
        $current_optionid = 0;
        foreach ($productoptions as $optionid => $productoption) {

            $current_optionid = $productoption->optionid;
            foreach ($variants as $variant) {
                $variantoptions = $variant->options;
                if (isset($variantoptions[$current_optionid])) {
                    $variant_option = $variantoptions[$current_optionid];
                    $matched_variantoptions[] = $variant_option;
                }
            }

            break;
        }

        return array($current_optionid, $matched_variantoptions);
    }

    # The list provided is a list of selected elements

    public function get_next_product_option_by_optionid_filtered($product, $list) {


        if ($list != array()) {
            $options_by_optionid = $this->get_product_options_by_optionid($product, $list);
        } else {
            #list($current_optionid, $matched_variantoptions) = $this -> get_first_product_option_by_optionid($product, $list);
            #return array ($current_optionid, $matched_variantoptions);
        }

        #$options_by_optionid = $this->get_product_options_by_optionid($product, $list);
        #var_dump($options_by_optionid);
        # Filter the options based on selected value in the list
        #
        # We want to get only the variantids that match these options
        #
        # This is like a select and join in SQL
        # 
        # Essentially, this function gets variant IDs that match option values
        # for options (1,2,3) 
        # and this tells us which variants options are meant to be placed or
        # kept in the next option (4).
        # Considering the previous table, with  Selected values of
        # 1.) Value="Red"
        #             VariantID 1 & 2 = Red matches 
        #             VariantID 1 and 2 are saved as variants
        #                   whose (future) options (optionID =2) we want to keep
        #             
        #
        # 2.) Value="Red" and Size="12"
        #             VariantID 1 & 2 = Red matches
        #             VariantID 1 = Size 12 matches
        #              Variant 1,2 are instersected with 1
        #
        #             Therefore VariantID 1 is saved as variants
        #                   whose (future) options (optionID =3) we want to keep
        #
        # 3.) Value="Red" and Size="12" and Width=52
        #             VariantID 1 & 2 = Red matches
        #             VariantID 1 = Size 12 matches
        #             VariantID 1 & 3 = 52 matches
        #              Variant 1,2 are instersected with 1
        #              VariantID 1 is intersected with 1 & 3
        #
        #             Therefore VariantID 1 is saved as variants
        #                   whose (future) options (optionID =4) we want to keep
        #

        

        $matched_so_far = array();
        $first_run = true;
        foreach ($list as $optionid => $value) {
            $matched_variantids = array();
            if (isset($options_by_optionid[$optionid])) {
                $options_to_filter = $options_by_optionid[$optionid];
                #var_dump($options_to_filter);
                $no_variantids_matched = true;
                foreach ($options_to_filter as $option) {

                    #var_dump($option);
                    #var_dump($option -> value);
                    if ($option->value == $value) {
                        $matched_variantids[$option->variantid] = $option->variantid;
                        $no_variantids_matched = false;
                    }
                }

                if ($no_variantids_matched == true) {
                    #return array ($optionid, $matched_variantoptions);
                    #return array(0, array());
                    # If no variantids match, we should NOT filter 
                    #continue;
                    #break;
                }

                #if ($no_variantids_matched == true) {
                #throw new \Exception();
                #}
                # No variants were matched.... this case occurs when a user 
                # Goes back in their option selection and changes Option 1 and
                # the old Option 2 is re-posted, in this case, 
                #var_dump($matched_variantids);


                if ($first_run == true) {
                    $matched_so_far = $matched_variantids;
                    #var_dump($matched_so_far);
                } else {
                    # We do a merge using the php array merge function of the FIRST to the SECOND
                    $matched_so_far = array_intersect($matched_so_far, $matched_variantids);
                    #var_dump($matched_so_far);
                    #var_dump($matched_variantids);
                }

                $first_run = false;
            }
            # On the first run we don't do the array intersect, we jus set
        }

        # Makes a list contain only the optionIDs that have not been
        #   selected from $list 
        #
        #   Essentially, it's the DIFFERENCE of $product -> option (IDs) and
        #     $list (Keys - optionIDs)
        $filtered_optionids = array();
        $productoptions = $product->options;
        foreach ($productoptions as $productoption) {
            $optionid = $productoption->optionid;

            #Ignore options with blank name
            if ($productoption->name == "") {
                continue;
            }

            if (isset($list[$optionid])) {
                continue;
            } else {
                $filtered_optionids[$optionid] = $optionid;
            }
        }

        # Filter to get the lowest optionid then... reindex? 
        asort($filtered_optionids);

        # Re-key to start on 0
        $filtered_optionids = array_keys($filtered_optionids);


        # Once we find the next lowest option ID (filtered_optionids[0])
        # We iterate through the variants to find that option (4 for instance)
        # and then filter according to our 
        #   "matched_so_far" intersections, which tells us the valid variantIDs
        #   whose options we can include in our returned results
        #
        # Another way to do this would be to just get
        #   the product -> matched variant(s) -> options[4] and place into array

        $grouped_variant_option_values_by_optionid = array();
        if (isset($filtered_optionids[0])) {
            #var_dump($filtered_optionids);
            #if ($filtered_optionids != array())
            # Option ID to return option values (of variants) for
            $next_option = $filtered_optionids[0];

            if (isset($options_by_optionid[$next_option])) {
                # Get the variantoptions for that option ID (for all variants NOT filtered)
                $grouped_variant_option_values_by_optionid = $options_by_optionid[$next_option];

                # Using matched variant IDs, filter the above list           
                #$matched_variantids is a simple array of variant IDs
                # Remove any variants not matched by our variantoptions for option 4
                foreach ($grouped_variant_option_values_by_optionid as $key => $group_option) {
                    $variantid = $group_option->variantid;
                    if (in_array($variantid, $matched_so_far)) {
                        #var_dump($variantid);
                        continue;
                    } else {
                        unset($grouped_variant_option_values_by_optionid[$key]);
                    }
                }
            }
        }


        # This just gets the "next" or .. least valued not selected ID
        $next_option = -1;
        if (isset($filtered_optionids[0])) {
            $next_option = $filtered_optionids[0];
        }

        return array($next_option, $grouped_variant_option_values_by_optionid);
    }

    public function is_trial() {
        $is_trial = true;
        $plan_name = $this->config_get_setting("plan_name");
        if ($plan_name != "Trial") {
            $is_trial = false;
        }
        return $is_trial;
    }

    public function is_overdue_payment() {
        return false;
    }

    public function file_config_get_setting($setting_name) {
        $client_root = $GLOBALS["sb_client_site_path"];
        $global_config_filename = $client_root . "/configuration/client_config.txt";

        $global_config = new \shared_code\configuration($global_config_filename);

        $setting = $global_config->get_setting($setting_name);

        return $setting;
    }

    public function file_config_get_config() {
        $client_root = $GLOBALS["sb_client_site_path"];
        $global_config_filename = $client_root . "/configuration/client_config.txt";

        $global_config = new \shared_code\configuration($global_config_filename);

        return $global_config;
    }

    public function file_config_get_settings() {
        $client_root = $GLOBALS["sb_client_site_path"];
        $global_config_filename = $client_root . "/configuration/client_config.txt";

        $global_config = new \shared_code\configuration($global_config_filename);

        $settings = $global_config->get_settings();

        return $settings;
    }

    public function file_config_get_setting_boolean_as_string($setting_name) {
        $client_root = $GLOBALS["sb_client_site_path"];
        $global_config_filename = $client_root . "/configuration/client_config.txt";

        $global_config = new \shared_code\configuration($global_config_filename);

        $setting = $global_config->get_setting_boolean_as_string($setting_name);

        return $setting;
    }

    public function file_config_set_setting($setting_name, $setting_value) {
        $client_root = $GLOBALS["sb_client_site_path"];
        $global_config_filename = $client_root . "/configuration/client_config.txt";

        $global_config = new \shared_code\configuration($global_config_filename);

        $setting = $global_config->set_setting($setting_name, $setting_value);

        $global_config = $global_config->save();

        return $setting;
    }

    // Get the settings of a particular seller.
    public function config_get_setting($setting_name) {

        /*
          $settings = $this->database->get_all_site_config_vars();



          $setting = null;
          if (isset($settings[$setting_name])) {
          $setting = $settings[$setting_name];
          }
         */

        $config = $this->config_get_site_config();

        $setting = $config->get_setting($setting_name);

        return $setting;
    }

    // Get the settings of a particular seller.
    public function config_get_setting_boolean_as_string($setting_name) {

        /*
          $settings = $this->database->get_all_site_config_vars();



          $setting = null;
          if (isset($settings[$setting_name])) {
          $setting = $settings[$setting_name];
          }
         */

        $config = $this->config_get_site_config();

        $setting = $config->get_setting_boolean_as_string($setting_name);

        return $setting;
    }

    // Get the settings of a particular seller.
    public function config_get_settings() {

        $config = $this->config_get_site_config();

        $settings = $config->get_settings();

        //var_dump($settings_simple_array);
        #public function set_settings(array $fields) {

        $filename = null;

        //$global_config = new \shared_code\configuration($filename);
        //$setting = $global_config->get_setting($setting_name);


        return $settings;
    }

    // Get the settings of a particular seller.
    public function config_get_site_config() {

        $global_config = $this->memcached_get_site_config();

        /*
          $settings = $this->database->get_all_site_config_vars();

          #public function set_settings(array $fields) {

          $filename = null;

          $global_config = new \shared_code\configuration($filename);
         * 
          if ($settings != null) {
          $global_config->set_settings_from_db_array($settings);
          }
         */


        //var_dump($settings_simple_array);


        return $global_config;
    }

    public function memcached_get_site_config() {

        # Check if we have it in memory before going to memcache
        # Do not use this in admin_code as it will produce stale results
        #if ($this->categories != null) {
        #return $this->categories;
        #}

        $global_config = null;
        $memcached_store = $this->memcached_store;

        $searchkeyvalues = array("siteconfig" => "siteconfig");

        $found = false;
        if ($this->memcached_store != null) {
            try {
                $global_config = $this->memcached_store->get_search($searchkeyvalues);

                $found = true;
            } catch (\Exception $e) {
                $found = false;
            }

            if ($found == false) {

                $settings = $this->database->get_all_site_config_vars();

                #public function set_settings(array $fields) {

                $filename = null;

                $global_config = new \shared_code\configuration($filename);

                if ($settings != null) {
                    $global_config->set_settings_from_db_array($settings);
                }

                try {
                    list($stored, $memcached_error_messages) = $memcached_store->store_search($searchkeyvalues, $global_config, $this->database_expiry);
                } catch (\Exception $e) {
                    #echo $e->getMessage();
                    #   echo "Unable to store";
                }
            }
        } else {
            echo "memcached is off";
            $settings = $this->database->get_all_site_config_vars();

            #public function set_settings(array $fields) {

            $filename = null;

            $global_config = new \shared_code\configuration($filename);

            if ($settings != null) {
                $global_config->set_settings_from_db_array($settings);
            }
        }

        $this->global_config = $global_config;

        //$categories_count = count($categories);

        return $global_config;
    }

    // Get the settings of a particular seller.
    public function config_set_setting($setting_name, $setting_value) {

        //$userid = $this -> session_get_userid();
        //$userid = 0;

        $result = $this->database->delete_site_config_var($setting_name);
        $result = $this->database->create_site_config_var($setting_name, $setting_value);

        # Don't do this because it results in flush in clientindex code.
        $this->increment_version();
        //return $setting;
    }

    public function datetime_convert_a_date_string_from_users_timezone_to_utc($start = "now") {
        $timezone_string = $this->config_get_setting("timezone");
        #var_dump($timezone_string);
        $usertimezone = new \DateTimeZone($timezone_string);

        $utc_timezone_string = "UTC";
        $utctimezone = new \DateTimeZone($utc_timezone_string);

        $start_date = new \DateTime($start, $usertimezone);
        $start_date->setTimezone($utctimezone);
        $start = $start_date->format('Y-m-d H:i:s');
        return $start;
    }

    public function datetime_convert_a_date_string_from_utc_timezone_to_users_timezone($start = "now") {
        $timezone_string = $this->config_get_setting("timezone");
        #var_dump($timezone_string);
        $usertimezone = new \DateTimeZone($timezone_string);

        $utc_timezone_string = "UTC";
        $utctimezone = new \DateTimeZone($utc_timezone_string);

        $start_date = new \DateTime($start, $utctimezone);
        $start_date->setTimezone($usertimezone);
        $start = $start_date->format('Y-m-d H:i:s');
        return $start;
    }

    public function datetime_convert_a_date_string_from_utc_timezone_to_users_timezone_and_return_as_timestamp($start = "now") {
        $timezone_string = $this->config_get_setting("timezone");
        #var_dump($timezone_string);
        $usertimezone = new \DateTimeZone($timezone_string);

        $utc_timezone_string = "UTC";
        $utctimezone = new \DateTimeZone($utc_timezone_string);

        $start_date = new \DateTime($start, $utctimezone);
        $start_date->setTimezone($usertimezone);
        $start = $start_date->getTimestamp();
        return $start;
    }

    // Returns the current date in UTC
    public function datetime_convert_date_string_from_machine_timezone_into_utc_datetime_text($date_string = "now") {
        #TODO: Ideally convert from machine timezone to UTC
        $utctimezone = new \DateTimeZone("UTC");

        $start_date = new \DateTime($date_string);
        $start_date->setTimezone($utctimezone);
        $start_date = $start_date->format("Y-m-d h:i:s");

        return $start_date;
    }

    public function datetime_convert_date_string_from_machine_timezone_into_utc_datetime_timestamp($date_string = "now") {
        #TODO: Ideally convert from machine timezone to UTC
        $utctimezone = new \DateTimeZone("UTC");
        $start_date = new \DateTime($date_string);
        $start_date->setTimezone($utctimezone);
        $start_date = $start_date->getTimestamp();

        return $start_date;
    }

    public function datetime_convert_date_string_from_machine_timezone_into_utc_datetime_object($date_string = "now") {
        #TODO: Ideally convert from machine timezone to UTC
        $utctimezone = new \DateTimeZone("UTC");
        $start_date = new \DateTime($date_string);
        $start_date->setTimezone($utctimezone);
        #$start_date = $start_date->getTimestamp();

        return $start_date;
    }

    /*
      // Get the settings of a particular seller.
      public function config_set_setting($setting_name, $setting_value) {

      //$userid = $this -> session_get_userid();
      //$userid = 0;

      $result = $this->database->delete_site_config_var($setting_name);
      $result = $this->database->create_site_config_var($setting_name, $setting_value);

      $this->increment_version();
      //return $setting;
      } */

    public function database_create_paypal_ipn($ipn_text) {
        $provider = "Paypal";
        $this->database->create_ipn($ipn_text, $provider);
    }

    public function database_create_ipn($ipn_text, $provider) {
        $this->database->create_ipn($ipn_text, $provider);
    }

    public function database_get_all_ipns() {
        $this->database->get_all_ipns();
    }

    public function database_get_all_ipns_by_provider($provider) {
        $this->database->get_all_ipns_by_provider($provider);
    }

    public function get_all_currencies() {


        $currencies = array();
        $memcached_store = $this->memcached_store;


        $searchkeyvalues = array("currencies" => "currencies");

        $found = false;
        try {
            $currencies = $this->memcached_store->get_search($searchkeyvalues);

            $found = true;
        } catch (\Exception $e) {
            $found = false;
        }

        if ($found == false) {
            $currencies = $this->database->get_all_currencies();


            try {
                #$this->database_expiry
                # Expire currency data after 1 hour for technical reasons.
                # 30 minutes in seconds = 30 * 60 seconds
                $currency_data_expiry = 30 * 60;
                list($stored, $memcached_error_messages) = $memcached_store->store_search($searchkeyvalues, $currencies, $currency_data_expiry);
            } catch (\Exception $e) {
                #echo $e->getMessage();
                #   echo "Unable to store";
            }
        }

        $currencies_count = count($currencies);


        return array($currencies, $currencies_count);
    }

    public function create_order_in_database() {
        $model = $this;
        $cart = $model->get_cart();
        $error_messages = array();
        $order_created = false;
        try {
            #$userid = $model->session_get_userid();
            #$client_site_id = $model->config_get_setting("site_id");
            list($order, $order_create_error_messages) = $model->create_order_object();

            $error_messages = array_merge($error_messages, $order_create_error_messages);

            #if ($order === null) {
            #$error_messages["order"] = $error_message;
            #}

            if ($order !== null) {
                #var_dump($order);
                # the id is the USERID not the client site ID!!
                $userid = null;
                $user = $model->session_get_user();
                if ($user != null) {
                    $userid = $user->userid;
                }

                list($orderid, $start) = $model->database_create_order($userid, $order);
                $order->orderid = $orderid;

                #echo "order id is " . $orderid;
                #if ($order !== null) {
                $order_created = true;

                $model->cart->order = $order;
                #}
            }
        } catch (\Exception $e) {
            $order_created = false;
            #echo "CAUGHT THE EXCEPTION";
            /*
              $error_message = "<br/>";
              $error_message .= "Error creating order in database, no funds have been withdrawn";
              $error_message .= "<br/>";
              $error_message .= $e->getMessage();
             * 
             */
            #throw new \Exception($error_message);
            $error_messages[] = "<br/>Error creating order in database, no funds have been withdrawn<br/>";
            $error_messages[] = $e->getMessage();
        }

        return array($order_created, $error_messages);
    }

    # TODO: Store in memcached

    public function get_all_product_taxes() {
        $product_taxes = $this->database->get_all_product_taxes();

        return $product_taxes;
    }

    public function get_product_taxes($productid) {
        $product_taxes = $this->get_all_product_taxes();
        $found_rules = array();
        foreach ($product_taxes as $tax_item) {
            if ($tax_item->productid == $productid) {
                $found_rules[] = $tax_item;
            }
        }

        return $found_rules;
    }

    public function delete_stock_holds() {
        $session_id = session_id();
        $this->database->delete_stock_holds($session_id);
    }

    public function create_stock_hold($productid, $variantid, $number_held) {
        $session_id = session_id();
        $expiry = 15 * 60;
        $this->database->create_stock_hold($session_id, $productid, $variantid, $number_held, $expiry);
    }

    public function update_stock_hold_sessionids($old_sessionid, $new_sessionid) {
        $this->database->update_stock_hold_sessionids($old_sessionid, $new_sessionid);
    }

    public function hold_stock() {
        $model = $this;
        $cart = $model->cart;
        $model->delete_stock_holds();

        $parcels = $cart->parcels;
        foreach ($parcels as $parcel) {
            $products = $parcel->products;
            foreach ($products as $product) {
                $productid = $product->productid;
                $variants = $product->variants;
                #echo count($variants);
                foreach ($variants as $variant) {
                    $variantid = $variant->variantid;
                    $number_held = $variant->orderedqty;
                    $this->create_stock_hold($productid, $variantid, $number_held);
                }
            }
        }
    }

    public function delete_old_stock_holds($stock_hold_database_expiry) {
        return $this->database->delete_old_stock_holds($stock_hold_database_expiry);
    }

    public function get_all_stock_holds() {
        $stock_holds = array();
        $memcached_store = $this->memcached_store;


        $searchkeyvalues = array("stock_holds" => "stock_holds");

        $found = false;
        try {
            $stock_holds = $this->memcached_store->get_search($searchkeyvalues);

            $found = true;
        } catch (\Exception $e) {
            $found = false;
        }

        if ($found == false) {
            $stock_hold_database_expiry = 15 * 60;
            // Call the delete code once per minute effectively when the 
            $this->delete_old_stock_holds($stock_hold_database_expiry);
            $stock_holds = $this->database->get_all_stock_holds();


            try {
                #$this->database_expiry
                # Expire currency data after 1 hour for technical reasons.
                # 30 minutes in seconds = 30 * 60 seconds
                $stock_hold_data_expiry = 1 * 60;
                list($stored, $memcached_error_messages) = $memcached_store->store_search($searchkeyvalues, $stock_holds, $stock_hold_data_expiry);
            } catch (\Exception $e) {
                #echo $e->getMessage();
                #   echo "Unable to store";
            }
        }

        $stock_holds_count = count($stock_holds);


        return array($stock_holds, $stock_holds_count);
    }

    # Get all variables that match a particular key prefix and put them into a new array

    public function get_variables_from_site_config($search_key, $strip_keyword = false) {
        $settings = $this->config_get_settings();

        $parsed_array = array();

        foreach ($settings as $key => $value) {
            if (strpos($key, $search_key) === 0) {
                $new_key = $key;

                if ($strip_keyword == true) {
                    $matches = array();
                    $subject = $key;
                    $pattern = "/^" . $search_key . "(\w+)/";
                    preg_match($pattern, $subject, $matches);

                    $new_key = $matches[1];
                }



                $parsed_array[$new_key] = $value;
            }
        }


        return $parsed_array;
    }

    // Comments are not organised the same way as categories....
    // Where categories have one clearly defined ROOT category "HOME" with parentid 0
    // Comments may have several ROOT comments with parentid = 0
    public function get_comments_and_children($productid) {
        # Get all comments by $productid
        #list($allcomments, $allcomments_count) = $this->database->get_all_comments_by_productid($productid);
        //$allcomments = $this->database->get_all_comments_by_productid($productid);
        list($allcomments, $count) = $this->get_all_comments_by_productid($productid);

        # Find the root category
        //$parent_category = $this->get_comment($commentid);

        $root_comments = array();

        // Find the root comments
        foreach ($allcomments as $comment) {

            if ($comment->parentid == 0) {
                $root_comments[] = $comment;
            }
        }

        foreach ($root_comments as $comment) {
            # Using the root category - build a tree instead of an array
            $comment = $this->get_comment_children_of_parent_to_leaf($productid, $comment);
        }

        return $root_comments;
    }

    public function get_comment_children_of_parent_to_leaf($productid, $comment) {
        # Get the category     
        if ($comment != null) {
            $commentid = $comment->commentid;
            #TODO: refactor out the next line
            #$children = array();
            $children = $this->get_comment_children($productid, $commentid, 0);
            $comment->set_children($children);


            $this->iterative_find_comment_children($productid, $comment, 0);
        }

        return $comment;
    }

    public function iterative_find_comment_children($productid, $root_comment, $depth) {
        if ($root_comment == null) {
            return;
        }
        $depth = $depth + 1;

        $commentid = $root_comment->commentid;
        #TODO: remove this next line / refactor
        $children = $this->get_comment_children($productid, $commentid, $depth);
        $root_comment->set_children($children);

        if ($children != array()) {
            foreach ($children as $child) {
                $this->iterative_find_comment_children($productid, $child, $depth);
            }
        }
    }

    public function &get_comment_children($productid, $commentid, $depth) {
        $children = array();
        if ($depth > 5) {

            return $children;
        }
        list($comments_in_array, $comment_count) = $this->get_all_comments_by_productid($productid);

        foreach ($comments_in_array as $cid => $comment) {
            if ($comment->parentid == $commentid) {
                $children[$cid] = $comment;
            }
        }

        return ($children);
    }

    public function get_all_comments() {


        $comments = $this->database->get_all_comments();



        $comments_count = count($comments);

        return array($comments, $comments_count);
    }

    public function get_all_comments_by_productid($productid) {


        //$allcomments = $this->database->get_all_comments_by_productid($productid);

        list($allcomments, $all_comments_count) = $this->get_all_comments();

        $foundcomments = array();
        foreach ($allcomments as $key => $comment) {
            if ($comment->productid == $productid) {
                $foundcomments[] = $comment;
            }
        }

        $found_comments_count = count($foundcomments);

        return array($foundcomments, $found_comments_count);
    }

    public function get_root_comments($allcomments, $productid) {


        $root_comments = array();

        // Find the root comments
        foreach ($allcomments as $comment) {

            if ($comment->parentid == 0) {
                $root_comments[] = $comment;
            }
        }
        return $root_comments;
    }

    // Do we want to allow all comment trees to be expanded or just one?
    public function build_comment_tree($productid, $active_commentid) {
        $model = $this;

        list($allcomments, $allcomments_count) = $this->get_all_comments_by_productid($productid);

        # Find the root category
        //$parent_category = $this->get_comment($commentid);

        $root_comments = $this->get_root_comments($allcomments, $productid);

        $stored_comments = array();

        //get_root_category equivalent
        foreach ($root_comments as $root_comment) {

            /* Gets the category and its children in a tree */
            $comment_list = $model->get_comment_and_parents_to_root($productid, $active_commentid);
            # Using the root category - build a tree instead of an array
            $root_comment = $this->get_comment_children_of_parent_to_leaf($productid, $root_comment);

            $keys = $this->get_comment_keys($comment_list);



            $root_children = null;
            if ($root_comment != null) {

                # If categoryid is 0, root is active category 
                if ($active_commentid == -1) {
                    $root_comment->active = true;
                }

                $stored_comments = $this->store_comment($stored_comments, $root_comment, 0);

                $navigation_root_commentid = $root_comment->id;

                $root_children = $root_comment->get_children();

                foreach ($root_children as $root_child) {
                    $stored_comments = $this->iterative_store_comment_children($active_commentid, $stored_comments, $root_child, 1, 5, $keys);
                }
            }
        }





        return $stored_comments;
    }

    function store_comment($stored_categories, $category, $depth) {
        $category->set_depth = $depth;
        $stored_categories[] = $category;

        return $stored_categories;
    }

    #$category is the current category node visited

    function iterative_store_comment_children($active_categoryid, $stored_categories, $category, $depth, $depthmax, $keys) {

        global $model;

        if ($category == null) {
            return $stored_categories;
        }

        if ($depth >= $depthmax) {
            return $stored_categories;
        }

        if ($active_categoryid == $category->id) {
            $category->active = true;
        }

        $stored_categories = $this->store_comment($stored_categories, $category, $depth);


        $categoryid = $category->id;
        $category->depth = $depth;

        $category_children = $category->get_children();

        //list ($products_array, $products_count) = $this->get_products_in_category_and_children($categoryid);
        //$category->count = $products_count;
        //list ($products_array, $products_count) = $this->get_products_in_category_and_children($categoryid);

        $depth++;
        foreach ($category_children as $category) {

            // Do we want to print the children?
            $print_childs_children = true;
            /*
             * WITH THIS COMMENTED, ALL BRANCHES WILL BE EXPANDED
              if (($depth > 1) && (!in_array($categoryid, $keys))) {
              $print_childs_children = false;
              } */


            if ($print_childs_children == true) {
                $stored_categories = $this->iterative_store_comment_children($active_categoryid, $stored_categories, $category, $depth, $depthmax, $keys);
            }
        }

        return $stored_categories;
    }

    function get_comment_keys($category_list) {
        $keys = array();
        if ($category_list != null) {
            foreach ($category_list as $category) {
                $keys[] = $category->id;
            }
        }

        return $keys;
    }

    public function get_comment_and_parents_to_root($productid, $parentid) {
        $category_list = array();

        list($categories, $category_count) = $this->get_all_comments_by_productid($productid);

        $root_category = $this->get_root_comments($categories, $productid);

        foreach ($root_category as $root_categories) {
            if ($root_category == null) {
                //   return $category_list;
                continue;
            }

            $categoryid = $parentid;

            if (!isset($categoryid)) {
                $category_list[] = $root_category;
            }

            if (isset($categoryid)) {
                # Get the category and parents up 5 levels.
                for ($i = 0; $i < 5; $i++) {
                    unset($category);

                    $category = null;
                    if (isset($categories["$categoryid"])) {
                        # DO NOT USE =& here - php crashes.

                        $category = $categories["$categoryid"];
                    }
                    if ($category == null) {
                        break;
                    }

                    $parentid = $category->parentid;


                    # Set the categoryid we want to retrieve next
                    $categoryid = $parentid;

                    $category_list[] = $category;

                    # If we're at the root category, exit
                    if ($parentid == 0) {
                        break;
                    }
                }
            }

            $category_list = array_reverse($category_list);

            if (count($category_list) > 0) {
                continue;
            }
            //break;
        }

        return $category_list;
    }

    public function database_create_product_comment($parentid, $productid, $userid, $admin_userid, $title, $comment) {
        $database = $this->database;

        $result = $database->add_comment($parentid, $productid, $userid, $admin_userid, $title, $comment);
        $this->increment_version();
        return $result;
    }

    public function database_delete_product_comment($productid, $commentid) {
        $database = $this->database;

        $result = $database->delete_comment($productid, $commentid);
        $this->increment_version();
        return $result;
    }

    public function database_hide_product_comment($productid, $commentid) {
        $database = $this->database;

        $result = $database->hide_comment($productid, $commentid);
        $this->increment_version();
        return $result;
    }

    public function session_get_unencrypted_user() {
        $safe_user = null;

        if (isset($_SESSION["client_site"]["unencrypted_user"])) {
            $safe_user = $_SESSION["client_site"]["unencrypted_user"];
        }

        return $safe_user;
    }

    public function session_get_unencrypted_admin_user() {
        $safe_user = null;

        if (isset($_SESSION["admin_site"]["unencrypted_user"])) {
            $safe_user = $_SESSION["admin_site"]["unencrypted_user"];
        }

        return $safe_user;
    }

    public function is_admin_logged_in() {
#var_dump($_SESSION);
#var_dump("hello");
        if (isset($_SESSION["admin_site_login"]["admin_user"]) && ($_SESSION["admin_site_login"]["admin_user"] != null)) {
            if (isset($_SESSION["admin_site_login"]["admin_user"]["user"])) {
# FURTHER CHECK in case of some hack setting the admin_user value to TRUE
                $admin_user = unserialize($_SESSION["admin_site_login"]["admin_user"]["user"]);
                if ($admin_user != null) {
                    return true;
                }
            }
        }
        return false;
    }

    public function get_comment_by_productid_and_commentid($productid, $commentid) {
        $foundcomment = null;
        //$allcomments = $this->database->get_all_comments_by_productid($productid);
        list($allcomments, $count) = $this->get_all_comments_by_productid($productid);

#var_dump($allcomments);

        foreach ($allcomments as $comment) {
            if ($comment->commentid == $commentid) {
                $foundcomment = $comment;
                break;
            }
        }


        return $foundcomment;
    }

    public function get_all_reviews() {
        $allreviews = $this->database->get_all_reviews();

        $reviews_count = count($allreviews);

        return array($allreviews, $reviews_count);
    }

    public function get_all_reviews_by_productid($productid) {

        list($allreviews, $all_reviews_count) = $this->get_all_reviews();

        $foundreviews = array();
        foreach ($allreviews as $key => $review) {
            if ($review->productid == $productid) {
                $foundreviews[] = $review;
            }
        }


        $reviews_count = count($foundreviews);

        return array($foundreviews, $reviews_count);
    }

    public function get_users_reviews($userid) {
        list($allreviews, $all_reviews_count) = $this->get_all_reviews();

        $foundreviews = array();
        foreach ($allreviews as $key => $review) {
            if ($review->userid == $userid) {
                $foundreviews[] = $review;
            }
        }

        $reviews_count = count($foundreviews);


        return array($foundreviews, $reviews_count);
    }

    public function get_review_by_reviewid($reviewid) {
        list($allreviews, $all_reviews_count) = $this->get_all_reviews();

        $foundreview = null;
        foreach ($allreviews as $key => $review) {
            if ($review->reviewid == $reviewid) {
                $foundreview = $review;
                break;
            }
        }

        //$reviews_count = count($foundreviews);


        return $foundreview;
    }

    public function database_create_product_review($productid, $userid, $title, $review) {
        $database = $this->database;

        $result = $database->add_review($productid, $userid, $title, $review);
        $this->increment_version();
        return $result;
    }

    public function database_delete_product_review($productid, $reviewid) {
        $database = $this->database;

        $result = $database->delete_review($productid, $reviewid);
        $this->increment_version();
        return $result;
    }

    public function database_hide_product_review($productid, $reviewid) {
        $database = $this->database;

        $result = $database->hide_review($productid, $reviewid);
        $this->increment_version();
        return $result;
    }

    public function get_all_users() {
        $users = $this->database->get_all_users();

        $count = count($users);

        return array($users, $count);
    }

    public function check_nickname_exists($nickname) {
        $users = $this->database->get_all_users();
        //list($users, $count) = $this -> get_all_users();

        $founduser = false;
        foreach ($users as $user) {
            if (($user->nickname == $nickname) && (($user->accountstate == 0) || ($user->accountstate == 1))) {
                $founduser = true;
            }
        }

        return $founduser;
    }

}
