<%@ Page Language="C#" MasterPageFile="~/Master page/MasterPage.master" AutoEventWireup="true" CodeFile="Sitemap.aspx.cs" Inherits="Sitemap" Title="Untitled Page" %>
<asp:Content ID="Content1" ContentPlaceHolderID="ContentPlaceHolder1" Runat="Server">
    <asp:SiteMapDataSource ID="SiteMapDataSource1" runat="server" />
    <br />
    <asp:TreeView ID="TvSiteMap" runat="server" DataSourceID="SiteMapDataSource1">
    </asp:TreeView>
</asp:Content>

