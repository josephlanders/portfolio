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
using ProductClass;

/* 
 * Assignment 1 for Web Development Technologies COSC2276
 * Coded by Joseph Peter Landers
 * s3163776@student.rmit.edu.au
 * josephlanders@gmail.com
 *
 *
 * <summary>
 * Code behind file for transaction summary page
 * </summary>
 */
public partial class TransactionSummary : themedPage
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


        if (!IsPostBack)
        {
            double total = 0;
            double shipping = 0;

            //Show the order summary
            reviewOrder();

            try
            {
                total = (Double)Convert.ToDouble(Session["Total"]);
            }
            catch { };

            LblTotal.Text = Convert.ToString(total);
            //Standard shipping
            if (((String)Session["Shipping"]).CompareTo("Standard") == 0)
            {
                LblShipping.Text = "2.50";
            }
            else
            //Overnight shopping
            {
                LblShipping.Text = "10";
            }

            shipping = Convert.ToDouble(LblShipping.Text);

            LblGrandTotal.Text = Convert.ToString(total + shipping);
            LblShipTo.Text = (String)Session["ShipTo"];
            LblAddressLine1.Text = (String)Session["AddressLine1"];
            LblPostCode.Text = (String)Session["PostCode"];
            LblCardHoldersName.Text = (String)Session["CardholdersName"];
            LblCardNo4.Text = (String)Session["CardNo4"];

            //NOW WE SHOULD REALLY UPDATE THE DATABASE AND CHANGE STOCK LEVELS
            ProductClass.ProductClass p = new ProductClass.ProductClass();

            //CODE TO UPDATE THE DATABASE
            List<item> myCart = (List<item>)(HttpContext.Current.Session["myCart"]);

            if (myCart != null)
            {
                foreach (item j in myCart)
                {
                    p.InsertOrder((String)Session["Username"],
                    Convert.ToString(j.Num),
                    Convert.ToString(j.Quantity),
                    Convert.ToString(j.Price),
                    (String)Session["ShipTo"],
                    ((String)Session["AddressLine1"]
                    + (String)Session["AddressLine2"]
                    + (String)Session["Suburb"]
                    + (String)Session["City"]
                    + (String)Session["State"]
                    + (String)Session["PostCode"]),
                    (String)Session["Shipping"]);
                    p.UpdateProductStockLevel(Convert.ToString(j.Num), Convert.ToString(j.Stock - j.Quantity));

                }
            }

            //CLEAR CART
            String Username = (String)Session["Username"];
            String UserType = (String)Session["UserType"];
            Session.Clear();
            Session.Abandon();
            Session["Username"] = Username;
            Session["UserType"] = UserType;

            //Refresh cart
            UserControl mycartControl = (UserControl)this.Page.Master.FindControl("myCart");
            ICart mycartInterface;
            mycartInterface = (ICart)mycartControl;
            mycartInterface.refresh();
        }
        else
        {
        }
    }
    protected void BtnLogout_Click(object sender, EventArgs e)
    {
        Session.Clear();
        Session.Abandon();
        Response.Redirect("~/home.aspx");
    }

    //Show the order summary
    protected void reviewOrder()
    {
        List<item> myCart = (List<item>)(HttpContext.Current.Session["myCart"]);

        if (myCart != null)
        {
            //add cart items to gridview
            DataTable dt = new DataTable();

            dt.Columns.Add("ProductID", System.Type.GetType("System.Int32"));
            dt.Columns.Add("Name", System.Type.GetType("System.String"));
            dt.Columns.Add("Price", System.Type.GetType("System.Int32"));
            dt.Columns.Add("Quantity", System.Type.GetType("System.Int32"));

            foreach (item j in myCart)
            {
                dt.Rows.Add(j.Num, j.Name, j.Price, j.Quantity);
            }

            //bind the datatable to the gridview
            GvReview.DataSource = dt;
            GvReview.DataBind();
        }
    }
}
