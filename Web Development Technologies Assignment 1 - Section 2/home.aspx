<%@ Page Language="C#" AutoEventWireup="true"  CodeFile="~/home.aspx.cs" Inherits="_Default" MasterPageFile="~/Master page/MasterPage.master" %>
<%@ Register Assembly="AjaxControlToolkit" Namespace="AjaxControlToolkit" TagPrefix="ajaxToolkit" %>
    <asp:content id='Content1' ContentPlaceHolderID='ContentPlaceHolder1' runat='server'>
        <asp:Panel ID="Panel2" runat="server" CssClass="collapsePanelHeader"> 
            <div id="newsTitle">
                <div id="newsTitle1">What is e-Shop?</div>
                <div id="newsTitle2">
                    <asp:Label ID="Label1" runat="server" CssClass="newsTitle3">(Show Post)</asp:Label>
                </div>
                <div id="newsTitle4">
                    <asp:ImageButton ID="Image1" runat="server"  AlternateText="(Show Details...)" CssClass="newsTitle5"/>
                </div>
            </div>
        </asp:Panel>
        <asp:Panel ID="Panel1" runat="server" CssClass="collapsePanel">
        <div id="news">
            <p>
                <asp:ImageButton ID="Image2" runat="server" ImageUrl="~/images/AJAX.gif"
                    AlternateText="ASP.NET AJAX" ImageAlign="right" CssClass="news1" />
                <asp:Label runat="server" Text="Welcome to e-Shop the premier online DVD and books retailer.<br><br>This site is coded using the Visual Studio 2005 Integrated Development Environment.<br>The C# language was used, along with ASP.NET 2.0 and AJAX." CssClass="news2" />
            </p>
        </div>
        </asp:Panel>

    <ajaxToolkit:CollapsiblePanelExtender ID="cpeDemo" runat="Server"
        TargetControlID="Panel1"
        ExpandControlID="Panel2"
        CollapseControlID="Panel2" 
        Collapsed="True"
        TextLabelID="Label1"
        ImageControlID="Image1"    
        ExpandedText="(Hide Post)"
        CollapsedText="(Show Post)"
        ExpandedImage="~/images/collapse_blue.jpg"
        CollapsedImage="~/images/expand_blue.jpg"
        SuppressPostBack="true"
        SkinID="CollapsiblePanelDemo" />
</asp:content>

