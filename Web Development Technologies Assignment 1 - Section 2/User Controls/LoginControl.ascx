<%@ Control Language="C#" AutoEventWireup="true" CodeFile="LoginControl.ascx.cs" Inherits="WebUserControl2" %>
<%@ Register Assembly="AjaxControlToolkit" Namespace="AjaxControlToolkit" TagPrefix="cc1" %>
<%@ Register Assembly="System.Web.Extensions, Version=1.0.61025.0, Culture=neutral, PublicKeyToken=31bf3856ad364e35"
    Namespace="System.Web.UI" TagPrefix="asp" %>
<div id="loginerror">
    <asp:Label CssClass="login0" ID="LblError" runat="server" ForeColor="Red" />
</div>
<div id="loginshowname">
    <asp:Label CssClass="login1" ID="LblLoggedInUsername" runat="server" Font-Italic="True" Visible="False" />
</div>
<div id="loginusername">
    <cc1:ValidatorCalloutExtender ID="ValidatorCalloutExtender1" runat="server" TargetControlID="RequiredFieldValidator1">
    </cc1:ValidatorCalloutExtender>
    <asp:RequiredFieldValidator ID="RequiredFieldValidator1" runat="server" ErrorMessage="Username must not be empty" ControlToValidate="TxtUsername" ValidationGroup="lg1" />
    <asp:Label CssClass="login2" ID="LblUsername" runat="server" Text="Username:" />
    <asp:TextBox CssClass="login3" ID="TxtUsername" runat="server" ValidationGroup="lg1" />
</div>
<div id="loginpassword">
    <cc1:ValidatorCalloutExtender ID="ValidatorCalloutExtender2" runat="server" TargetControlID="RequiredFieldValidator2">
    </cc1:ValidatorCalloutExtender>
    <asp:RequiredFieldValidator ID="RequiredFieldValidator2" runat="server" ErrorMessage="Password must not be empty" ControlToValidate="TxtPassword" ValidationGroup="lg1" />
    <asp:Label CssClass="login4" ID="LblPassword" runat="server" Text="Password:" />
    <asp:TextBox CssClass="login5" ID="TxtPassword" runat="server" TextMode="Password" ValidationGroup="lg1" />
</div>
<div id="loginbutton">
<asp:Button CssClass="login6" ID="BtnLogin" runat="server" OnClick="BtnLogin_Click1" Text="Login" ValidationGroup="lg1" />
</div>