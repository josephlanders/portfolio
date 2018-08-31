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
 * Code behind file for shopping cart web user control
 * </summary>
 */
public partial class WebUserControl4 : System.Web.UI.UserControl, ICart
{
    protected void Page_Load(object sender, EventArgs e)
    {
        LblError.Text = "";

        //If we are on a checkout page, disable the cart
        if (this.Page is ICheckOut)
        {
            GvCart.Enabled = false;
        }
        else
        {
            GvCart.Enabled = true;
        }
        refresh();
    }

    public void refresh()
    {
        List<item> mycart = (List<item>) (HttpContext.Current.Session["myCart"]);

        //If cart is not empty
        if (mycart != null)
        {
            //add cart items to gridview
            DataTable dt = new DataTable();

            dt.Columns.Add("ProductID", System.Type.GetType("System.Int32"));
            dt.Columns.Add("Name", System.Type.GetType("System.String"));
            dt.Columns.Add("Price", System.Type.GetType("System.Int32"));
            dt.Columns.Add("Quantity", System.Type.GetType("System.Int32"));
            double total = 0.0;
            foreach (item j in mycart)
            {
                try
                {
                    total += (j.Quantity * j.Price);
                }
                catch { }
                dt.Rows.Add(j.Num, j.Name, j.Price, j.Quantity);
            }
            LblTotal.Text = total.ToString();

            //bind cart to datatable
            GvCart.DataSource = dt;
            GvCart.DataBind();

            if (mycart.Count == 0)
            {
                LnkCheckout.Visible = false;
            }
            else
            {
                LnkCheckout.Visible = true;
            }
        }
        else
        {
            LnkCheckout.Visible = false;
        }
    }

    //Update an items quantity when user clicks x, +, -
    protected void rowCommand(object sender, GridViewCommandEventArgs e)
    {
        //Get the row number
        int row = Convert.ToInt32(e.CommandArgument);

        //Get the primary key value of that row
        Int32 ProductID = Convert.ToInt32(GvCart.DataKeys[row].Value);

        //Get the cart
        List<item> myCart = (List<item>)(HttpContext.Current.Session["myCart"]);

        //Iterate through items in the cart
        foreach (item j in myCart)
        {
            //Find item by key
            if (j.Num == ProductID)
            {
                //update item

                //Add 1 to quantity if stocklevel permits and refresh
                if (e.CommandName.CompareTo("+") == 0)
                {
                    if ((j.Quantity + 1) <= j.Stock)
                    {
                        //get current index of item
                        int index = myCart.IndexOf(j);
                        myCart.Remove(j);

                        //Put updated item back at index if qty - 1 > 0
                        myCart.Insert(index, new item(j.Num, j.Name, j.Price, j.Stock, j.Quantity + 1));

                    }
                    else
                    { //no more stock
                        LblError.Text = "Can't +1, insufficient stock";
                    }
                    break;
                }
                //Remove 1 from quantity, if qty 1 then don't do anything
                else if (e.CommandName.CompareTo("-") == 0)
                {
                    //Put updated item back at index if qty - 1 > 0
                    if ((j.Quantity - 1) > 0)
                    {
                        //get current index of item
                        int index = myCart.IndexOf(j);
                        myCart.Remove(j);
                        myCart.Insert(index, new item(j.Num, j.Name, j.Price, j.Stock, j.Quantity - 1));
                    }
                    else
                    { //qty is 1, advise user
                        LblError.Text = "Can't -1 as qty will be 0, remove item instead";
                    }
                    break;

                }
                //Remove item
                else if (e.CommandName.CompareTo("x") == 0)
                {
                    myCart.Remove(j);
                    break;
                }
            }
        }

        //Update cart
        HttpContext.Current.Session["myCart"] = myCart;

        //Refresh the cart
        refresh();
    }
}
