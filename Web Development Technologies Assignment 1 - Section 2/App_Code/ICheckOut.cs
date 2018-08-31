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
 * This is an interface, all checkout pages inherit this.
 * It is used by the shopping cart
 * The shopping cart disables itself if we are on a checkout page
 * to prevent updates to the cart
 * </summary>
 */
public interface ICheckOut
{
}
