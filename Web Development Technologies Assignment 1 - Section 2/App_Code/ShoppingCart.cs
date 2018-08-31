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
 *
 * <summary>
 * item is a data structure used to store info about CDs/DVDs
 * in the cart 
 * </summary>
 */
public class item
{
    //Define vars
    private int recordNumber = 0;
    private String itemName = "";
    private int pricePerItem = 0;
    private int stockLevel = 0;
    private int quantityAvailable = 0;

    public item()
    {
    }

    //Overloaded constructor
    public item(Int32 n, String i, Int32 p, Int32 s, Int32 q)
    {
        this.recordNumber = n;
        this.itemName = i;
        this.pricePerItem = p;
        this.stockLevel = s;
        this.Quantity = q;
    }

    public String Name
    {
        get { return itemName; }
        set { itemName = value; }
    }

    public Int32 Price
    {
        get { return pricePerItem; }
        set { pricePerItem = value; }
    }

    public Int32 Num
    {
        get { return recordNumber; }
        set { recordNumber = value; }
    }

    public Int32 Stock
    {
        get { return stockLevel; }
        set { stockLevel = value; }
    }

    public Int32 Quantity
    {
        get { return quantityAvailable; }
        set { quantityAvailable = value; }
    }

}
