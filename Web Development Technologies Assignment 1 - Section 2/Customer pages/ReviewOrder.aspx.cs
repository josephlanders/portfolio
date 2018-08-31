using System;
using System.Data;
using System.Data.SqlClient;
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
 * Code behind file for review order page
 * </summary>
 */
public partial class ReviewOrder : themedPage, ICheckOut, ILogin
{
    //if logged in show order
    public void showloggedin()
    {
        LblLogin.Visible = false;
        LblUsername.Visible = false;
        LblPassword.Visible = false;
        TxtUsername.Visible = false;
        TxtPassword.Visible = false;
        BtnSubmit.Visible = false;

        LblReview.Visible = true;
        GvReview.Visible = true;
        LblTotalText.Visible = true;
        LblTotal.Visible = true;
        LnkProducts.Visible = true;
        LnkShipBill.Visible = true;

        Label LblError = (Label)Page.Master.FindControl("LblError");
        LblError.Text = "";

    }

    //if not logged in show login prompt
    public void showlogin()
    {
        LblLogin.Visible = true;
        LblUsername.Visible = true;
        LblPassword.Visible = true;
        TxtUsername.Visible = true;
        TxtPassword.Visible = true;
        BtnSubmit.Visible = true;

        LblReview.Visible = false;
        GvReview.Visible = false;
        LblTotalText.Visible = false;
        LblTotal.Visible = false;
        LnkProducts.Visible = false;
        LnkShipBill.Visible = false;

        Label LblError = (Label)Page.Master.FindControl("LblError");
        LblError.Text = "";
    }

    //User clicks login - try to authenticate
    protected void BtnSubmit_Click(object sender, EventArgs e)
    {
        SqlConnection conn = null;
        SqlDataReader rdr = null;
        SqlCommand cmd = null;
        Label LblError = (Label)Page.Master.FindControl("LblError");

        try
        {
            //Create SQL connection
            conn = new SqlConnection("Server=(local);DataBase=jlanders;Integrated Security=SSPI");

            //Create the SQL command
            cmd = new SqlCommand("dbo.GetUser", conn);

            cmd.CommandType = CommandType.StoredProcedure;
            cmd.Parameters.Add(new SqlParameter("@Username", TxtUsername.Text));

            conn.Open();

            // execute the command
            rdr = cmd.ExecuteReader();

            // iterate through results
            while (rdr.Read())
            {
                if (rdr["Password"].Equals(TxtPassword.Text))
                {
                    //user authenticated 
                    Session["Username"] = TxtUsername.Text;
                    Session["UserType"] = rdr["UserType"];

                    //Refresh login web user control
                    try
                    {
                        UserControl loginControl = (UserControl)this.Page.Master.FindControl("myLogin");
                        ILogin loginInterface;
                        loginInterface = (ILogin)loginControl;
                        loginInterface.refresh();
                    }
                    catch { };

                    //Show logged in screen
                    showloggedin();

                    LblError.Text = "";

                    //show order
                    reviewOrder();
                    Response.Redirect("~/Customer pages/ReviewOrder.aspx");
                    return; //exits the while
                }
            }

            LblError.Text = "Username / password incorrect";
        }
        finally
        {
            if (conn != null)
            {
                conn.Close();
            }
            if (rdr != null)
            {
                rdr.Close();
            }
        }   
    }

    protected void Page_Init(object sender, EventArgs e)
    {
        
    }

    protected void Page_Load(object sender, EventArgs e)
    {
        refresh();
    }

    public void refresh()
    {
        String userType = (String)Session["UserType"];

        //If user logged in
        if (userType != null)
        {
            showloggedin();
            reviewOrder();
        }
        else
        //User not logged in
        {
            //Not logged in
            showlogin();
        }
    }

    protected void reviewOrder()
    {
        List<item> myCart = (List<item>)(HttpContext.Current.Session["myCart"]);

        //If cart is not empty
        if (myCart != null)
        {
            //add cart items to gridview
            DataTable dt = new DataTable();

            dt.Columns.Add("ProductID", System.Type.GetType("System.Int32"));
            dt.Columns.Add("Name", System.Type.GetType("System.String"));
            dt.Columns.Add("Price", System.Type.GetType("System.Int32"));
            dt.Columns.Add("Quantity", System.Type.GetType("System.Int32"));
            double total = 0.0;

            foreach (item j in myCart)
            {
                try
                {
                    total += (j.Quantity * j.Price);
                }
                catch { }
                dt.Rows.Add(j.Num, j.Name, j.Price, j.Quantity);
            }
            LblTotal.Text = total.ToString();
            Session["Total"] = total.ToString();
            GvReview.DataSource = dt;
            GvReview.DataBind();
            if (myCart.Count == 0)
            {
                LnkShipBill.Visible = false;
                //LnkCheckout.Visible = false;
            }
            else
            {
                LnkShipBill.Visible = true;
                //LnkCheckout.Visible = true;
            }
        }
        else
        {
            LnkShipBill.Visible = false;
            //LnkCheckout.Visible = false;
        }
    }
}
