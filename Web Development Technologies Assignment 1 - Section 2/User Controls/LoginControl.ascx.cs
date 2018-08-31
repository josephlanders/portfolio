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
using System.Data.SqlClient;

/* 
 * Assignment 1 for Web Development Technologies COSC2276
 * Coded by Joseph Peter Landers
 * s3163776@student.rmit.edu.au
 * josephlanders@gmail.com
 *
 *
 * <summary>
 * Code behind file for login web user control
 * </summary>
 */
public partial class WebUserControl2 : System.Web.UI.UserControl, ILogin
{
    public void refresh()
    {
        String a = (String)HttpContext.Current.Session["UserType"];
        //If user is logged in
        if (a != null)
        {
            //Show logout
            showloggedin();
        }
        else
        {
            //Not logged in, show login
            showlogin();
            LblError.Text = "";
        }
    }

    public void showloggedin()
    {
        LblUsername.Visible = false;
        LblPassword.Visible = false;
        TxtUsername.Visible = false;
        TxtPassword.Visible = false;
        BtnLogin.Text = "Logout";
        LblLoggedInUsername.Text = "Logged in as: " + HttpContext.Current.Session["Username"].ToString();
        LblLoggedInUsername.Visible = true;
        LblError.Text = "";
    }

    public void showlogin()
    {
        LblUsername.Visible = true;
        LblPassword.Visible = true;
        TxtUsername.Visible = true;
        TxtPassword.Visible = true;
        TxtUsername.Text = "";
        TxtPassword.Text = "";
        BtnLogin.Text = "Login";
        LblLoggedInUsername.Text = "";
        LblLoggedInUsername.Visible = false;
        LblError.Text = "";
    }

    protected void Page_Load(object sender, EventArgs e)
    {
    }

    protected void Page_Init(object sender, EventArgs e)
    {
        refresh();
    }

    //Performs login
    protected void BtnLogin_Click1(object sender, EventArgs e)
    {
        bool authenticated = false;
        //If user wants to login
        if (BtnLogin.Text != "Logout")
        {
            SqlConnection conn = null;
            SqlDataReader rdr = null;

            try
            {
                // Open SQL connection
                conn = new
                    SqlConnection("Server=(local);DataBase=jlanders;Integrated Security=SSPI");

                // Create SQL command
                SqlCommand cmd = new SqlCommand("dbo.GetUser", conn);

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
                        HttpContext.Current.Session["Username"] = TxtUsername.Text;
                        HttpContext.Current.Session["UserType"] = rdr["UserType"];

                        //Response.Write("Username / password correct");

                        //Refresh login page
                        if (this.Page is ILogin)
                        {
                            ILogin ef;
                            ef = (ILogin)this.Page;
                            ef.refresh();
                        }
                        else
                        {
                        }
                        //Show logout
                        authenticated = true;
                        showloggedin();
                        Response.Redirect("~/home.aspx");
                    }
                }

                if (authenticated == false)
                {
                        //Show login
                        showlogin();

                        LblError.Text = "Invalid Credentials";
                }
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
        else
        {
            //User wants to logout so clear session variables
            HttpContext.Current.Session.RemoveAll();
            HttpContext.Current.Session.Clear();
            HttpContext.Current.Session.Abandon();

            //If page is a login page, refresh it
            if (this.Page is ILogin)
            {
                ILogin loginPage;
                loginPage = (ILogin)this.Page;
                loginPage.refresh();
            }

            //Refresh shopping carts due to session variables being cleared
            UserControl mycartControl = (UserControl)this.Page.Master.FindControl("mycart");
            ICart mycartInterface;
            mycartInterface = (ICart)mycartControl;
            mycartInterface.refresh();

            //Show login prompt
            showlogin();

            //Redirect to home page!! otherwise all pages that rely on session vars will be broken
            Response.Redirect("~/home.aspx");
        }
    }
}