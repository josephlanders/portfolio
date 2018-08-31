using System;
using System.Data;
using System.Configuration;
using System.Collections;
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
 * Code behind file for shipping and billing page
 * </summary>
 */
public partial class ShipBill : themedPage, ICheckOut
{
    protected void Page_Load(object sender, EventArgs e)
    {
        String userType = (String)Session["UserType"];

        //If user logged in
        if (userType != null)
        {
        }
        else
        //User not logged in
        {
            //Not logged in
            Response.Redirect("~/home.aspx");
        }
    }

    //When user clicks link take them to payment screen
    protected void LnkPayment_Click(object sender, EventArgs e)
    {
        //Validate page
        Page.Validate("vg1");
        if (Page.IsValid)
        {
            //Set session variables
            Session["ShipTo"] = TxtShipTo.Text;
            Session["AddressLine1"] = TxtAddressLine1.Text;
            Session["AddressLine2"] = TxtAddressLine2.Text;
            Session["Suburb"] = TxtSuburb.Text;
            Session["City"] = TxtCity.Text;
            Session["State"] = DDLState.SelectedValue;
            Session["PostCode"] = TxtPost.Text;
            Session["Shipping"] = DDLShipping.SelectedValue;
            //Session["ShippingName"] = DDLShipping.;

            Response.Redirect("~/Customer pages/Payment.aspx");
        }
        else
        {
            //Response.Write(Page.IsValid);
        }
    }
}
