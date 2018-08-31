<%@ Page Language="C#" MasterPageFile="~/Master page/MasterPage.master" AutoEventWireup="true" CodeFile="ProductList_Detail.aspx.cs" Inherits="ProductList_Detail" Title="Untitled Page" %>
<%@ PreviousPageType VirtualPath="~/Customer pages/Category.aspx" %>
<asp:Content ID="Content1" ContentPlaceHolderID="ContentPlaceHolder1" Runat="Server">
      <div style="vertical-align:middle; text-align:center; float:left; clear:both;">    
    <asp:ObjectDataSource ID="ObjectDataSource1" runat="server" SelectMethod="GetProductsByCategory" TypeName="ProductClass.ProductClass">
        <SelectParameters>
            <asp:SessionParameter Name="CategoryID" SessionField="CategoryID"
                Type="String" />
        </SelectParameters>
    </asp:ObjectDataSource>
    <asp:GridView ID="GvProductList" runat="server" AllowPaging="True" AllowSorting="True"
        AutoGenerateColumns="False" DataSourceID="ObjectDataSource1" OnSelectedIndexChanged="addToCart" DataKeyNames="ProductID"> 
        <Columns>
            <asp:CommandField SelectText="Add to Cart" ShowSelectButton="True"  />
            <asp:ImageField DataImageUrlField="Image" HeaderText="Image">
                <ControlStyle Height="100px" Width="70px" />
                <ItemStyle Height="100px" Width="70px" />
            </asp:ImageField>
            <asp:BoundField DataField="Name" HeaderText="Name" />
            <asp:BoundField DataField="ShortDescription" HeaderText="Description" />
            <asp:BoundField DataField="Price" HeaderText="Price" />
            <asp:BoundField DataField="StockLevel" HeaderText="Stock Level" />
        </Columns>
        <EmptyDataTemplate>
            No products in this category.<br />
            <asp:LinkButton ID="LnkCategory" runat="server" PostBackUrl="~/Customer pages/Category.aspx">Back to Categories list</asp:LinkButton>
        </EmptyDataTemplate>
    </asp:GridView>
        </div>
</asp:Content>

