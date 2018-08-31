using System;
using System.Data;
using System.Configuration;
using System.Collections;
using System.Collections.Generic;
using System.Web;
using System.Web.Security;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Web.UI.WebControls.WebParts;
using System.Web.UI.HtmlControls;

/* 
 * Assignment 1 for Web Development Technologies COSC2276
 * Coded by Joseph Peter Landers
 * s3163776@student.rmit.edu.au
 * josephlanders@gmail.com
 *
 *
 * <summary>
 * Code behind file for product list page
 * </summary>
 */
public partial class ProductList_Detail : themedPage
{
    protected void Page_Load(object sender, EventArgs e)
    {
        Label LblError = (Label)Page.Master.FindControl("LblError");
        LblError.Text = "";
    }

    //Add the item to the shopping cart via session variable
    protected void addToCart(object sender, EventArgs e)
    {
        Label LblError = (Label)Page.Master.FindControl("LblError");
        LblError.Text = "";

        List<item> i;
        bool foundCart = false;

        //If a cart already exists
        if (Session["myCart"] != null)
        {
            //Get cart data
            i = (List<item>)Session["myCart"];

            //Find the item in the store
            foreach (item m in i)
            {
                //See if item already in cart
                if (m.Num == (Int32)Convert.ToInt32(GvProductList.SelectedDataKey.Value))
                {
                    foundCart = true;
                    break;
                }
            }

            //If item not in cart already
            if (foundCart == false)
            {
                //Check stock level is sufficient to add 1 item
                //Realistically you would want to update this in real time
                if ((Int32)Convert.ToInt32(GvProductList.SelectedRow.Cells[5].Text) >= 1)
                {
                    //Response.Write("Stock Level > 0");
                    //Add item to cart
                    i.Add(new item((Int32)Convert.ToInt32(GvProductList.SelectedDataKey.Value), (String)GvProductList.SelectedRow.Cells[2].Text, (Int32)Convert.ToInt32(GvProductList.SelectedRow.Cells[4].Text), (Int32)Convert.ToInt32(GvProductList.SelectedRow.Cells[5].Text), 1));
                }
                else
                {
                    //Insufficient stock
                    //Response.Write("Stock level is 0, can not add to cart");
                    LblError.Text = "Stock level is 0, can not add to cart";
                }
            }
            else
            {
                //Item already in cart do not add again
                //Response.Write("item already exists in cart, can not add again");
                LblError.Text = "Item already exists in cart, can not add again";
            }
        }
        else
        //No cart exists
        {
            //Create new cart
            i = new List<item>();


            //Check stock level is sufficient to add 1 item
            //Realistically you would want to update this in real time
            if ((Int32)Convert.ToInt32(GvProductList.SelectedRow.Cells[5].Text) >= 1)
            {
                
                //Response.Write("Stock Level > 0");

                //Add item to cart
                i.Add(new item((Int32)Convert.ToInt32(GvProductList.SelectedDataKey.Value), (String)GvProductList.SelectedRow.Cells[2].Text, (Int32)Convert.ToInt32(GvProductList.SelectedRow.Cells[4].Text), (Int32)Convert.ToInt32(GvProductList.SelectedRow.Cells[5].Text), 1));
            }
            else
            {
                //Insufficient stock
                //Response.Write("Stock level is 0, can not add to cart");
                LblError.Text = "Stock level is 0, can not add to cart";
            }
        }

        //Update cart after changes
        Session["myCart"] = i;

        //Refresh cart web user control
        try
        {
            UserControl mycartcontrol = (UserControl)this.Page.Master.FindControl("myCart");
            ICart mycart;
            mycart = (ICart)mycartcontrol;
            mycart.refresh();
        }
        catch { };
    }
}
