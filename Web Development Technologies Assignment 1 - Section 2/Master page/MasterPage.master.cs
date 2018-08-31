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
 * Code behind file for master page
 * </summary>
 */
public partial class MasterPage : System.Web.UI.MasterPage
{
    protected void Page_Load(object sender, EventArgs e)
    {
        //Do not cache stuff
        Response.Cache.SetCacheability(HttpCacheability.NoCache);

        //Show the date time in the footer
        LblDateTime.Text = (String) DateTime.Now.ToString();
    }

    protected void Page_PreInit(object sender, EventArgs e)
    {
        //Apply theme depending on type of user
        String UserType = (String)Session["UserType"];
        if ((UserType != null) && (UserType.CompareTo("Admin") == 0))
        {
            //Admin user theme
            Page.Theme = "AdminTheme";
        }
        else
        {
            //Customer theme or logged out user
            Page.Theme = "MainTheme";
        }
    }
}
