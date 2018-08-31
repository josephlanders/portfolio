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
 * Code behind file for payment page
 * </summary>
 */
public partial class Payment : themedPage, ICheckOut
{
    //When the page loads, we want to show the total amount due
    protected void Page_Load(object sender, EventArgs e)
    {
        String userType = (String)Session["UserType"];

        //If user logged in
        if (userType != null)
        {
        }
        else
        //User not logged in
        {
            //Not logged in
            Response.Redirect("~/home.aspx");
        }

        double total = (double) Convert.ToDouble(Session["Total"]);

        //Standard rate
        if (((String)Session["Shipping"]).CompareTo("Standard") == 0)
        {
            total = total + 2.5;

        }
        else
        //overnight rate
        {
            total = total + 10;
        }

        LblTotal.Text = total.ToString();
    }

    //When payment is clicked we want to authenticate the CC
    protected void LnkPayment_Click(object sender, EventArgs e)
    {
        //Validate page first
        Page.Validate("vg1");
        if (IsValid)
        {
            com.imacination.webservices.ValidateService webservice = new com.imacination.webservices.ValidateService();

            //Concat CC num
            String CCNum = "";
            CCNum = TxtCardNo1.Text
                     + "-"
                     + TxtCardNo2.Text
                     + "-"
                     + TxtCardNo3.Text
                     + "-"
                     + TxtCardNo4.Text;

            //Concat CC Exp
            String CCExp = "";
            CCExp = DDLExp1.Text
                     + "/"
                     + DDLExp2.Text;
            /*
                        //If valid, go to next page
                        if (webservice.validateCard(CCNum, CCExp))
                        //Test number is "4012-8888-8888-1881"
                        {
                            Response.Write("Card number + expiry are valid");

                            //Note we keep CC details because it's manual processing
                            Session["CardholdersName"] = TxtCardholdersName.Text;
                            Session["CardType"] = DDLCardType.Text;
                            Session["CardNo"] = CCNum;
                            Session["CardNo4"] = TxtCardNo4.Text;
                            Session["CardExp"] = CCExp;
            
                            //Redirect to summary page
                            Response.Redirect("~/Customer pages/TransactionSummary.aspx");
                
                        }
                        else
                        //CC invalid
                        {
                            Response.Write("Card number + expiry are invalid");
                        }
             */

            //Redirect to summary page - DELETE THIS CODE IF USING WEB SERVICE
            Response.Redirect("~/Customer pages/TransactionSummary.aspx");
        }
    }
}
