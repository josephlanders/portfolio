{* 
   This code is distributed under the BSD license and is free for commercial use
   ,please see the LICENSE file in the root folder 
*}


<!-- <script src="{$theme_dir}/jquery/jquery.js"></script> -->
              {*$cart -> count*}
                

{*if $cart -> count == 0*}
{if $cart -> count == 0}
{*if empty($cart -> parcels[0])*}
Your cart is empty, please add items to your cart first.
Visit the <a href="/">Homepage</a> or
<a href="/">Continue Shopping</a>

{else}


<div id="cart">
    <div id="cart_error_messages">        
      {if (!empty($error_messages))}
          
         {foreach $error_messages as $error_message}
            <span style="color:red;">
               <br/>
               •
               {$error_message}
            </span>
         {/foreach}
      {/if}
     </div>
<ul id="cart_table">
    <li id="cart_header_row">
        <!-- 
        <div id="cart_header_productid" class="cart_header_descriptor">Item ID</div>
        -->
        <div id="cart_header_image" class="cart_header_descriptor">Image</div>
        <div id="cart_header_name" class="cart_header_descriptor">Name</div>
        <div id="cart_header_unitcost" class="cart_header_descriptor">Each</div>
        <div id="cart_header_unittaxcost" class="cart_header_descriptor">Tax</div>
        <div id="cart_header_quantity" class="cart_header_descriptor">
            Qty
            <!--- This needs to be a combo box -->
        </div>


        <div id="cart_header_quantitycost" class="cart_header_descriptor">Sub Total</div>


        <div id="cart_header_remove" class="cart_header_descriptor"> </div>

    </li>

    <form id="cart_contents_form" action="/{$url_format_keywords["cart"]}" method="post"  accept-charset="UTF-8" >
        <input type="hidden" id="action" name="action" value="cart_update_qty"/>

{foreach $cart -> parcels as $parcel}
        {foreach $parcel -> products as $product}

   {foreach $product -> variants as $variant}

        <li id="cart_row" class="cart_row_class" data-parcelid="{$parcel -> id}" data-productid="{$product -> id}" data-variantid="{$variant -> id}">
            <!-- 
            <div id="cart_productid" class="cart_row_descriptor">
{$product -> id}
            </div>
            -->
            <div id="cart_image" class="cart_row_descriptor">
                {*$product -> featured -> thumb*}
                <a href="{$product -> url}" >
                        <img src="{$product -> featured -> thumb}" class="cart_image_img" />
                </a>

            </div>

            <div id="cart_name" class="cart_row_descriptor">
                {$product -> name} {if $variant -> name != null} - {/if} {$variant -> name}
                {if $product -> use_stocklevel == true}
                <div id="cart_stocklevel">Stocklevel: {$variant -> stocklevel_with_holds}</div>
                {/if}
                <div id="cart_weight">Weight: {$variant -> weightinkg * $variant -> orderedqty} kg </div>
            </div>
            <div id="cart_unitcost" class="cart_row_descriptor">
                <span class="money">{$variant -> purchaseprice|money_format:$format_money_format}</span>
            </div>
            <div id="cart_unittaxcost" class="cart_row_descriptor">
                <span class="money">{$variant -> purchaseprice_unit_tax|money_format:$format_money_format}</span>
            </div>
            <div id="cart_quantity" class="cart_row_descriptor">
                <!--- This needs to be a combo box or text box -->

                <input type="text" maxlength="3" style="width:30px;" value="{$variant -> orderedqty}" id="qty_{$product -> id}_{$variant -> variantid}" name="qty_{$product -> id}_{$variant -> variantid}" />

                <div id="cart_quantityaddremove" class="cart_row_descriptor">

                     
                </div>





            </div>
            
                <div id="cart_quantitycost" class="cart_row_descriptor">
                                                            
                    {$sub = $variant -> subtotal}
                    
                    <span class="money">{$sub|money_format:$format_money_format}</span>
                </div>

                    <!--
                <div id="cart_taxcost" class="cart_row_descriptor">
                    {$sub = $variant -> tax  * $variant -> orderedqty}
                    
                    <span class="money">{$sub|money_format:$format_money_format}</span>
                    
                    
                </div>            
                    -->

            <div id="cart_remove" class="cart_row_descriptor">
                <a id="cart_remove_link_id" class="cart_remove_link" data-parcelid="{$parcel -> id}" data-productid="{$product -> id}" data-variantid="{$variant -> id}" href="/{$url_format_keywords["cart"]}?parcelid={$parcel -> parcelid}&productid={$product -> productid}&variantid={$variant -> variantid}&action=cart_remove_from_cart">
                    Remove</a>
            </div>

