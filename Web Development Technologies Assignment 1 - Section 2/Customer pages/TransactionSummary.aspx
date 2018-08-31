<%@ Page Language="C#" MasterPageFile="~/Master page/MasterPage.master" AutoEventWireup="true" CodeFile="TransactionSummary.aspx.cs" Inherits="TransactionSummary" Title="Untitled Page" %>
<asp:Content ID="Content1" ContentPlaceHolderID="ContentPlaceHolder1" Runat="Server">
    <div style="width: 100%; height: 100%">
        <br />
        <asp:Label ID="LblReview" runat="server" Height="33px" Text="Thank you for your order. <br> Please print this transaction summary for your records."
            Width="437px"></asp:Label><br />
        <br />
        <asp:GridView ID="GvReview" runat="server" AutoGenerateColumns="False" DataKeyNames="ProductID">
            <Columns>
                <asp:BoundField DataField="Name" HeaderText="Name" />
                <asp:BoundField DataField="Price" HeaderText="$ ea" />
                <asp:BoundField DataField="Quantity" HeaderText="Qty" />
            </Columns>
            <EmptyDataTemplate>
                Empty
            </EmptyDataTemplate>
        </asp:GridView>
        &nbsp;&nbsp;
        <br />
        <asp:Label ID="LblTotalText" runat="server" Text="Total: $"></asp:Label>&nbsp;
        <asp:Label ID="LblTotal" runat="server" Text="0"></asp:Label>
        <br />
        <br />
        <asp:Label ID="Label1" runat="server" Text="Shipping:"></asp:Label>
        <asp:Label ID="LblShipping" runat="server" Text="0"></asp:Label><br />
        <br />
        <asp:Label ID="Label2" runat="server" Text="Grand Total:"></asp:Label>
        <asp:Label ID="LblGrandTotal" runat="server" Text="0"></asp:Label><br />
        <br />
        <asp:Label ID="LblShipToLbl" runat="server" Text="Shipping to:"></asp:Label>&nbsp;
        <asp:Label ID="LblShipTo" runat="server"></asp:Label><br />
        <br />
        <asp:Label ID="LblAddressLine1Lbl" runat="server" Text="Address Line 1:"></asp:Label>
        <asp:Label ID="LblAddressLine1" runat="server"></asp:Label><br />
        <br />
        <asp:Label ID="LblPostCodeLbl" runat="server" Text="PostCode:"></asp:Label>
        <asp:Label ID="LblPostCode" runat="server"></asp:Label><br />
        <br />
        <asp:Label ID="LblCardholersNameLbl" runat="server" Text="Cardholders Name:"></asp:Label>
        <asp:Label ID="LblCardHoldersName" runat="server"></asp:Label><br />
        <br />
        <asp:Label ID="LblCardNoLbl" runat="server" Text="Card Number:"></asp:Label>
        <asp:Label ID="Label3" runat="server" Text="****"></asp:Label>
        <asp:Label ID="LblDash1" runat="server" Text="- " Width="12px"></asp:Label><asp:Label
            ID="Label4" runat="server" Text="****"></asp:Label><asp:Label ID="Label5" runat="server"
                Text="- " Width="12px"></asp:Label><asp:Label ID="Label6" runat="server" Text="****"></asp:Label><asp:Label
                    ID="Label7" runat="server" Text="- " Width="12px"></asp:Label>
        <asp:Label ID="LblCardNo4" runat="server"></asp:Label><br />
        <br />
        <asp:Button ID="BtnLogout" runat="server" OnClick="BtnLogout_Click" Text="Logout" /></div>
</asp:Content>

