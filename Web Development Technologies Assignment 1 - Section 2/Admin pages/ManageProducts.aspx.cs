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
 * Code behind file for admin-manage products page
 * </summary>
 */
public partial class _Default : themedPage
{
    protected void Page_Load(object sender, EventArgs e)
    {
        //If user not authorised, redirect to homepage
        if ((Session["Username"] == null) || (((String)Session["UserType"]).CompareTo("Admin") != 0))
        {
            Response.Redirect("~/home.aspx");
        }
    }
    protected void GridView1_SelectedIndexChanged(object sender, EventArgs e)
    {
    }

    //Catch SQL errors when using objectdatasource
    protected void CatchError_ProductInserted(object sender, ObjectDataSourceStatusEventArgs e)
    {
        if (e.Exception != null)
        {
            LblStatus.Text = "An error occured whilst updating the database<br>"
                          + "1. Check ProductID is unique<br>"
                          + "2. Check CategoryID exists<br>"
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
                          + "1. Check ProductID is unique<br>"
                          + "2. Check CategoryID exists<br>"
                          + "No changes have been made";
            e.ExceptionHandled = true;
            return;
        }
        LblStatus.Text = "";
    }

    //When a row is selected in the gridview, update the dataview
    protected void RowSelect(object sender, EventArgs e)
    {
        DvProducts.PageIndex = GvProducts.SelectedIndex;
        LblStatus.Text = "";
    }

    protected void ItemCommand(object sender, EventArgs e)
    {
        LblStatus.Text = "";
    }

    /* Must use the business object to check if a Product exists with 
     * the same ID when inserting (check dataset != null)
     * must also check category exists */
    /*
    protected bool Validate_Product(bool InsertOrUpdate)
    {
        bool error = false;
        Label1.Text = "";
        
        String Name = ((TextBox)DetailsView1.FindControl("NameTextBox")).Text;
        String DisplaySeq = ((TextBox)DetailsView1.FindControl("DisplaySeqTextBox")).Text;
        String ParentProductID = ((TextBox)DetailsView1.FindControl("ParentProductIDTextBox")).Text;
        String CategoryID = ((TextBox)DetailsView1.FindControl("CategoryIDTextBox")).Text;
        String ProductGroupID = ((TextBox)DetailsView1.FindControl("ProductGroupIDTextBox")).Text;
        String Display = ((TextBox)DetailsView1.FindControl("DisplayTextBox")).Text;
        String ShortDescription = ((TextBox)DetailsView1.FindControl("ShortDescriptionTextBox")).Text;
        String LongDescription = ((TextBox)DetailsView1.FindControl("LongDescriptionTextBox")).Text;
        String Image = ((TextBox)DetailsView1.FindControl("ImageTextBox")).Text;


        if (InsertOrUpdate == true)
        {
            String ProductID = ((TextBox)DetailsView1.FindControl("ProductIDTextBox")).Text;
            if (checkNotNull(ProductID) == false)
            {
                Label1.Text += "*ProductID must not be null<br>";
                error = true;
            }
            else
            {
                if (checkInt32(ProductID) != true)
                {
                    Label1.Text += "*ProductID must be an Int32<br>";
                    error = true;
                } else {
                    if (checkNotNegative(ProductID) != true) {
                        Label1.Text += "*ProductID must not be negative <br>";
                        error = true;
                    }
                }
            }
        }

        if (checkInt16(DisplaySeq) != true)
        {
            Label1.Text += "*DisplaySeq must be an Int16<br>";
            error = true;
        }
        else
        {
            if (checkNotNegative(DisplaySeq) != true)
            {
                Label1.Text += "*DisplaySeq must not be negative <br>";
                error = true;
            }
        }

        if (checkInt32(ParentProductID) != true)
        {
            Label1.Text += "*ParentProductID must be an Int32<br>";
            error = true;
        }
        else
        {
            if (checkNotNegative(ParentProductID) != true)
            {
                Label1.Text += "*ParentProductID must not be negative <br>";
                error = true;
            }
        }

        if (checkInt16(Display) != true)
        {
            Label1.Text += "*Display must be an Int16<br>";
            error = true;
        }
        else
        {
            if (checkNotNegative(Display) != true)
            {
                Label1.Text += "*Display must not be negative <br>";
                error = true;
            }
        }

        if (checkNotNull(CategoryID) == false)
        {
            Label1.Text += "*CategoryID must not be null<br>";
            error = true;
        }
        else
        {
            if (checkInt32(CategoryID) != true)
            {
                Label1.Text += "*CategoryID must be an Int32<br>";
                error = true;
            }
            else
            {
                if ((CategoryID != "1") && (CategoryID != "2"))
                {
                    Label1.Text += "*CategoryID must be 1 (books) or 2 (DVDs)<br>";
                    error = true;
                }
            }
        }

        if (checkInt32(ProductGroupID) != true)
        {
            Label1.Text += "*ProductGroupID must be an Int32<br>";
            error = true;
        }
        else
        {
            if (checkNotNegative(ProductGroupID) != true)
            {
                Label1.Text += "*ProductGroupID must not be negative <br>";
                error = true;
            }
        }

        if (checkLength(Name, 25) == false)
        {
            Label1.Text += "*Name must be <= 25 characters<br>";
            error = true;
        }

        if (checkLength(ShortDescription, 50) == false)
        {
            Label1.Text += "*ShortDescription must be <= 50 characters<br>";
            error = true;
        }

        if (checkLength(LongDescription, 500) == false)
        {
            Label1.Text += "*LongDescription must be <= 500 characters<br>";
            error = true;
        }

        if (checkLength(Image, 50) == false)
        {
            Label1.Text += "*Image must be <= 50 characters<br>";
            error = true;
        }

        Response.Write(!error);
        return !error;

    }

    
    private bool checkLength(String s, int l)
    {
        if (s.Length > l)
        {
            return false;
        }
        return true;
    }

    private bool checkNotNull (String s)
    {
        if (s.Equals("") == true)
        {
            return false;
        }
        return true;
    }

    private bool checkInt16(String s)
    {
        try
        {

            Convert.ToInt16(s);
        }
        catch
        {            
            return false;
        }
        return true;
    }

    private bool checkInt32(String s)
    {
        try
        {
            Convert.ToInt32(s);
        }
        catch
        {            
            return false;
        }
        return true;
    }

    private bool checkNotNegative(String i)
    {
        try
        {
            if (Convert.ToInt64(i) < 0)
            {
                return false;
            }
        }
        catch
        {
            return false;
        }
        return true;
    }
    
    protected void ItemCommand(object sender, EventArgs e)
    {
        Label1.Text = "";
    }
    */
}