<!--
    {$tax_rules = $variant -> subtotal_taxes}
    {foreach $tax_rules as $taxes}
        {if $taxes -> total_tax != 0}
    {$taxes -> taxname}
    {$taxes -> percentage}%
    <span class="money">{$taxes -> total_tax|money_format:$format_money_format}</span>
    {/if}
    {/foreach}
    -->


        </li>

{/foreach}
        {/foreach}
{/foreach}
</ul>



        <button  type="submit" id="cart_update_qty_button" class="viewcart_update_cart_submit">Update</button>
    </form>

<div id="cart_summary">

    <div id="cart_lower_left">
    <div id="cart_estimate_shipping"  style="float:left;width:300px;margin-top:100px;">
                                <h1 class="product_label" style="float:left;clear:left;width:300px;"> Shipping Estimate </h1>
            <form action="/{$url_format_keywords["cart"]}" accept-charset="UTF-8" method="post">

                <div style="float:left;width:300px">
                        <h4 class="product_label" style="float:left;clear:left;width:100px;"> Country </h4>
                        <!--
            <input type="text" name="country" value="" class="product_input"></input>
                        -->

                        <select name="shiptocountry" id="shiptocountry" class="product_input" style="float:left;width:200px;">
                            {$countries}
                            </select>
</div>
<div style="float:left; width:300px">
            <h4 class="product_label" style="float:left;clear:left;width:100px;">State</h4>            
            {if $cart -> shipping_address != null}
                               {$country = $cart -> shipping_address -> country}
                               
            {if !empty($states)}
                                                                    <select name="shiptostate" id="shiptostate" class="product_input" style="float:left;width:200px;">
                                         
                                                                        
                                                                        
                                                                            {$states}

                                                                        </select>
                                                                            {else}
                                                         <select name="shiptostate" id="shiptostate" class="product_input" style="float:left;width:200px;visibility:hidden;">
                                         
                                                                        
                                                                        
                                                                            

                                                                        </select>
            {/if}                                                                                                                                            {/if}
           
</div>
<div style="float:left; width:300px">
            <button  type="submit" value="submit" name="cart_estimate_shipping_button" id="cart_estimate_shipping_button" class="viewcart_update_cart_submit" style="float:left;clear:left;clear:right;">Calculate</button>
</div>
            <input type="hidden" name="action" id="action" value="cart_estimate_shipping"></input>
           
        </form>  </div>
            
            
            
        <div id="cart_coupon_div">
            <h2>{$strings["CART_COUPON_HEADER"]}</h2>
            
                <div id="coupon_error_messages" style="color:red;float:left;{if empty($coupon_error_messages)}display:none;{/if}">
            {foreach $coupon_error_messages as $error_message}
                
                    • {$error_message}<br/>
                
            {/foreach}
            </div>
            
                <div id="coupon_success_messages" style="color:green;float;left;{if empty($coupon_success_messages)}display:none;{/if}">
            {foreach $coupon_success_messages as $success_message}
                
                    • {$success_message}<br/>
                
            {/foreach}
            </div>
            

            <form name="form1" action="/{$url_format_keywords["cart"]}" method="POST"  accept-charset="UTF-8" >
                <div class="coupon_row" style="float:left;width:300px;">
                    <!-- <h4 class="product_label" style="float:left;clear:left;width:100px;"> Coupon </h4> -->
                    <input type="text" id="code" name="code" class="product_input" />
                </div>

                <input type="hidden" id="action" name="action" value="cart_use_coupon"/>

                <div class="coupon_row">
                    <label class="product_label">   </label>
                    <button name="submit" id="cart_use_coupon_button" class="viewcart_update_cart_submit">{$strings["CART_COUPON_APPLY"]}</button>
                </div>
                
                <!-- <a href="/cart?action=cart_remove_coupon">Remove Coupon</a> -->

            </form>
        </div>
                </div>
                
   
    <div id="cart_totals">

        <div id="cart_total_row">
            <div id="cart_product_total_description">Item total excl GST</div>
            <div id="cart_product_total" class="cart_final_info">
{*$cart -> item_total|money_format:$format_money_format*} 
<span class="money">{$cart -> product_total|money_format:$format_money_format}</span>
            </div>
        </div>
        <div id="cart_total_row">
            

            <div id="cart_delivery_description">
                Delivery and handling excl GST
            </div>
            <div id="cart_shipping" class="cart_final_info">
                
