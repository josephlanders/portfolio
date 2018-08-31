<%@ Page Language="C#" MasterPageFile="~/Master page/MasterPage.master" AutoEventWireup="true" CodeFile="ManageProducts.aspx.cs" Inherits="_Default" Title="Untitled Page" %>

<asp:Content ID="Content1" ContentPlaceHolderID="ContentPlaceHolder1" Runat="Server">
<script language="javascript" type="text/javascript">
// <!CDATA[

function DIV1_onclick() {

}

// ]]>
</script>

    &nbsp;&nbsp; &nbsp;&nbsp;
    &nbsp; &nbsp;&nbsp;
    <div style="clear: left; float: left; width: 100%; height: 100%; margin-left: 3%;">
        <br />
        <asp:GridView ID="GvProducts" runat="server" AllowPaging="True" AllowSorting="True"
            DataSourceID="ObjectDataSource1" OnSelectedIndexChanged="RowSelect" OnRowCommand="ItemCommand" style="text-align: center"  DataKeyNames="ProductID">
            <Columns>
                <asp:CommandField ShowSelectButton="True" ShowDeleteButton="True" />
            </Columns>
        </asp:GridView>
        <br />
        <asp:ValidationSummary ID="ValidationSummary1" runat="server" Width="190px" />
        <br />
        &nbsp;&nbsp;
        <asp:Label ID="LblStatus" runat="server" Width="295px"></asp:Label>&nbsp;<br />
        <br />
        &nbsp;
    <asp:ObjectDataSource ID="ObjectDataSource1" runat="server" SelectMethod="GetProducts"
        TypeName="ProductClass.ProductClass" InsertMethod="InsertProduct" UpdateMethod="UpdateProduct"
         DeleteMethod="DeleteProduct" OnInserted="CatchError_ProductInserted" OnUpdated="CatchError_ProductUpdated" >
        <DeleteParameters>
            <asp:Parameter Name="ProductID" Type="String" />
        </DeleteParameters>
        <InsertParameters>
            <asp:Parameter Name="ProductID" Type="String" />
            <asp:Parameter Name="Name" Type="String" />
            <asp:Parameter Name="ParentProductID" Type="String" />
            <asp:Parameter Name="DisplaySeq" Type="String" />
            <asp:Parameter Name="CategoryID" Type="String" />
            <asp:Parameter Name="ProductGroupID" Type="String" />
            <asp:Parameter Name="Display" Type="String" />
            <asp:Parameter Name="ShortDescription" Type="String" />
            <asp:Parameter Name="LongDescription" Type="String" />
            <asp:Parameter Name="Image" Type="String" />
            <asp:Parameter Name="Price" Type="String" />
            <asp:Parameter Name="StockLevel" Type="String" />
        </InsertParameters>
        <UpdateParameters>
            <asp:Parameter Name="ProductID" Type="String" />
            <asp:Parameter Name="Name" Type="String" />
            <asp:Parameter Name="ParentProductID" Type="String" />
            <asp:Parameter Name="DisplaySeq" Type="String" />
            <asp:Parameter Name="CategoryID" Type="String" />
            <asp:Parameter Name="ProductGroupID" Type="String" />
            <asp:Parameter Name="Display" Type="String" />
            <asp:Parameter Name="ShortDescription" Type="String" />
            <asp:Parameter Name="LongDescription" Type="String" />
            <asp:Parameter Name="Image" Type="String" />
            <asp:Parameter Name="Price" Type="String" />
            <asp:Parameter Name="StockLevel" Type="String" />
        </UpdateParameters>
    </asp:ObjectDataSource>
        <asp:ObjectDataSource ID="ObjectDataSource2" runat="server" SelectMethod="GetCategories"
            TypeName="ProductClass.ProductClass"></asp:ObjectDataSource>
        <div style="width: 60%; height: 100%">
    <asp:DetailsView ID="DvProducts" runat="server" AutoGenerateRows="False" DataSourceID="ObjectDataSource1"
     DataKeyNames="ProductID" OnItemCommand="ItemCommand"
     
        Height="50px" Width="311px" AllowPaging="True" style="clear: left; float: left">
        <Fields>
            <asp:CommandField ShowInsertButton="True" ShowEditButton="True" ShowDeleteButton="True" />
            <asp:TemplateField HeaderText="ProductID">
                <EditItemTemplate>
                    <asp:Label ID="LblProductID" runat="server" Text='<%# Bind("ProductID") %>'></asp:Label>
                </EditItemTemplate>
                <InsertItemTemplate>
                    <asp:TextBox ID="ProductIDTextBox" runat="server" Text='<%# Bind("ProductID") %>' style="width: 70%;" Width="129px"></asp:TextBox><asp:RangeValidator ID="RangeValidator1" runat="server" ControlToValidate="ProductIDTextBox"
                        ErrorMessage="ProductID must be between 0 and 255" MaximumValue="255" MinimumValue="0" Type="Integer" style="text-align: right" Width="8px">*</asp:RangeValidator>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator1" runat="server" ControlToValidate="ProductIDTextBox"
                        ErrorMessage="ProductID is a required field" Width="6px">*</asp:RequiredFieldValidator>
                </InsertItemTemplate>
                <ItemTemplate>
                    <asp:Label ID="LblProductID" runat="server" Text='<%# Bind("ProductID") %>'></asp:Label>
                </ItemTemplate>
            </asp:TemplateField>
            
            <asp:TemplateField HeaderText="Name">
                <EditItemTemplate>
                    <asp:TextBox ID="NameTextBox" runat="server" Text='<%# Bind("Name") %>' MaxLength="25" style="width: 70px"></asp:TextBox>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator2" runat="server" ControlToValidate="NameTextBox"
                        ErrorMessage="Name is a required field">*</asp:RequiredFieldValidator>
                </EditItemTemplate>
                <InsertItemTemplate>
                    <asp:TextBox ID="NameTextBox" runat="server" Text='<%# Bind("Name") %>' MaxLength="25" style="width: 70%"></asp:TextBox>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator2" runat="server" ControlToValidate="NameTextBox"
                        ErrorMessage="Name is a required field">*</asp:RequiredFieldValidator>
                </InsertItemTemplate>
                <ItemTemplate>
                    <asp:Label ID="LblName" runat="server" Text='<%# Bind("Name") %>'></asp:Label>
                </ItemTemplate>
            </asp:TemplateField>
            
            <asp:TemplateField HeaderText="DisplaySeq">
                <EditItemTemplate>
                    <asp:TextBox ID="DisplaySeqTextBox" runat="server" Text='<%# Bind("DisplaySeq") %>' style="width: 70%"></asp:TextBox>
                    <asp:RangeValidator ID="RangeValidator3" runat="server" ControlToValidate="DisplaySeqTextBox"
                        ErrorMessage="DisplaySeq must be between 0 and 255" MaximumValue="255" MinimumValue="0" Type="Integer">*</asp:RangeValidator>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator3a" runat="server" ControlToValidate="DisplaySeqTextBox"
                        ErrorMessage="DisplaySeq is a required field">*</asp:RequiredFieldValidator><br />
                    &nbsp;
                </EditItemTemplate>
                <InsertItemTemplate>
                    <asp:TextBox ID="DisplaySeqTextBox" runat="server" Text='<%# Bind("DisplaySeq") %>' style="width: 70%"></asp:TextBox>
                    <asp:RangeValidator ID="RangeValidator3" runat="server" ControlToValidate="DisplaySeqTextBox"
                        ErrorMessage="DisplaySeq must be between 0 and 255" MaximumValue="255" MinimumValue="0" Type="Integer">*</asp:RangeValidator>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator3a" runat="server" ControlToValidate="DisplaySeqTextBox"
                        ErrorMessage="DisplaySeq is a required field">*</asp:RequiredFieldValidator>
                </InsertItemTemplate>
                <ItemTemplate>
                    <asp:Label ID="LblDisplaySeq" runat="server" Text='<%# Bind("DisplaySeq") %>'></asp:Label>
                </ItemTemplate>
            </asp:TemplateField>
            
            <asp:TemplateField HeaderText="ParentProductID">
                <EditItemTemplate>
                    <asp:TextBox ID="ParentProductIDTextBox" runat="server" Text='<%# Bind("ParentProductID") %>' style="width: 70%"></asp:TextBox>
                    <asp:RangeValidator ID="RangeValidator4" runat="server" ControlToValidate="ParentProductIDTextBox"
                        ErrorMessage="ParentProductID must be between 0 and 255" MaximumValue="255" MinimumValue="0" Type="Integer">*</asp:RangeValidator>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator4a" runat="server" ControlToValidate="ParentProductIDTextBox"
                        ErrorMessage="ParentProductID is a required field">*</asp:RequiredFieldValidator><br />
                    &nbsp;
                </EditItemTemplate>
                <InsertItemTemplate>
                    <asp:TextBox ID="ParentProductIDTextBox" runat="server" Text='<%# Bind("ParentProductID") %>' style="width: 70%"></asp:TextBox>
                    <asp:RangeValidator ID="RangeValidator4" runat="server" ControlToValidate="ParentProductIDTextBox"
                        ErrorMessage="ParentProductID must be between 0 and 255" MaximumValue="255" MinimumValue="0" Type="Integer">*</asp:RangeValidator>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator4a" runat="server" ControlToValidate="ParentProductIDTextBox"
                        ErrorMessage="ParentProductID is a required field">*</asp:RequiredFieldValidator>
                </InsertItemTemplate>
                <ItemTemplate>
                    <asp:Label ID="LblParentProductID" runat="server" Text='<%# Bind("ParentProductID") %>'></asp:Label>
                </ItemTemplate>
            </asp:TemplateField>
            
            <asp:TemplateField HeaderText="CategoryID">
                <EditItemTemplate>
                    <asp:TextBox ID="CategoryIDTextBox" runat="server" Text='<%# Bind("CategoryID") %>' style="width: 70%"></asp:TextBox><asp:RequiredFieldValidator
                        ID="RequiredFieldValidator5" runat="server" ControlToValidate="CategoryIDTextBox"
                        ErrorMessage="CategoryID is a required field">*</asp:RequiredFieldValidator><asp:RangeValidator
                            ID="RangeValidator5" runat="server" ControlToValidate="CategoryIDTextBox" ErrorMessage="Category ID must be between 0 and 255"
                            MaximumValue="255" MinimumValue="0" Width="1px" Type="Integer">*</asp:RangeValidator>
                </EditItemTemplate>
                <InsertItemTemplate>
                    <asp:TextBox ID="CategoryIDTextBox" runat="server" Text='<%# Bind("CategoryID") %>' style="width: 70%"></asp:TextBox><asp:RequiredFieldValidator
                        ID="RequiredFieldValidator5" runat="server" ControlToValidate="CategoryIDTextBox"
                        ErrorMessage="CategoryID is a required field">*</asp:RequiredFieldValidator>
                    <asp:RangeValidator ID="RangeValidator5" runat="server" ControlToValidate="CategoryIDTextBox"
                        ErrorMessage="Category ID must be between 0 and 255" MaximumValue="255" MinimumValue="0" Type="Integer"
                        Width="1px">*</asp:RangeValidator>
                </InsertItemTemplate>
                <ItemTemplate>
                    <asp:Label ID="LblCategoryID" runat="server" Text='<%# Bind("CategoryID") %>'></asp:Label>
                </ItemTemplate>
            </asp:TemplateField>
                
            <asp:TemplateField HeaderText="ProductGroupID">
                <EditItemTemplate>
                    <asp:TextBox ID="ProductGroupIDTextBox" runat="server" Text='<%# Bind("ProductGroupID") %>' style="width: 70%"></asp:TextBox>
                    <asp:RangeValidator ID="RangeValidator6" runat="server" ControlToValidate="ProductGroupIDTextBox"
                        ErrorMessage="ProductGroupID must be between 0 and 255" MaximumValue="255" MinimumValue="0" Type="Integer">*</asp:RangeValidator>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator6a" runat="server" ControlToValidate="ProductGroupIDTextBox"
                        ErrorMessage="ProductGroupID is a required field">*</asp:RequiredFieldValidator><br />
                    &nbsp;
                </EditItemTemplate>
                <InsertItemTemplate>
                    <asp:TextBox ID="ProductGroupIDTextBox" runat="server" Text='<%# Bind("ProductGroupID") %>' style="width: 70%"></asp:TextBox>
                    <asp:RangeValidator ID="RangeValidator6" runat="server" ControlToValidate="ProductGroupIDTextBox"
                        ErrorMessage="ProductGroupID must be between 0 and 255" MaximumValue="255" MinimumValue="0" Type="Integer">*</asp:RangeValidator>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator6a" runat="server" ControlToValidate="ProductGroupIDTextBox"
                        ErrorMessage="ProductGroupID is a required field">*</asp:RequiredFieldValidator>
                </InsertItemTemplate>
                <ItemTemplate>
                    <asp:Label ID="LblProductGroupID" runat="server" Text='<%# Bind("ProductGroupID") %>'></asp:Label>
                </ItemTemplate>
            </asp:TemplateField>
            
            <asp:TemplateField HeaderText="Display">
                <EditItemTemplate>
                    <asp:TextBox ID="DisplayTextBox" runat="server" Text='<%# Bind("Display") %>' style="width: 70%"></asp:TextBox>
                    <asp:RangeValidator ID="RangeValidator7" runat="server" ControlToValidate="DisplayTextBox"
                        ErrorMessage="Display must be 0 or 1" MaximumValue="1" MinimumValue="0" Type="Integer">*</asp:RangeValidator>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator8a" runat="server" ControlToValidate="DisplayTextBox"
                        ErrorMessage="Display is a required field">*</asp:RequiredFieldValidator><br />
                    &nbsp;
                </EditItemTemplate>
                <InsertItemTemplate>
                    <asp:TextBox ID="DisplayTextBox" runat="server" Text='<%# Bind("Display") %>' style="width: 70%"></asp:TextBox>
                    <asp:RangeValidator ID="RangeValidator7" runat="server" ControlToValidate="DisplayTextBox"
                        ErrorMessage="Display must be  0 or 1" MaximumValue="1" MinimumValue="0" Type="Integer">*</asp:RangeValidator>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator8a" runat="server" ControlToValidate="DisplayTextBox"
                        ErrorMessage="Display is a required field">*</asp:RequiredFieldValidator>
                </InsertItemTemplate>
                <ItemTemplate>
                    <asp:Label ID="LblDisplay" runat="server" Text='<%# Bind("Display") %>'></asp:Label>
                </ItemTemplate>
            </asp:TemplateField>                                                           

            <asp:TemplateField HeaderText="ShortDescription">
                <EditItemTemplate>
                    <asp:TextBox ID="ShortDescriptionTextBox" runat="server" Text='<%# Bind("ShortDescription") %>' MaxLength="50" style="width: 70%" Rows="2" TextMode="MultiLine"></asp:TextBox>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator9a" runat="server" ControlToValidate="ShortDescriptionTextBox"
                        ErrorMessage="Short Description is a required field">*</asp:RequiredFieldValidator>
                </EditItemTemplate>
                <InsertItemTemplate>
                    <asp:TextBox ID="ShortDescriptionTextBox" runat="server" Text='<%# Bind("ShortDescription") %>' MaxLength="50" style="width: 70%" Rows="2" TextMode="MultiLine"></asp:TextBox>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator9a" runat="server" ControlToValidate="ShortDescriptionTextBox"
                        ErrorMessage="Short Description is a required field">*</asp:RequiredFieldValidator>
                </InsertItemTemplate>
                <ItemTemplate>
                    <asp:Label ID="LblShortDescription" runat="server" Text='<%# Bind("ShortDescription") %>'></asp:Label>
                </ItemTemplate>
            </asp:TemplateField>                                                            


            <asp:TemplateField HeaderText="LongDescription">
                <EditItemTemplate>
                    <asp:TextBox ID="LongDescriptionTextBox" runat="server" Text='<%# Bind("LongDescription") %>' MaxLength="500" style="width: 70%" Rows="5" TextMode="MultiLine"></asp:TextBox>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator10a" runat="server" ControlToValidate="LongDescriptionTextBox"
                        ErrorMessage="Long Description is a required field">*</asp:RequiredFieldValidator>
                </EditItemTemplate>
                <InsertItemTemplate>
                    <asp:TextBox ID="LongDescriptionTextBox" runat="server" Text='<%# Bind("LongDescription") %>' MaxLength="500" style="width: 70%" Rows="5" TextMode="MultiLine"></asp:TextBox>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator10a" runat="server" ControlToValidate="LongDescriptionTextBox"
                        ErrorMessage="Long Description is a required field">*</asp:RequiredFieldValidator>
                </InsertItemTemplate>
                <ItemTemplate>
                    <asp:Label ID="LblLongDescription" runat="server" Text='<%# Bind("LongDescription") %>'></asp:Label>
                </ItemTemplate>
            </asp:TemplateField>                                                            


            <asp:TemplateField HeaderText="Image">
                <EditItemTemplate>
                    <asp:TextBox ID="ImageTextBox" runat="server" Text='<%# Bind("Image") %>' MaxLength="50" style="width: 70%"></asp:TextBox>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator11a" runat="server" ControlToValidate="ImageTextBox"
                        ErrorMessage="Image is a required field">*</asp:RequiredFieldValidator>
                </EditItemTemplate>
                <InsertItemTemplate>
                    <asp:TextBox ID="ImageTextBox" runat="server" Text='<%# Bind("Image") %>' MaxLength="50" style="width: 70%"></asp:TextBox>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator11a" runat="server" ControlToValidate="ImageTextBox"
                        ErrorMessage="Image is a required field">*</asp:RequiredFieldValidator>
                </InsertItemTemplate>
                <ItemTemplate>
                    <asp:Label ID="LblImage" runat="server" Text='<%# Bind("Image") %>'></asp:Label>
                </ItemTemplate>
            </asp:TemplateField>                                                            

            <asp:TemplateField HeaderText="Price">
                <EditItemTemplate>
                    <asp:TextBox ID="PriceTextBox" runat="server" Text='<%# Bind("Price") %>' style="width: 70%"></asp:TextBox>
                    <asp:RangeValidator ID="RangeValidator8" runat="server" ControlToValidate="PriceTextBox"
                        ErrorMessage="Price must be between 0 and 255" MaximumValue="255" MinimumValue="0" Type="Integer">*</asp:RangeValidator>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator12a" runat="server" ControlToValidate="PriceTextBox"
                        ErrorMessage="Price is a required field">*</asp:RequiredFieldValidator>
                </EditItemTemplate>
                <InsertItemTemplate>
                    <asp:TextBox ID="PriceTextBox" runat="server" Text='<%# Bind("Price") %>' style="width: 70%"></asp:TextBox>
                    <asp:RangeValidator ID="RangeValidator8" runat="server" ControlToValidate="PriceTextBox"
                        ErrorMessage="Price must be between 0 and 255" MaximumValue="255" MinimumValue="0" Type="Integer">*</asp:RangeValidator>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator12a" runat="server" ControlToValidate="PriceTextBox"
                        ErrorMessage="Price is a required field">*</asp:RequiredFieldValidator>
                </InsertItemTemplate>
                <ItemTemplate>
                    <asp:Label ID="LblPrice" runat="server" Text='<%# Bind("Price") %>'></asp:Label>
                </ItemTemplate>
            </asp:TemplateField> 
            
            <asp:TemplateField HeaderText="StockLevel">
                <EditItemTemplate>
                    <asp:TextBox ID="StockLevelTextBox" runat="server" Text='<%# Bind("StockLevel") %>' style="width: 70%"></asp:TextBox>
                    <asp:RangeValidator ID="RangeValidator9" runat="server" ControlToValidate="StockLevelTextBox"
                        ErrorMessage="StockLevel must be between 0 and 255" MaximumValue="255" MinimumValue="0" Type="Integer">*</asp:RangeValidator>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator13a" runat="server" ControlToValidate="StockLevelTextBox"
                        ErrorMessage="Stock is a required field">*</asp:RequiredFieldValidator>
                </EditItemTemplate>
                <InsertItemTemplate>
                    <asp:TextBox ID="StockLevelTextBox" runat="server" Text='<%# Bind("StockLevel") %>' style="width: 70%"></asp:TextBox>
                    <asp:RangeValidator ID="RangeValidator9" runat="server" ControlToValidate="StockLevelTextBox"
                        ErrorMessage="Display must be between 0 and 255" MaximumValue="255" MinimumValue="0" Type="Integer">*</asp:RangeValidator>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator13a" runat="server" ControlToValidate="StockLevelTextBox"
                        ErrorMessage="Stock is a required field">*</asp:RequiredFieldValidator>
                </InsertItemTemplate>
                <ItemTemplate>
                    <asp:Label ID="LblStockLevel" runat="server" Text='<%# Bind("StockLevel") %>'></asp:Label>
                </ItemTemplate>
            </asp:TemplateField> 

        </Fields>
        <EmptyDataTemplate>
            <asp:LinkButton ID="LinkButton1" runat="server" CommandArgument="New" CommandName="New">New</asp:LinkButton>
            - Create your first product
        </EmptyDataTemplate>
    </asp:DetailsView>
        <asp:GridView ID="GvCategory" runat="server" DataSourceID="ObjectDataSource2" Style="clear: none;
            float: right" AutoGenerateColumns="False">
            <Columns>
                <asp:BoundField DataField="CategoryID" HeaderText="CategoryID" />
                <asp:BoundField DataField="Name" HeaderText="Name" />
            </Columns>
            <EmptyDataTemplate>
                Please set up
                <asp:LinkButton ID="LnkCategory" runat="server" PostBackUrl="~/ManageCategories.aspx">categories</asp:LinkButton>
                before products.
            </EmptyDataTemplate>
        </asp:GridView>
        </div>
    </div>
    <br />
    &nbsp;&nbsp;<br />
    <br />
    <br />
    <br />
    <br />
    <br />
    <br />
    &nbsp; &nbsp;&nbsp;<br />
    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;<br />
    &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<br />
    &nbsp;<br />
    <br />
    <br />
    <br />
    <br />
    &nbsp;&nbsp;<br />
    &nbsp; &nbsp;<br />
    <br />
    <br />
    <br />
    <br />
</asp:Content>

