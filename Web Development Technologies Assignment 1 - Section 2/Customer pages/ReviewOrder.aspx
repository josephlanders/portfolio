<%@ Page Language="C#" MasterPageFile="~/Master page/MasterPage.master" AutoEventWireup="true" CodeFile="ReviewOrder.aspx.cs" Inherits="ReviewOrder" Title="Untitled Page" %>

<%@ Register Assembly="System.Web.Extensions, Version=1.0.61025.0, Culture=neutral, PublicKeyToken=31bf3856ad364e35"
    Namespace="System.Web.UI" TagPrefix="asp" %>
<asp:Content ID="Content1" ContentPlaceHolderID="ContentPlaceHolder1" Runat="Server">
<div id="revieworder">
    <div id="revieworderloginform">
        <asp:Label CssClass="revieworder0" id="LblLogin" runat="server" Text="Cart Login" />
        <div id="revieworderusername">
            <asp:Label CssClass="revieworder1" id="LblUsername" runat="server" Text="Username:"/>
            <asp:TextBox CssClass="revieworder2" id="TxtUsername" runat="server" />
            <asp:RequiredFieldValidator CssClass="revieworder3" ID="RequiredFieldValidator1" runat="server" ControlToValidate="TxtUsername"
                          ErrorMessage="Must not be empty" ValidationGroup="vg1"/>
        </div>
        <div id="revieworderpassword">
             <asp:Label CssClass="revieworder4" id="LblPassword" runat="server" Text="Password:" />
             <asp:TextBox CssClass="revieworder5" id="TxtPassword" runat="server" TextMode="Password" />
             <asp:RequiredFieldValidator CssClass="revieworder6" ID="RequiredFieldValidator2" runat="server" ControlToValidate="TxtPassword"
                        ErrorMessage="Must not be empty" ValidationGroup="vg1" />
        </div>
    </div>
    <asp:Button CssClass="reviewordersubmit" id="BtnSubmit" onclick="BtnSubmit_Click" runat="server" Text="Submit" ValidationGroup="vg1" />
</div>
    
<div id="reviewdiv">
    <asp:Label CssClass="reviewdiv1" ID="LblReview" runat="server" Text="Please review your order: " />
    <asp:GridView CssClass="reviewdiv2" ID="GvReview" runat="server" AutoGenerateColumns="False" DataKeyNames="ProductID">
        <Columns>
            <asp:BoundField DataField="Name" HeaderText="Name" />
            <asp:BoundField DataField="Price" HeaderText="$ ea" />
            <asp:BoundField DataField="Quantity" HeaderText="Qty" />
        </Columns>
        <EmptyDataTemplate>
            Empty
        </EmptyDataTemplate>
    </asp:GridView>
    <div id="reviewdivrow">
        <asp:Label CssClass="reviewdiv3" ID="LblTotalText" runat="server" Text="Total: $"/>
        <asp:Label CssClass="reviewdiv4" ID="LblTotal" runat="server" Text="0"/>
    </div>
    <asp:LinkButton CssClass="reviewdiv5" ID="LnkShipBill" runat="server" Visible="False" Width="219px" PostBackUrl="~/Customer pages/ShipBill.aspx">Proceed to shipping and payment</asp:LinkButton>
    <asp:LinkButton CssClass="reviewdiv6" ID="LnkProducts" runat="server" Width="219px" PostBackUrl="~/Customer pages/Category.aspx">Return to products page</asp:LinkButton>
</div>

</asp:Content>