{if $cart -> shipping === null} 
Calculated at checkout
    {else}
        <span class="money">{$cart -> shipping|money_format:$format_money_format}</span>
{/if}
            </div>
        </div>
            
      
        <div id="cart_coupon_row" {if $cart -> coupon_applied_value == 0}style="display:none;"{/if} >
            <div id="cart_coupon_description">Coupon applied value</div>
            <div id="cart_coupon_applied_value" class="cart_final_info">
                <span class="money">-{$cart->coupon_applied_value|money_format:$format_money_format}</span>
            </div>
        </div>
      
            
      {*if $cart -> coupon_applied_value > 0}
        <div id="cart_total_row">
            <div id="cart_coupon_description">Coupon applied value</div>
            <div id="cart_coupon_applied_value" class="cart_final_info">
                <span class="money">-{$cart->coupon_applied_value|money_format:$format_money_format}</span>
            </div>
        </div>
      {/if*}

        <div id="cart_total_row">
            <div id="cart_subtotal_description">Sub total excl GST</div>
            <div id="cart_subtotal" class="cart_final_info">
                
                <span class="money">{$cart -> subtotal|money_format:$format_money_format}</span>
            </div>
        </div>
 


        <div id="cart_total_row">
            <div id="cart_delivery_description">
               GST 
            </div>
            <div id="cart_tax" class="cart_final_info">
                
                <span class="money">{$cart -> tax|money_format:$format_money_format}</span>
            </div>
            
          
    {foreach $cart -> taxes as $taxes}
        {if $taxes -> total_tax != 0}
            <div id="cart_delivery_description">
               {$taxes -> taxname} {$taxes -> percentage}%
            </div>
            <div id="cart_delivery_total" class="cart_final_info">                
                <span class="money">{$taxes -> total_tax|money_format:$format_money_format}</span>
            </div>                      
    {/if} 
    {/foreach}
        </div>


        <div id="cart_total_row">
            <div id="cart_total_description">
                Total (Including GST)
            </div>
            <div id="cart_total" class="cart_final_info">

                <span class="money">{$cart -> total|money_format:$format_money_format}</span>
            </div>
        </div>

    </div>

</div>
            
            
<div class="viewcart_checkout">

	<a href="/{$url_format_keywords["cart"]}?action=cart_validate_cart&payment_method=PAYPAL_EXPRESS"
		id="viewcart_paypal_link">
		<div id="viewcart_div_button_paypal"></div> </a> 
<div id="viewcart_div_or">
or
</div>
    <a href="/{$url_format_keywords["cart"]}?action=cart_validate_cart" id="viewcart_checkout_link">
        <div id="viewcart_div_button_checkout">
            <span class="viewcart_button_text">Checkout</span>
        </div> </a>       

<!--    
        <a href="/cart?action=validate_cart&requires_login=true" id="viewcart_checkout_link">
        <div id="viewcart_div_button_checkout">
            <span class="viewcart_button_text">Checkout with login</span>
        </div> </a>
-->
</div>    
          
            <!-- end cart -->
            </div>


