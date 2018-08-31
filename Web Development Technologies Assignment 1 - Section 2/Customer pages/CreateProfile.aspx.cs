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
 * Code behind file for category page
 * </summary>
 */
public partial class CreateProfile : themedPage, ILogin
{
    protected void Page_Load(object sender, EventArgs e)
    {
        refresh();
    }

    //Catch errors with SQL query with objectdatasource
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
        else
        {
            LblStatus.Text = "Succesfully created new user";
            DvProfile.Visible = false;
        }
    }

    //Catch errors with SQL query with objectdatasource
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
        else
        {
            LblStatus.Text = "Operation Successful";
            DvProfile.Visible = false;
        }
    }

    protected void ItemCommand(object sender, EventArgs e)
    {
        LblStatus.Text = "";
    }

    //If user is logged in then we ask them to log out first
    public void refresh()
    {
        if (Session["Username"] != null)
        {
            DvProfile.Visible = false;
            ValidationSummary1.Visible = false;

            LblInfo.Visible = true;
        }
        else
        {
            DvProfile.Visible = true;
            ValidationSummary1.Visible = true;

            LblInfo.Visible = false;
        }
    }
}
