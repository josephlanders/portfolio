<%@ Page Language="C#" MasterPageFile="~/Master page/MasterPage.master" AutoEventWireup="true" CodeFile="Category.aspx.cs" Inherits="Category" Title="Untitled Page" %>
<asp:Content ID="Content1" ContentPlaceHolderID="ContentPlaceHolder1" Runat="Server">
    <div style="width: 100%;">
        &nbsp; &nbsp; &nbsp;&nbsp;
        <asp:ObjectDataSource ID="ObjectDataSource1" runat="server" SelectMethod="GetCategories"
            TypeName="ProductClass.ProductClass"></asp:ObjectDataSource>
        <div style="width: 100%;">
        <asp:Repeater ID="RptCategory" runat="server" DataSourceID="ObjectDataSource1">
            <ItemTemplate>
                <div style="width: 100%">
                    <asp:LinkButton OnClick="setCategory" ID="LblLink" runat="server" Text='<%# Bind("Name") %>' CommandArgument='<%# Bind("CategoryID") %>' PostBackUrl=""></asp:LinkButton>
                    -
                        <asp:Label ID="LblDescription" runat="server" Text='<%# Bind("Description") %>'></asp:Label>
                        <br/>
                        <br/>
                </div>
            </ItemTemplate>
        </asp:Repeater>
        </div>
    </div>
</asp:Content>

