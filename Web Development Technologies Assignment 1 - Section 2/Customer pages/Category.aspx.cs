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
public partial class Category : themedPage
{
    protected void Page_Load(object sender, EventArgs e)
    {

    }

    private String category_;

    public String category
    {
        get
        {
            return category_;
        }
        set
        {
            category_ = value;
        }
    }

    private String categoryname_;

    public String categoryname
    {
        get
        {
            return categoryname_;
        }
        set
        {
            categoryname_ = value;
        }
    }

    /* When the user clicks a category link 
     * we set the category session variable and redirect */
    protected void setCategory(object sender, EventArgs e)
    {
       category = ((LinkButton)sender).CommandArgument;
       categoryname = ((LinkButton)sender).Text;
       
       Session["CategoryID"] = category;
       Session["CategoryName"] = categoryname;

        //NOTE MUST DO REDIRECT HERE otherwise session vars not set
        //DO NOT DO REDIRECT IN THE ASP TAG

       Response.Redirect("~/Customer pages/ProductList_Detail.aspx");
    }
}
