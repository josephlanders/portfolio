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
 * Code behind file for admin page
 * </summary>
 */
public partial class _Default : themedPage, ILogin
{
    //Show navigation options for logged in users
    public void showloggedin()
    {
        LnkProfiles.Visible = true;
        LnkProducts.Visible = true;
        LnkCategories.Visible = true;
        LblProfiles.Visible = true;
        LblProducts.Visible = true;
        LblCategories.Visible = true;
        LblTitle.Visible = true;
        LnkCrystal.Visible = true;

        LblUsername.Visible = false;
        LblPassword.Visible = false;
        TxtUsername.Visible = false;
        TxtPassword.Visible = false;
        BtnSubmit.Visible = false;
        LblTitle.Visible = false;

        Label LblError = (Label)Page.Master.FindControl("LblError");
        LblError.Text = "";
    }

    //Show admin log in prompt
    public void showlogin()
    {
        LnkProfiles.Visible = false;
        LnkProducts.Visible = false;
        LnkCategories.Visible = false;
        LblProfiles.Visible = false;
        LblProducts.Visible = false;
        LblCategories.Visible = false;
        LblTitle.Visible = false;
        LnkCrystal.Visible = false;

        LblUsername.Visible = true;
        LblPassword.Visible = true;
        TxtUsername.Visible = true;
        TxtPassword.Visible = true;
        BtnSubmit.Visible = true;
        LblTitle.Visible = true;

        Label LblError = (Label)Page.Master.FindControl("LblError");
        LblError.Text = "";
    }

    //Submit button logs user in
    protected void BtnSubmit_Click(object sender, EventArgs e)
    {
        SqlConnection conn = null;
        SqlDataReader rdr = null;
        Label LblError = (Label)Page.Master.FindControl("LblError");

        try
        {
            //open SQL connection
            conn = new SqlConnection("Server=(local);DataBase=jlanders;Integrated Security=SSPI");

            //Create SQL command
            SqlCommand cmd = new SqlCommand("dbo.GetUser", conn);

            cmd.CommandType = CommandType.StoredProcedure;
            cmd.Parameters.Add(new SqlParameter("@Username", TxtUsername.Text));

            conn.Open();

            //Execute command
            rdr = cmd.ExecuteReader();

            // iterate through results
            while (rdr.Read())
            {
                if (rdr["UserType"].Equals("Admin"))
                {
                    //rdr["Username"].Equals(TxtUsername.Text) NOT NEEDED
                    if (rdr["Password"].Equals(TxtPassword.Text))
                    {
                        //user authenticated as admin
                        Session["Username"] = TxtUsername.Text;
                        Session["UserType"] = "Admin";

                        //Response.Write("Username / password correct");

                        //Refresh web user login control
                        try
                        {
                            UserControl loginControl = (UserControl)this.Page.Master.FindControl("myLogin");
                            ILogin loginInterface;
                            loginInterface = (ILogin)loginControl;
                            loginInterface.refresh();
                        }
                        catch { };

                        //show the user the navigation options
                        showloggedin();

                        LblError.Text = "";
                        Response.Redirect("~/Admin pages/admin.aspx");
                        return; // break out of while loop
                    }

                }
                else
                {
                    //Show user the login prompt
                    showlogin();

                    LblError.Text = "You do not have admin privileges, Access Denied";
                    return; // break out of while loop
                }
            }

            //Show user the login prompt
            showlogin();

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
        refresh();
    }

    protected void Page_Load(object sender, EventArgs e)
    {
        //refresh();
    }

    public void refresh()
    {
        String userType = (String)Session["UserType"];

        //If Logged in
        if (userType != null)
        {
            //If admin, show admin options
            if (Session["UserType"].ToString().CompareTo("Admin") == 0)
            {
                showloggedin();
                return;
            }
            //If not admin, show login
            else
            {
                showlogin();
                return;
            }
        }
        else
        //Not logged in
        {
            //Not logged in
            showlogin();
        }
    }
}
