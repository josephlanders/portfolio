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

require_once("sortable.php");

class variant implements sortable {

    public $productid = 0;
    public $variantid = 0;
    public $dateadded = 0;
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
    public $special = null;
    public $purchaseprice = 0;
    private $comparator = 0;
    public $specialprice = 0;
    public $subtotal = 0;
    public $taxable = 0;
    public $is_taxable = true;
    public $featured = null;
    private $tax_itemid = null;
    public $decodedurl = "";
    public $id = 0;
    public $taxes = array();
    public $images = array();
    public $options = array();
    public $metadata = array();
    public $displayprice = 0;
    public $subtotal_with_tax = 0;
    public $purchaseprice_unit_tax = 0;
    public $purchaseprice_unit_taxes = array();
    public $subtotal_tax = 0;
    public $subtotal_taxes = array();
    public $stock_held = 0;
    public $stocklevel = 0;
    public $stocklevel_with_holds = 0;
    public $current_user_stock_held = 0;

    # The product array

    public function __construct($fields) {
        foreach ($fields as $key => $value) {
            $this->$key = $value;
        }

        /* Pass fields in to create special object */
        $this->special = new special($fields);
        
        $this->name = $this->variantname;

        $this->get_subtotal();

        $this->id = $this->variantid;
        
        $this -> stocklevel_with_holds = $this -> stocklevel;
    }

    public function set_images($images) {
        $this->images = $images;
        
        // Reorder to get the first variant as they are no longer keyed on 0 - 
        // could start on 2 or w/e if an image is deleted
        $images = array_values($images);
        if (isset($images[0])) {
            $this->featured = $images[0];
        }
    }

    public function get_images() {
        return $this->images;
    }

    public function get_purchaseprice() {
        $this->purchaseprice = $this->price;
        $this->specialprice = null;
        $special = $this->special;

        if ($special->active == true) {
            $specialprice = $special->specialprice;           
            if (($specialprice > 0) && ($specialprice < $this->price)) {
                $this->purchaseprice = $specialprice;
            }
            $this -> specialprice = $special->specialprice;
        } else {
        }
        
        return $this->purchaseprice;
    }

    public function get_subtotal() {
        $this->purchaseprice = $this->get_purchaseprice();
        $this->subtotal = $this->orderedqty * $this->purchaseprice;
        return $this->subtotal;
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
