<%@ Page Language="C#" MasterPageFile="~/Master page/MasterPage.master" AutoEventWireup="true" CodeFile="ShipBill.aspx.cs" Inherits="ShipBill" Title="Untitled Page" %>
<asp:Content ID="Content1" ContentPlaceHolderID="ContentPlaceHolder1" Runat="Server">
    <div style="width: 100%; height: 100%">
        <asp:Label ID="LblShippingInfo" runat="server" Text="Please enter shipping information:" /><br />
        <asp:ValidationSummary ID="ValidationSummary1" runat="server" ValidationGroup="vg1" />
        <br />
       <div id="row">
        <asp:Label CssClass="row1left" ID="Label1" runat="server" Text="Shipping to:"></asp:Label>
        <asp:TextBox CssClass="rowright" ID="TxtShipTo" runat="server"></asp:TextBox>
       </div>
        <asp:RequiredFieldValidator ID="RequiredFieldValidator1" runat="server" ControlToValidate="TxtShipTo"
            ErrorMessage="Addressee must not be empty" ValidationGroup="vg1">*</asp:RequiredFieldValidator><br />
        <br />
        <div id="row1">
        <asp:Label CssClass="row1left" ID="LblAddressLine1" runat="server" Text="Address Line 1:"></asp:Label>
        <asp:TextBox CssClass="rowright" ID="TxtAddressLine1" runat="server"></asp:TextBox>
        </div>
        <asp:RequiredFieldValidator ID="RequiredFieldValidator2" runat="server" ControlToValidate="TxtAddressLine1"
            ErrorMessage="AddressLine1 must not be empty" ValidationGroup="vg1">*</asp:RequiredFieldValidator><br />
        <br />
        <div id="row2">
        <asp:Label CssClass="row1left" ID="LblAddressLine2" runat="server" Text="Address Line 2:"></asp:Label>
        <asp:TextBox CssClass="rowright" ID="TxtAddressLine2" runat="server"></asp:TextBox><br />
        </div>
        <br />
        <div id="row3">
        <asp:Label CssClass="row1left" ID="LblSuburb" runat="server" Text="Suburb:"></asp:Label>
        <asp:TextBox CssClass="rowright" ID="TxtSuburb" runat="server"></asp:TextBox>
        </div>
        <asp:RequiredFieldValidator ID="RequiredFieldValidator4" runat="server" ControlToValidate="TxtSuburb"
            ErrorMessage="Suburb must not be empty" ValidationGroup="vg1">*</asp:RequiredFieldValidator><br />
        <br />
        <div id="row4">
        <asp:Label CssClass="row1left" ID="LblCity" runat="server" Text="City:"></asp:Label>
        <asp:TextBox CssClass="rowright" ID="TxtCity" runat="server"></asp:TextBox>
        </div>
        <asp:RequiredFieldValidator  ID="RequiredFieldValidator5" runat="server" ControlToValidate="TxtCity"
            ErrorMessage="City must not be empty" ValidationGroup="vg1">*</asp:RequiredFieldValidator><br />
        <br />
        <div id="row5">
        <asp:Label CssClass="row1left" ID="LblState" runat="server" Text="State:"></asp:Label>
        <asp:DropDownList CssClass="rowright" ID="DDLState" runat="server">
            <asp:ListItem>VIC</asp:ListItem>
            <asp:ListItem>NSW</asp:ListItem>
            <asp:ListItem>ACT</asp:ListItem>
            <asp:ListItem>NT</asp:ListItem>
            <asp:ListItem>SA</asp:ListItem>
            <asp:ListItem>QLD</asp:ListItem>
            <asp:ListItem>TAS</asp:ListItem>
            <asp:ListItem>WA</asp:ListItem>
        </asp:DropDownList>
        </div>
        <br />
        <br />
        <div id="row6">
        <asp:Label CssClass="row1left" ID="LblPostCode" runat="server" Text="PostCode:"></asp:Label>
        <asp:TextBox CssClass="rowright" ID="TxtPost" runat="server" MaxLength="4"></asp:TextBox>
        </div>
        <asp:RequiredFieldValidator ID="RequiredFieldValidator3" runat="server" ControlToValidate="TxtPost"
            ErrorMessage="Postcode must not be empty" ValidationGroup="vg1">*</asp:RequiredFieldValidator>
        <asp:RangeValidator ID="RangeValidator1" runat="server" ControlToValidate="TxtPost"
            ErrorMessage="Postcode must be a number (rangevalidator)" MaximumValue="9999"
            MinimumValue="0" ValidationGroup="vg1">*</asp:RangeValidator>
        <asp:RegularExpressionValidator ID="RegularExpressionValidator1" runat="server" ControlToValidate="TxtPost"
            ErrorMessage="Postcode must be 4 digits (Regex validator)" ValidationExpression="[0-9]{4}"
            ValidationGroup="vg1">*</asp:RegularExpressionValidator><br />
        <br />
        <div id="row7">
        <asp:Label CssClass="row1left" ID="LblShippingType" runat="server" Text="Shipping Type:"></asp:Label>
        <asp:DropDownList CssClass="rowright" ID="DDLShipping" runat="server">
            <asp:ListItem Value="Standard">Standard</asp:ListItem>
            <asp:ListItem>Overnight</asp:ListItem>
        </asp:DropDownList>
        (Standard=$2.50, Overnight=$10)
        </div>
        <br />
        <br />
        <asp:LinkButton ID="LnkPayment" runat="server" OnClick="LnkPayment_Click" CausesValidation="False">Proceed to Payment</asp:LinkButton><br />
        <br />
        <br />
        <asp:LinkButton ID="LnkReview" runat="server" CausesValidation="False" PostBackUrl="~/Customer pages/ReviewOrder.aspx">Return to Review Order</asp:LinkButton><br />
    </div>
</asp:Content>

