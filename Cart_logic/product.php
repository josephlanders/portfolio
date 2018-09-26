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

#chdir("../client_code");

require_once("sortable.php");

class product implements sortable {

    public $variant_count = 0;
    public $productid = 0;
    public $dateadded = 0;
    public $variants = array();
    public $inventoryname = "";
    public $name = "";
    private $productname = "";
    private $producturl = "";
    public $description = "";
    public $categoryid = 0;
    public $price = 0;
    public $weightinkg = 0;
    public $heightincm = 0;
    public $depthincm = 0;
    public $quantityonhand = 0;
    public $featuredimage = 0;
    public $url = "";
    public $orderedqty = 0;
    private $special = null;
    public $purchaseprice = 0;
    private $comparator = 0;
    public $specialprice = 0;
    public $subtotal = 0;
    public $taxable = 0;
    public $is_taxable = true;
    public $featured = null;
    public $tax_itemid = null;
    public $decodedurl = "";
    public $tags = array();
    public $total_ordered = 0;
    public $use_stocklevel = true;
    public $orphaned = false;
    public $hide_when_out_of_stock = true;
    public $tax = 0;
    public $options = array();
    public $weightmin = 0;
    public $weightmax = 0;    
    public $images = array();
    public $type = "";
    public $metadata = array();
    public $taxname = "";
    public $taxes = array();
    public $purchaseprice_unit_tax = 0;
    public $purchaseprice_unit_taxes = array();
    #public $has_options = false;
    public $associated_products = array();
    public $prefix = "/buy/";
    public $comments = array();
    public $reviews = array();
    # The product array

    public function __construct($fields) {
        foreach ($fields as $key => $value) {
            $this->$key = $value;
        }

        $this->id = $this->productid;
        $this->name = $this->productname;

        $this->url = $this->producturl;

        array_map("rawurlencode", explode("/", $this -> decodedurl)));
               
        $prefix = $this -> prefix;
        $this->decodedurl = $this->url;
        $this->shorturl = implode("/", array_map("rawurlencode", explode("/", $this->decodedurl)));
        $this->url = $prefix . $this->shorturl;
        $this->producturl = $this->url;

        if ($this->tags != null) {
            #Expand the tags into a tags array
            $this->tags = explode(",", $this->tags);
        } else {
            $this->tags = array();
        }
    }
    
    public function set_images($images) {
        $this->images = $images;
        
        $images = array_values($images);
        if (isset($images[0])) {
            $this->featured = $images[0];
            #echo "featured";
        } else {
            #echo "no featured";
        }
    }

    public function get_images() {
        return $this->images;
    }

    public function set_comparator($value) {
        $this->comparator = $value;
    }

    public function get_comparator() {
        return $this->comparator;
    }

    public function compareTo($product) {
        $comp = $product->get_comparator();
        return $this->comparator - $comp;
    }

}
?>
