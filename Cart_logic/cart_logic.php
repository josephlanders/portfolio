    public function add_product(product $product, $variantid) {
        $error_messages = array();
        $added = false;
        $error = false;


        list($in_stock, $error_messages) = $this->check_product_in_stock($product, $variantid);

        if ($in_stock == false) {
            return array($added, $error_messages);
        }

        list($exists, $error_messages) = $this->check_product_exists_in_cart($product, $variantid);

        if ($exists == false) {
            $count = $this->get_unique_product_count();
            if ($count >= $this->cart_max_count) {
                # - Unable to add product - Maximum 20 types of products allowed in cart"                    
                $error_messages[] = "CART_SIZE_EXCEEDED";
                throw new \Exception("CART_SIZE_EXCEEDED");
            }
        }

        list($ok, $add_error_messages) = $this->add_product_to_cart_without_increment($product, $variantid);
        $error_messages = array_merge($error_messages, $add_error_messages);

        list($incremented, $inc_error_messages) = $this->increment_product_count_in_cart($product, $variantid);
        $error_messages = array_merge($error_messages, $inc_error_messages);

        # Store the cart state in string form in memory for caching.
        $this->store_cart_session_string();

        $added = true;

        return array($added, $error_messages);
    }

    public function add_product_to_cart_without_increment(product $product, $variantid) {
        $ok = false;
        $error_messages = array();

        #var_dump($product);
        $productid = $product->id;

        $exists = false;

        $parcel = $this->get_parcel(0);

        if ($variantid == null) {
            $ok = false;
            $error_messages[] = "internal error, add_product_to_cart_without_increment, variantid is null";
            return array($ok, $error_messages);
        }

        $variant = null;


        if ($parcel == null) {
            #echo "parcel is null";
            # Create new parcel, add new product
            #TODO: Logic to get next parcel ID etc
            $fields["parcelid"] = 0;
            $parcel = new parcel($fields);

            if (isset($product->variants[$variantid])) {
                # OK I worked it out, this code uses the passed in product and removes the other VARIANTS
                # so they don't appear in the cart.
                $variant = $product->variants[$variantid];

                $product->variants = array();
                $product->variants[$variantid] = $variant;
            } else {
                $ok = false;
                $error_messages[] = "";
                return array($ok, $error_messages);
            }
        } else {
            #parcel exists, get products in parcel
            $parcel_products = $parcel->products;
            # Get product
            # Check the product exists in the parcel
            if (isset($parcel_products[$productid])) {
                $parcel_product = $parcel_products[$productid];

                # Check the variant exists in the parcel product
                if (isset($parcel_product->variants[$variantid])) {
                    $variant = $parcel_product->variants[$variantid];
                } else {
                    # Otherwise use the variant from the users product
                    $variant = $product->variants[$variantid];
                }
                $product = $parcel_product;

            } else {
                # If product doesn't exist, use user product
                // for clarity $product = $product
                $variant = $product->variants[$variantid];

                $product = $this->unset_empty_variants($product);
            }
        }

        $product->variants[$variantid] = $variant;
        $parcel->products[$productid] = $product;
        $this->set_parcel(0, $parcel);

        # Store the cart state in string form in memory for caching.
        $this->store_cart_session_string();

        return array($ok, $error_messages);
    }



    public function increment_product_count_in_cart($product, $variantid) {
        $error_messages = array();
        $incremented = false;
        $error = false;

        if ($variantid == null) {
            $incremented = false;
            $error_messages[] = "inc_error";
            return array($incremented, $error_messages);
        }


        if ($product != null) {
            $productid = $product->id;


            $parcel = $this->get_parcel(0);

            #parcel exists, get products in parcel
            $parcel_products = $parcel->products;
            # Get product
            # Check the product exists in the parcel
            if (isset($parcel_products[$productid])) {
                $parcel_product = $parcel_products[$productid];

                # Check the variant exists in the parcel product
                if (isset($parcel_product->variants[$variantid])) {
                    $variant = $parcel_product->variants[$variantid];
                    $product = $parcel_product;

                    $current_qty = $variant->orderedqty;

                    if ($current_qty + 1 > $this->cart_max_qty) {
                        $incremented = false;
                        $error = true;
                        $error_messages[] = "CART_ITEM_QTY_EXCEEDED";
                        throw new \Exception("CART_ITEM_QTY_EXCEEDED");
                    }


                    if ($product->use_stocklevel == true) {
                        # Do we have enough stock for it?
                        if ($variant->orderedqty + 1 > ($variant->stocklevel_with_holds + $variant->current_user_stock_held)) {
                            $incremented = false;
                            $error = true;
                            $error_messages[] = "CART_ITEM_STOCKLEVEL_EXCEEDED";
                            throw new \Exception("CART_ITEM_STOCKLEVEL_EXCEEDED");
                        }
                    }

                    if ($error == false) {
                        $incremented = true;
                        $variant->orderedqty = $variant->orderedqty + 1;
                    }

                    $product->variants[$variantid] = $variant;
                } else {
                    $error_messages[] = "Internal error, variant doesn't exist in product -> variants";
                }

                $parcel->products[$productid] = $product;
            } else {
                $error_messages[] = "Internal error, product doesn't exist in parcel -> products";
            }

            $this->set_parcel(0, $parcel);
        }

        return array($incremented, $error_messages);
    }

    public function remove_product($productid, $variantid) {
        $removed = false;
        $error_messages = array();
        $parcelid = 0;
        $parcel = $this->get_parcel($parcelid);

        if ($parcel != null) {
            $products = $parcel->products;

            if (isset($products[$productid])) {
                $product = $products[$productid];

                $variants = $product->variants;

                $variant_count = count($variants);

                if ($variant_count == 1) {
                    if (isset($products[$productid])) {
                        unset($products[$productid]);

                        $parcel->products = $products;

                        $this->set_parcel($parcelid, $parcel);
                    }
                } else {
                    if (isset($variants[$variantid])) {
                        unset($variants[$variantid]);
                    }

                    $product->variants = $variants;
                    $parcel->products = $products;
                    $this->set_parcel($parcelid, $parcel);
                }
            }
            $removed = true;
        }

        $this->store_cart_session_string();

        return array($removed, $error_messages);
    }




    public function calculate() {
        $model = $this->model;
        $product_total = 0;
        $product_undiscounted_total = 0;
        $shipping = 0;
        $subtotal = 0;
        $total = 0;
        $tax = 0;
        $weight = 0;
        $product_total_after_coupon = 0;
        $subtotal_after_coupon = 0;
        $total_without_shipping = 0;
        $total_with_shipping = 0;
        $coupon_applied_value = 0;

        $error_messages = array();
        $success = true;

        $tax_subtotal_or_itemised = "itemised";
        $tax_subtotal = false;
        $tax_is_itemised = true;

        $parcels = $this->parcels;

        $coupon = $this->get_coupon();

        $this->display_cart_prices_with_tax = (boolean) $model->config_get_setting_boolean_as_string("display_cart_prices_with_tax");
        $display_cart_prices_with_tax = $this->display_cart_prices_with_tax;


        $this->shipping_estimate_using_default_location = (boolean) $model->config_get_setting_boolean_as_string("shipping_estimate_using_shop_location");

        $shipping_address = $this->get_shipping_address();
        if ($shipping_address->country == null && $this->shipping_estimate_using_default_location === true) {

            list($default_country, $default_state) = $model->get_default_country_and_state();

            if (($default_country != null) && ($default_country != "choose_country")) {
                $shipping_address = new address(null);
                $shipping_address->country = $default_country;
                $shipping_address->state = $default_state;
                $this->set_shipping_address($shipping_address);
            }
        }

        if ($parcels == null) {
            #return array(false, "CART_EMPTY");
            $success = false;
            $error_messages[] = "internal error, parcel empty";
            return array($success, $error_messages);
        }

        $tax_calculator = new tax_calculator($this->model);

        $all_tax_rules = $model->get_all_taxes();
        $address = $this->get_shipping_address();

        $combined_taxes = array();

        $ratio_product_total = 0;
        $taxed_product_total = 0;

        foreach ($parcels as $parcelid => $parcel) {
            $products = $parcel->products;
            # GET THE PRODUCT PURCHASE PRICE TOTALS
            foreach ($products as $product) {
                $variants = $product->variants;
                foreach ($variants as $variant) {
                    $cart_is_taxable = true;
                    $purchase_price = $variant->get_purchaseprice();
                    $product_qty_purchase_price = $purchase_price * $variant->orderedqty;

                    $ratio_product_total += $product_qty_purchase_price;
                    $amount = $product_qty_purchase_price;

                    if ($product->is_taxable == true) {

                        # Because of the way the rules are applied, each tax item must have it's tax_rules applied separately 
                        # to find the correct most specific rule per tax type.
                        foreach ($product->taxes as $tax_item) {
                            $tax_rules_that_apply = array();
                            $tax_rules_that_apply[] = $tax_item;

                            list($tax_amount, $matched_rules) = $tax_calculator->calculate_tax_on_amount($amount, $tax_rules_that_apply, $all_tax_rules, $address);

                            $combined_taxes = $tax_calculator->combine_tax($combined_taxes, $matched_rules);

                            $variant->subtotal_tax = $variant->subtotal_tax + $tax_amount;

                            $variant->subtotal_taxes = $variant->subtotal_taxes + $matched_rules;
                        }

                    }

                    $variant->subtotal = $variant->purchaseprice * $variant->orderedqty;
                }
            }
        }

        $coupon_applied_value = 0;
        $weight = 0;
        $product_tax_sum = 0;
        foreach ($parcels as $parcelid => $parcel) {
            $products = $parcel->products;

            foreach ($products as $productid => $product) {
                $variants = $product->variants;
                foreach ($variants as $variant) {
                    $orderedqty = $variant->orderedqty;
                    # Use the purchase price as this is the
                    # Lower of the special price or the original price

                    $purchaseprice = $variant->get_purchaseprice();
                    $product_qty_purchase_price = ($orderedqty * $purchaseprice);

                    // Remove a percentage discount from each item
                    if ($coupon != null) {

                        $discount_money_value = $coupon->get_discount_money_value($product_qty_purchase_price, $ratio_product_total);

                        $discounttype = $coupon->discounttype;

                        #Subtotal value
                        if (($discounttype == 3) || ($discounttype == 5)) {
                            $product_qty_purchase_price_total_after_coupon = $product_qty_purchase_price - $discount_money_value;
                            $coupon_value = $discount_money_value;



                            #orders can't have negative values, otherwise we'd be paying the customer :);
                            if ($product_qty_purchase_price_total_after_coupon < 0) {
                                $product_qty_purchase_price_total_after_coupon = 0;
                                $coupon_value = $product_qty_purchase_price;
                            }

                            $coupon_applied_value += $coupon_value;

                            $product_qty_purchase_price = $product_qty_purchase_price_total_after_coupon;
                        }
                    }

                    // Remove a specific amount (total value) - we can either apply this per item OR 
                    // after all the products taxes have been calculated, then use this as a ratio against the $product_total;
                    //   either way, the sum should be the same once rounded down unless we accidentally are 0.00001 less....

                    if ($coupon != null) {

                        $discount_money_value = $coupon->get_discount_money_value($ratio_product_total, $ratio_product_total);

                        $ratio = $discount_money_value / $ratio_product_total;
                        $discounttype = $coupon->discounttype;

                        #Subtotal value
                        if (($discounttype == 4)) {
                            $ratio_discount = $product_qty_purchase_price * $ratio;
                            $product_qty_purchase_price_total_after_coupon = $product_qty_purchase_price - $ratio_discount;
                            $coupon_value = $ratio_discount;



                            #orders can't have negative values, otherwise we'd be paying the customer :);
                            if ($product_qty_purchase_price_total_after_coupon < 0) {
                                $product_qty_purchase_price_total_after_coupon = 0;
                                $coupon_value = $product_qty_purchase_price;
                            }
                            $coupon_applied_value += $coupon_value;

                            $product_qty_purchase_price = $product_qty_purchase_price_total_after_coupon;
                        }
                    }



                    $amount = $product_qty_purchase_price;

                    if ($product->is_taxable == true) {
                        $tax_rules_that_apply = $product->taxes;

                        # Because of the way the rules are applied, each tax item must have it's tax_rules applied separately 
                        # to find the correct most specific rule per tax type.
                        foreach ($product->taxes as $tax_item) {
                            $tax_rules_that_apply = array();
                            $tax_rules_that_apply[] = $tax_item;

                            list($tax_amount, $matched_rules) = $tax_calculator->calculate_tax_on_amount($amount, $tax_rules_that_apply, $all_tax_rules, $address);

                            $combined_taxes = $tax_calculator->combine_tax($combined_taxes, $matched_rules);

                            $variant->subtotal_tax = $variant->subtotal_tax + $tax_amount;

                            $variant->subtotal_taxes = $variant->subtotal_taxes + $matched_rules;

                            $product_tax_sum += $tax_amount;
                        }
                    }

                    # INFORMATION ONLY NOT USED FOR CALCULATIONS
                    # The discounted total may seem wrong when adding numbers together for the 
                    # user in the checkout
                    #$product_undiscounted_total += $product_qty_purchase_price;

                    $variant->subtotal = $variant->purchaseprice * $variant->orderedqty;

                    $weight += ($variant->weightinkg * $orderedqty);

                    $product_total += $product_qty_purchase_price;
                }
            }
        }

        $this->item_total = $product_total;

        $this->weight = $weight;

        $shipping = null;
        $shipping_address = $this->get_shipping_address();

        $shipping_method = null;

        if ($shipping_address != null) {
            if ($shipping_address->country != "") {

                $shipping_method = $this->get_selected_shipping_method();

                if ($shipping_method != null) {

                    if (($shipping_method->selectedcountry != $shipping_address->country) || ($shipping_method->selectedstate != $shipping_address->state)) {
                        #echo "Deleting";
                        $shipping_method = null;
                    }
                }

                if ($shipping_method == null) {
                    $shipping_method = $this->set_cheapest_shipping_method();
                }

                if ($shipping_method != null) {
                    $shipping = $shipping_method->total;
                } else {
                    if ($this->shipping_address != null) {
                        $error_messages[] = "CART_SHIPPING_NO_RULE";
                    }
                    $shipping = null;
                }
            }
        }

        $this->shipping_method = $shipping_method;


        #$this -> shipping = $shipping;
        #$this->shipping_total_before_coupon = $shipping;

        /* Coupons that apply to items values MUST reduce the product_total... */

        $shipping_after_coupon = $shipping;

        # Subtotal based tax
        if ($coupon != null) {

            $discount_money_value = $coupon->get_discount_money_value($shipping, $product_total);

            $discounttype = $coupon->discounttype;

            #Subtotal value
            if (($discounttype == 6) || ($discounttype == 7)) {
                $shipping_total_after_coupon = $shipping - $discount_money_value;
                $coupon_applied_value = $discount_money_value;

                #orders can't have negative values, otherwise we'd be paying the customer :);
                if ($shipping_total_after_coupon < 0) {
                    $shipping_total_after_coupon = 0;
                    $coupon_applied_value = $shipping;
                }

                $shipping_after_coupon = $shipping_total_after_coupon;
            }
        }

        #itemised
        if ($coupon != null) {

            $discount_money_value = $coupon->get_discount_money_value($shipping, $product_total);

            $discounttype = $coupon->discounttype;

            #Subtotal value
            if (($discounttype == 3)) {
                $shipping_total_after_coupon = $shipping - $discount_money_value;
                $coupon_value = $discount_money_value;

                #orders can't have negative values, otherwise we'd be paying the customer :);
                if ($shipping_total_after_coupon < 0) {
                    $shipping_total_after_coupon = 0;
                    $coupon_value = $shipping;
                }

                $coupon_applied_value += $coupon_value;

                $shipping_after_coupon = $shipping_total_after_coupon;
            }
        }

        $amount = $shipping_after_coupon;
        $shipping_tax_rules_that_apply = $model->get_all_shipping_taxes();

        $shipping_tax = 0;
        $shipping_taxes = array();
        # Because of the way the rules are applied, each tax item must have it's tax_rules applied separately 
        # to find the correct most specific rule per tax type.
        foreach ($shipping_tax_rules_that_apply as $tax_item) {
            $tax_rules_that_apply = array();
            $tax_rules_that_apply[] = $tax_item;

            list($tax_amount, $matched_rules) = $tax_calculator->calculate_tax_on_amount($amount, $tax_rules_that_apply, $all_tax_rules, $address);
            $shipping_taxes = $tax_calculator->combine_tax($shipping_taxes, $matched_rules);
            $combined_taxes = $tax_calculator->combine_tax($combined_taxes, $matched_rules);

            $shipping_tax += $tax_amount;
        }

        $product_total_with_shipping = $product_total + $shipping_after_coupon;

        #THIS IS THE FINAL VLAUE OF THE SUBTOTAL (BEFORE COUPON)
        $subtotal = $product_total_with_shipping;

        $subtotal_after_coupon = $subtotal;

        $total_tax = $product_tax_sum + $shipping_tax;
        $tax = $total_tax;
        $taxes = $combined_taxes;
        $shipping_taxes = $shipping_taxes;
        $parcel = $this->get_parcel(0);
        $parcel->products = $products;
        $this->set_parcel(0, $parcel);

        $tax_cart_product_prices_include_tax = $tax_calculator->get_tax_cart_product_prices_include_tax();

        if ($tax_cart_product_prices_include_tax === true) {
            $total_with_shipping = $subtotal_after_coupon;
        } else {
            $total_with_shipping = $subtotal_after_coupon + $tax;
        }

        $total = $total_with_shipping;

        if ($total < 0) {
            $total = 0;
        }




        if ($error_messages != array()) {
            $success = false;
        }


        $this->weight = $weight;



        //Represents the product total without discounts unless discounts.
        $this->product_total = $ratio_product_total;
        //Represents the product total + product taxation, without discounts unless discounts.

        $this->product_undiscounted_total = $ratio_product_total;
        $this->shipping = $shipping;
        $this->subtotal = $subtotal;

        $this->tax = $tax;
        $this->total = $total;


        # arrays of calculated taxes
        $this->taxes = $taxes;
        $this->shipping_taxes = $shipping_taxes;
        $this->count = $this->get_count();
        $this->coupon_applied_value = $coupon_applied_value;

        return array($success, $error_messages);
    }