{/if}



  <script>
   //var $countries = new Object();
   var $states = new Object();
   //var $states = [];

   function cart_load(event)
   {
       $data = {
           action: "get_state_options",
    format: "json"
       };

    $.ajax({
        type: "GET",
        dataType: "json",
        url: "{$protocol}://{$shop -> localurl}",
        data: $data,       
       success: cartfunc
     });
   }
   
   
   
   function cartfunc(json, jsonstate){
                $states = json["all_states"];
               $('#shiptocountry').ready(setcountries);
                 
          }
   
   function setcountries (event, $type){
                                                        
      $('#shiptocountry').change(setstates);

   }
                    
   function setstates (event, $type){
      $country =  $('#shiptocountry').val();
                                                                                
$text = "";
      $has_states = false;
      if ($country in $states)
      {
      $text = "<select name='shiptostate' id='shiptostate' class='product_input' style='float:left;width:200px'>";                                                              
      $text += $states[$country];
      $text += "</select>";
          $has_states = true;

      }

       
                                                
      if ($has_states == false)
      {
      $('#shiptostate').replaceWith("<input type=text name='shiptostate' id='shiptostate'></input>");
         $('#shiptostate').hide();                  
      } else {
               $('#shiptostate').replaceWith($text);
               $('#shiptostate').show();

      $('#shiptostate').show();
      }
                            
   };
   
   function estimate_shipping_load(event)
   {        
       
       $data = {
       action: "cart_estimate_shipping",
       shiptocountry: $("#shiptocountry").val(),
       shiptostate: $("#shiptostate").val(),
       format: "json"
        };

    $.ajax({
        type: "POST",
        dataType: "json",
        url: "{$protocol}://{$shop -> localurl}?",
        data: $data,       
       success: cartestimateupdatefunc
     });
     
     event.stopPropagation();
  
  return false;
   }
   
   function cartestimateupdatefunc(json, jsonstate)
   {
       $("#cart_product_total").html("<span class='money'>" + json.product_total_formatted + "</span>");
       $("#cart_subtotal").html("<span class='money'>" + json.subtotal_formatted + "</span>");
       if (json.shipping !== null)
       {
           $("#cart_shipping").html("<span class='money'>" + json.shipping_formatted + "</span>");
       } else {    
           $("#cart_shipping").html("<span class='money'>Calculated at checkout</span>");
       }
       $("#cart_total").html("<span class='money'>" + json.total_formatted + "</span>");
       
   var $coupon_applied_value_element = $('#cart_coupon_applied_value');

    if ( $coupon_applied_value_element.length > 0){
        
          $("#cart_coupon_applied_value").html("<span class='money'>" + json.coupon_applied_value_formatted + "</span>");
          if (json.coupon_applied_value > 0)
          {
             $("#cart_coupon_row").show();
          } else {
              $("#cart_coupon_row").hide();
          }
      }
       $("#cart_tax").html("<span class='money'>" + json.tax_formatted + "</span>");
       
       $error_messages = json.error_messages;
       
       $new_string = "";
       $.each($error_messages, function($id, $error_message)
       {
           $new_string +=
                   "<span style='color:red;'><br/>&bull;" + $error_message + "</span>";
       }
                );
       
       $("#cart_error_messages").html($new_string);

   }
   
   
   function use_coupon_load(event)
   {                                             
    $.ajax({
        type: "POST",
        dataType: "json",
        url: "{$protocol}://{$shop -> localurl}?",
        data: {
       action: "cart_use_coupon",
       code: $("#code").val(),
       format: "json"
        },       
       success: cartusecouponupdatefunc
     });
     
     event.stopPropagation();
  
  return false;
   }
   
   function cartusecouponupdatefunc(json, jsonstate)
   {
       $("#cart_product_total").html("<span class='money'>" + json.product_total_formatted + "</span>");
       $("#cart_subtotal").html("<span class='money'>" + json.subtotal_formatted + "</span>");
       if (json.shipping !== null)
       {
           $("#cart_shipping").html("<span class='money'>" + json.shipping_formatted + "</span>");
       } else {
           $("#cart_shipping").html("<span class='money'>Calculated at checkout</span>");
       }
       $("#cart_total").html("<span class='money'>" + json.total_formatted + "</span>");
       
   var $coupon_applied_value_element = $('#cart_coupon_applied_value');

    if ( $coupon_applied_value_element.length > 0){
        
          $("#cart_coupon_applied_value").html("<span class='money'>" + json.coupon_applied_value_formatted + "</span>");
          if (json.coupon_applied_value > 0)
          {
             $("#cart_coupon_row").show();
          } else {
              $("#cart_coupon_row").hide();
          }
      }
       $("#cart_tax").html("<span class='money'>" + json.tax_formatted + "</span>");
       
       $error_messages = json.error_messages;
       
       $new_string = "";
       $.each($error_messages, function($id, $error_message)
       {
           $new_string +=
                   "<span style='color:red;'><br/>&bull;" + $error_message + "</span>";
       }
                );
       
       $("#cart_error_messages").html($new_string);
       if (json.error_messages != null)
       {
       $("#coupon_error_messages").html($new_string);
       $("#coupon_error_messages").show();
       } else {
           $("#coupon_error_messages").hide();
       }
       
       $success_messages = json.success_messages;
       
       $new_string = "";
       $.each($success_messages, function($id, $success_message)
       {
           $new_string +=
                   "<span style='color:green;float;left;clear:left;'><br/>&bull;" + $success_message + "</span>";
       });
       
              if (json.success_messages != null)
       {
       $("#coupon_success_messages").html($new_string);
       $("#coupon_success_messages").show();
       } else {
           $("#coupon_success_messages").hide();
       }

   }  
   
   function cart_remove_load(event)
   {                        
     event.preventDefault();
     
      $productid = $(event.target).attr("data-productid");
       $variantid = $(event.target).attr("data-variantid");
       $parcelid = $(event.target).attr("data-parcelid");

    $.ajax({
        type: "POST",
        dataType: "json",
        url: "{$protocol}://{$shop -> localurl}",
        data: {
               action: "cart_remove_from_cart",
               parcelid: $parcelid,
               productid: $productid,
               variantid: $variantid,
               format: "json"
        },       
       success: cartremoveupdatefunc
     });
     
     //event.stopPropagation();

  
  //return false;
   }
   
   function cartremoveupdatefunc(json, jsonstate)
   {
       $parcelid = json.fields.parcelid;
       $productid = json.fields.productid;
       $variantid = json.fields.variantid;      
       $updated = json.updated;
       
       $data = $(".cart_row_class");
       
       
       // Output go, 2  , 4, 3
       
           if ($updated == true)
           {
       $.each($data, function (index, element)
       {
           //0 htmlelement object
           //alert(index);
           //alert(element);
           //alert($(element));
           //$(this).attr("data-parcelid");
                   //$(element).attr("data-parcelid");
           //alert(element.attr("data-parcelid"));
           $elementParcelID = $(element).attr("data-parcelid");
           $elementProductID = $(element).attr("data-productid");
           $elementVariantID = $(element).attr("data-variantid");
           

           if (($parcelid == $elementParcelID) && ($productid == $elementProductID) && ($variantid == $elementVariantID))
           {
               //alert($parcelid + " "  + $productid + " " + $variantid + " " + $elementParcelID + " " + $elementProductID + " " + $elementVariantID);
               $(element).remove();
           }
       });
                  }
                  
       $('#cartbar_count').html(json.cart_count);

       //return;
               //$("A").val
       $("#cart_product_total").html("<span class='money'>" + json.product_total_formatted + "</span>");
       $("#cart_subtotal").html("<span class='money'>" + json.subtotal_formatted + "</span>");
       if (json.shipping !== null)
       {
           $("#cart_shipping").html("<span class='money'>" + json.shipping_formatted + "</span>");
       } else {
           $("#cart_shipping").html("<span class='money'>Calculated at checkout</span>");
       }
       $("#cart_total").html("<span class='money'>" + json.total_formatted + "</span>");
       
   var $coupon_applied_value_element = $('#cart_coupon_applied_value');

    if ( $coupon_applied_value_element.length > 0){
        
          $("#cart_coupon_applied_value").html("<span class='money'>" + json.coupon_applied_value_formatted + "</span>");
          if (json.coupon_applied_value > 0)
          {
             $("#cart_coupon_row").show();
          } else {
              $("#cart_coupon_row").hide();
          }
      }
       $("#cart_tax").html("<span class='money'>" + json.tax_formatted + "</span>");
       
       $error_messages = json.error_messages;
       
       $new_string = "";
       $.each($error_messages, function($id, $error_message)
       {
           $new_string +=
                   "<span style='color:red;'><br/>&bull;" + $error_message + "</span>";
       }
                );
       
       $("#cart_error_messages").html($new_string);
       if (json.error_messages != null)
       {
       $("#coupon_error_messages").html($new_string);
       $("#coupon_error_messages").show();
       } else {
           $("#coupon_error_messages").hide();
       }
       
   }
   
   function update_qty_load(event)
   {               
        $data = {
       //action: "cart_update_qty",
       format: "json"
        };
       
        $arr = $("#cart_contents_form").serializeArray();        
       
   $data_serialized = $.param ($data, true);
   
   $data_serialized2 = $.param ($arr, true);
   
   $data_serialized = $data_serialized + "&" + $data_serialized2;
       


    $.ajax({
        type: "POST",
        dataType: "json",
        url: "{$protocol}://{$shop -> localurl}?",
        data: $data_serialized,       
       success: cartqtyupdatefunc
     });
     
     
     event.stopPropagation();
  
  return false;
   }
   
   function cartqtyupdatefunc(json, jsonstate)
   {
       $updated = json.updated;
       
       $data = $(".cart_row_class");
       
       $cart = json.cart;       
       
       // Output go, 2  , 4, 3
       
       $.each($data, function (index, element)
       {           
           
           $elementParcelID = $(element).attr("data-parcelid");
           $elementProductID = $(element).attr("data-productid");
           $elementVariantID = $(element).attr("data-variantid");
                      
           $.each ($cart["parcels"], function (index, $parcel)
           {               
               $parcelid = $parcel["parcelid"];
               $products = $parcel["products"];
              $.each ($products, function (index, $product)
              {
                  $productid = $product["productid"];
                  $variants = $product["variants"];
              $.each ($variants, function (index, $variant)
              {
                  $variantid = $variant["variantid"];
		  
           if (($parcelid == $elementParcelID) && ($productid == $elementProductID) && ($variantid == $elementVariantID))
           {
               $orderedqty = $variant["orderedqty"];
               $("#qty_" + $productid + "_" + $variantid).val($orderedqty);
               
               $cart_purchaseprice_obj = $(element).find("#qty_" + $productid + "_" + $variantid);
               $cart_purchaseprice_obj.val($orderedqty);
               $cart_purchaseprice_obj = $(element).children("#cart_unitcost");

               $cart_purchaseprice_obj.html("<span class='money'>" + $variant["purchaseprice_formatted"] + "</span>");
               
               $cart_purchaseprice_obj = $(element).children("#cart_unittaxcost");

               $cart_purchaseprice_obj.html("<span class='money'>" + $variant["purchaseprice_unit_tax_formatted"] + "</span>");
               
               $cart_purchaseprice_obj = $(element).children("#cart_quantitycost");

               $cart_purchaseprice_obj.html("<span class='money'>" + $variant["subtotal_formatted"] + "</span>");


           }

              });
              });
           });

       });
       
       $('#cartbar_count').html(json.cart_count);
                  //} 
       //return;
       
       $("#cart_product_total").html("<span class='money'>" + json.product_total_formatted + "</span>");
       $("#cart_subtotal").html("<span class='money'>" + json.subtotal_formatted + "</span>");
       if (json.shipping !== null)
       {
           $("#cart_shipping").html("<span class='money'>" + json.shipping_formatted + "</span>");
       } else {
           $("#cart_shipping").html("<span class='money'>Calculated at checkout</span>");
       }
       $("#cart_total").html("<span class='money'>" + json.total_formatted + "</span>");
       
   var $coupon_applied_value_element = $('#cart_coupon_applied_value');

    if ( $coupon_applied_value_element.length > 0){
        
          $("#cart_coupon_applied_value").html("<span class='money'>" + json.coupon_applied_value_formatted + "</span>");
          if (json.coupon_applied_value > 0)
          {
             $("#cart_coupon_row").show();
          } else {
              $("#cart_coupon_row").hide();
          }
      }
       $("#cart_tax").html("<span class='money'>" + json.tax_formatted + "</span>");
       
       $error_messages = json.error_messages;
       
       $new_string = "";
       $.each($error_messages, function($id, $error_message)
       {
           $new_string +=
                   "<span style='color:red;'><br/>&bull;" + $error_message + "</span>";
       }
                );
       
       $("#cart_error_messages").html($new_string);
       
   }
   
   
                                                                                                        
   $(document).ready(function(){
      cart_load();
      
         $("#cart_estimate_shipping_button").bind("click", estimate_shipping_load);
         
         $("#cart_use_coupon_button").bind("click", use_coupon_load);
         
         $("#cart_use_coupon_button").bind("click", use_coupon_load);
         
         $(".cart_remove_link").bind("click", cart_remove_load);
         //$(".cart_remove_link").one("click", cart_remove_load);
         
         //Not finished.
         $("#cart_update_qty_button").bind("click", update_qty_load);
         
     
   });
                </script>
