<%@ Page Language="C#" MasterPageFile="~/Master Page/MasterPage.master" AutoEventWireup="true" CodeFile="admin.aspx.cs" Inherits="_Default" Title="Untitled Page" %>

<%@ Register Assembly="System.Web.Extensions, Version=1.0.61025.0, Culture=neutral, PublicKeyToken=31bf3856ad364e35"
    Namespace="System.Web.UI" TagPrefix="asp" %>
<asp:Content ID="Content1" ContentPlaceHolderID="ContentPlaceHolder1" Runat="Server">

       <div id="revieworderloginform">
            <asp:Label CssClass="revieworder0" id="LblTitle" runat="server" Text="Admin Login" />
            <div id="revieworderusername">
                <asp:Label CssClass="revieworder1" id="LblUsername" runat="server" Text="Username:"/>
                <asp:TextBox CssClass="revieworder2" id="TxtUsername" runat="server"></asp:TextBox>
                <asp:RequiredFieldValidator CssClass="revieworder3" ID="RequiredFieldValidator1" runat="server" ControlToValidate="TxtUsername"
                            ErrorMessage="Must not be empty" ValidationGroup="vg1"/>
             </div>
             <div id="revieworderpassword">
                 <asp:Label CssClass="revieworder4" id="LblPassword" runat="server" Text="Password:" />
                 <asp:TextBox CssClass="revieworder5" id="TxtPassword" runat="server" TextMode="Password" />
                 <asp:RequiredFieldValidator CssClass="" ID="RequiredFieldValidator2" runat="server" ControlToValidate="TxtPassword"
                            ErrorMessage="Must not be empty" ValidationGroup="vg1" />
              </div>
         <asp:Button CausesValidation="true" CssClass="reviewordersubmit" id="BtnSubmit" onclick="BtnSubmit_Click" runat="server" Text="Submit" ValidationGroup="vg1"/>
    </div>
         
    <div id="adminlinks">
    <div id="adminlinkrow1">
        <asp:LinkButton CssClass="adminlinks1" ID="LnkProfiles" runat="server" Visible="False" PostBackUrl="~/Admin pages/ManageProfiles.aspx">Manage Profiles</asp:LinkButton>
        <asp:Label CssClass="adminlinks2" ID="LblProfiles" runat="server" Text="- Add/Delete/Edit user details" Visible="False" />
    </div>
    <div id="adminlinkrow2">
        <asp:LinkButton CssClass="adminlinks1" ID="LnkProducts" runat="server" Visible="False" PostBackUrl="~/Admin pages/ManageProducts.aspx">Manage Products</asp:LinkButton>
        <asp:Label CssClass="adminlinks2" ID="LblProducts" runat="server" Text="- Add/Delete/Edit product list" Visible="False" />
    </div>
    <div id="adminlinkrow3">
        <asp:LinkButton CssClass="adminlinks1" ID="LnkCategories" runat="server" PostBackUrl="~/Admin pages/ManageCategories.aspx" Visible="False">Manage Categories</asp:LinkButton>
        <asp:Label CssClass="adminlinks2" ID="LblCategories" runat="server" Text="- Add/Delete/Edit category list" Visible="False" />
    </div>
    <div id="adminlinkrow4">
        <asp:LinkButton CssClass="adminlinks1" ID="LnkCrystal" Visible="false" runat="server" CausesValidation="False" PostBackUrl="~/Admin pages/Crystal.aspx">A lovely Crystal Report</asp:LinkButton>
    </div>
   </div>
</asp:Content>

