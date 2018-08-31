using System;
using System.Data;
using System.Configuration;
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
 * <summary>
 * Used by login pages.
 * Login controls can force update of login pages
 * They do this to ensure login control and login page have same info
 * </summary>
 */
public interface ILogin
{
    //Refresh the login
    void refresh();
}
