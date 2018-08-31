<%@ Control Language="C#" AutoEventWireup="true" CodeFile="ShoppingCart.ascx.cs" Inherits="WebUserControl4" %>
<%@ Register Assembly="System.Web.Extensions, Version=1.0.61025.0, Culture=neutral, PublicKeyToken=31bf3856ad364e35"
    Namespace="System.Web.UI" TagPrefix="asp" %>
<div style="WIDTH: 100%; HEIGHT: 100%; overflow: auto;">
    <asp:Label ID="LblCart" runat="server" Text="Shopping Cart:"></asp:Label><br />
<asp:LinkButton ID="LnkCheckout" runat="server" Visible="False" PostBackUrl="~/Customer pages/ReviewOrder.aspx" CausesValidation="False">Checkout</asp:LinkButton><br />
<asp:GridView ID="GvCart" runat="server" AutoGenerateColumns="False"  DataKeyNames="ProductID" OnRowCommand="rowCommand">
    <Columns>
        <asp:ButtonField Text="Delete" CommandName="x" />
        <asp:BoundField HeaderText="Name" DataField="Name" />
        <asp:BoundField HeaderText="$ ea" DataField="Price" />
        <asp:BoundField HeaderText="Qty" DataField="Quantity" />
        <asp:ButtonField Text="+1" CommandName="+" />
        <asp:ButtonField Text="-1" CommandName="-" />
    </Columns>
    <EmptyDataTemplate>
        Empty
    </EmptyDataTemplate>
</asp:GridView>
<asp:Label ID="LblError" runat="server" Text="" ForeColor="red"></asp:Label>
&nbsp;&nbsp;
<br />
<asp:Label ID="LblTotalText" runat="server" Text="Total: $"></asp:Label>&nbsp;
<asp:Label ID="LblTotal" runat="server" Text="0"></asp:Label> </div>