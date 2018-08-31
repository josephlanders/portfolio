<%@ Page Language="C#" MasterPageFile="~/Master page/MasterPage.master" AutoEventWireup="true" CodeFile="Payment.aspx.cs" Inherits="Payment" Title="Untitled Page" %>
<asp:Content ID="Content1" ContentPlaceHolderID="ContentPlaceHolder1" Runat="Server">
    <div style="width: 100%; height: 100%">
        <asp:Label ID="LblPaymentInfo" runat="server" Text="Please enter payment information:"></asp:Label><br />
        <br />
        &nbsp;<asp:Label ID="LblTotalText" runat="server" Text="Total inc shipping:"></asp:Label>
        <asp:Label ID="LblTotal" runat="server" Text="0"></asp:Label><br />
        <br />
        <div id="row1a">
        <asp:Label CssClass="rowlefta" ID="Label1" runat="server" Text="Cardholders Name:"></asp:Label>
        <asp:TextBox CssClass="rowrighta" ID="TxtCardholdersName" runat="server"></asp:TextBox>
        </div>
        <asp:RequiredFieldValidator ID="RequiredFieldValidator1" runat="server" ControlToValidate="TxtCardholdersName"
            ErrorMessage="Must not be empty"></asp:RequiredFieldValidator><br />
        &nbsp;<br />
        <div id="row2a">
        <asp:Label CssClass="rowlefta" ID="LblCardType" runat="server" Text="Card Type:"></asp:Label>
        <asp:DropDownList CssClass="rowrighta" ID="DDLCardType" runat="server">
            <asp:ListItem>VISA</asp:ListItem>
            <asp:ListItem>MasterCard</asp:ListItem>
            <asp:ListItem>AMEX</asp:ListItem>
        </asp:DropDownList>
        </div>
        <br />
        <br />
        <div id="row3a">
        <asp:Label CssClass="rowlefta" ID="LblCardNo" runat="server" Text="Card Number:"></asp:Label>
        <asp:TextBox ID="TxtCardNo1" runat="server" MaxLength="4" Width="29px"></asp:TextBox>
        <asp:Label ID="LblDash1" runat="server" Text="- " Width="9px"></asp:Label><asp:TextBox
            ID="TxtCardNo2" runat="server" MaxLength="4" Width="29px"></asp:TextBox><asp:Label
                ID="LblDash2" runat="server" Text="- " Width="9px"></asp:Label><asp:TextBox ID="TxtCardNo3"
                    runat="server" MaxLength="4" Width="29px"></asp:TextBox><asp:Label ID="LblDash3"
                        runat="server" Text="- " Width="9px"></asp:Label>
        <asp:TextBox ID="TxtCardNo4" runat="server" MaxLength="4" Width="29px"></asp:TextBox>
        </div>
        <br />
        <br />
        <div id="row4a">
        <asp:Label CssClass="rowlefta" ID="LblExpDate" runat="server" Text="Expiration Date:"></asp:Label>
        <asp:DropDownList ID="DDLExp1" runat="server">
            <asp:ListItem>01</asp:ListItem>
            <asp:ListItem>02</asp:ListItem>
            <asp:ListItem>03</asp:ListItem>
            <asp:ListItem>04</asp:ListItem>
            <asp:ListItem>05</asp:ListItem>
            <asp:ListItem>06</asp:ListItem>
            <asp:ListItem>07</asp:ListItem>
            <asp:ListItem>08</asp:ListItem>
            <asp:ListItem>09</asp:ListItem>
            <asp:ListItem>10</asp:ListItem>
            <asp:ListItem>11</asp:ListItem>
            <asp:ListItem>12</asp:ListItem>
        </asp:DropDownList>
        &nbsp;<asp:Label ID="LblDash4" runat="server" Text="- " Width="9px"></asp:Label>&nbsp;<asp:DropDownList
            ID="DDLExp2" runat="server">
            <asp:ListItem>08</asp:ListItem>
            <asp:ListItem>09</asp:ListItem>
            <asp:ListItem>10</asp:ListItem>
            <asp:ListItem>11</asp:ListItem>
            <asp:ListItem>12</asp:ListItem>
        </asp:DropDownList>
        </div>
        <br />
        <br />
        Note: Cardholder must have same address as shipping address<br />
        <br />
        <asp:LinkButton ID="LnkPay" runat="server" OnClick="LnkPayment_Click">Make Payment</asp:LinkButton><br />
        <br />
        <br />
        <asp:LinkButton ID="LnkShipping" runat="server" PostBackUrl="~/Customer Pages/ShipBill.aspx" CausesValidation="False">Return to Shipping Details form</asp:LinkButton><br />
    </div>
</asp:Content>

