using System;
using System.Data;
using System.Configuration;
using System.Web;
using System.Web.Security;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Web.UI.WebControls.WebParts;
using System.Web.UI.HtmlControls;

/// <summary>
/// Summary description for themedPage
/// </summary>
public class themedPage : Page
{
	public themedPage()
	{
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
