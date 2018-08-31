<%@ Control Language="C#" AutoEventWireup="true" CodeFile="Navigation.ascx.cs" Inherits="WebUserControl" %>
<div id="navcontrol">
   <asp:LinkButton CssClass="navcontrol1" ID="LnkHome" runat="server" PostBackUrl="~/home.aspx" CausesValidation="False">Home</asp:LinkButton>
   <asp:LinkButton CssClass="navcontrol2" ID="LnkRegister" runat="server" PostBackUrl="~/Customer pages/CreateProfile.aspx" CausesValidation="False">Register</asp:LinkButton>
   <asp:LinkButton CssClass="navcontrol3" ID="LnkProducts" runat="server" CausesValidation="False" PostBackUrl="~/Customer pages/Category.aspx">Products</asp:LinkButton>
   <asp:LinkButton CssClass="navcontrol4" ID="LnkSiteMap" runat="server" CausesValidation="False" PostBackUrl="~/sitemap.aspx">Site Map</asp:LinkButton>
</div>