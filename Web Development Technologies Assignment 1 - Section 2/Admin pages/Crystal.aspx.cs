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
using CrystalDecisions.Shared; 
using CrystalDecisions.CrystalReports.Engine;

/* 
 * Assignment 1 for Web Development Technologies COSC2276
 * Coded by Joseph Peter Landers
 * s3163776@student.rmit.edu.au
 * josephlanders@gmail.com
 *
 *
 * <summary>
 * Code behind file for my crystal report
 * </summary>
 */
public partial class _Default : themedPage
{
    protected void Page_Load(object sender, EventArgs e)
    {
        //If user not authorised, redirect to homepage
        if (((String)Session["Username"] == null) || (((String)Session["UserType"]).CompareTo("Admin") != 0))
        {
            Response.Redirect("~/home.aspx");
        }
    }

    protected void CrystalReportViewer1_Init(object sender, EventArgs e)
    {
    }
}
