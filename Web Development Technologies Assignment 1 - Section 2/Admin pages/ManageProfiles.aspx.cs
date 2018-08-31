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
 * Code behind file for admin-manage profiles page
 * </summary>
 */
public partial class _Default : themedPage
{
    protected void Page_Load(object sender, EventArgs e)
    {
        //If user not authorised, redirect to homepage
        if ( ((String) Session["Username"] == null) || (((String)Session["UserType"]).CompareTo("Admin") != 0))
        {
            Response.Redirect("~/home.aspx");
        }
    }

    //Catch SQL errors when using objectdatasource
    protected void CatchError_ProductInserted(object sender, ObjectDataSourceStatusEventArgs e)
    {
        if (e.Exception != null)
        {
            LblStatus.Text = "An error occured whilst updating the database<br>"
                          + "1. Check Username is unique<br>"
                          + "No changes have been made";
            e.ExceptionHandled = true;
            return;
        }
        LblStatus.Text = "";
    }

    //Catch SQL errors when using objectdatasource
    protected void CatchError_ProductUpdated(object sender, ObjectDataSourceStatusEventArgs e)
    {
        if (e.Exception != null)
        {
            LblStatus.Text = "An error occured whilst updating the database<br>"
                          + "1. Check Username is unique<br>"
                          + "No changes have been made";
            e.ExceptionHandled = true;
            return;
        }
        LblStatus.Text = "";
    }

    //When a row is selected in the gridview, update the dataview
    protected void RowSelect(object sender, EventArgs e)
    {
        DvProfiles.PageIndex = GvProfiles.SelectedIndex;
        LblStatus.Text = "";
    }

    protected void ItemCommand(object sender, EventArgs e)
    {
        LblStatus.Text = "";
    }
}
