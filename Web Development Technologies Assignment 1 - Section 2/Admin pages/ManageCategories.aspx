<%@ Page Language="C#" MasterPageFile="~/Master page/MasterPage.master" AutoEventWireup="true" CodeFile="ManageCategories.aspx.cs" Inherits="_Default" Title="Untitled Page" %>

<asp:Content ID="Content1" ContentPlaceHolderID="ContentPlaceHolder1" Runat="Server">
<script language="javascript" type="text/javascript">
// <!CDATA[

function DIV1_onclick() {

}

// ]]>
</script>

    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
    &nbsp; &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;
    &nbsp; &nbsp;&nbsp;<div style="clear: left; float: left; width: 315px; height: 100px; margin-left: 3%;">
        &nbsp;<asp:GridView ID="GvCategory" runat="server" AllowPaging="True" AllowSorting="True"  DataKeyNames="CategoryID"
            DataSourceID="ObjectDataSource1" OnSelectedIndexChanged="RowSelect" OnRowCommand="ItemCommand" style="text-align: center">
            <Columns>
                <asp:CommandField ShowSelectButton="True" ShowDeleteButton="True" />
            </Columns>
        </asp:GridView>
        <br />
        &nbsp;<br />
        <asp:ValidationSummary ID="ValidationSummary1" runat="server" Width="190px" />
        <br />
        &nbsp;&nbsp;
        <asp:Label ID="LblStatus" runat="server" Width="295px"></asp:Label>
        <br />
        <br />
    <asp:DetailsView ID="DvCategory" runat="server" AutoGenerateRows="False" DataSourceID="ObjectDataSource1"
     DataKeyNames="CategoryID" OnItemCommand="ItemCommand"
     
        Height="50px" Width="311px" AllowPaging="True">
        <Fields>
            <asp:CommandField ShowInsertButton="True" ShowEditButton="True" ShowDeleteButton="True" />

            <asp:TemplateField HeaderText="CategoryID">
                <EditItemTemplate>
                    <asp:Label ID="CategoryIDTextBox" runat="server" Text='<%# Bind("CategoryID") %>' style="width: 70%"></asp:Label>
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
                 
            <asp:TemplateField HeaderText="Name">
                <EditItemTemplate>
                    <asp:TextBox ID="NameTextBox" runat="server" Text='<%# Bind("Name") %>' MaxLength="50" style="width: 70px"></asp:TextBox>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator2a" runat="server" ControlToValidate="NameTextBox"
                        ErrorMessage="Name is a required field">*</asp:RequiredFieldValidator>
                </EditItemTemplate>
                <InsertItemTemplate>
                    <asp:TextBox ID="NameTextBox" runat="server" Text='<%# Bind("Name") %>' MaxLength="50" style="width: 70%"></asp:TextBox>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator2a" runat="server" ControlToValidate="NameTextBox"
                        ErrorMessage="Name is a required field">*</asp:RequiredFieldValidator>
                </InsertItemTemplate>
                <ItemTemplate>
                    <asp:Label ID="LblName" runat="server" Text='<%# Bind("Name") %>'></asp:Label>
                </ItemTemplate>
            </asp:TemplateField>
            
            <asp:TemplateField HeaderText="Description">
                <EditItemTemplate>
                    <asp:TextBox ID="DescriptionTextBox" runat="server" Text='<%# Bind("Description") %>' MaxLength="100" style="width: 70%" Rows="2" TextMode="MultiLine"></asp:TextBox>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator3a" runat="server" ControlToValidate="DescriptionTextBox"
                        ErrorMessage="Description is a required field">*</asp:RequiredFieldValidator>
                </EditItemTemplate>
                <InsertItemTemplate>
                    <asp:TextBox ID="DescriptionTextBox" runat="server" Text='<%# Bind("Description") %>' MaxLength="100" style="width: 70%" Rows="2" TextMode="MultiLine"></asp:TextBox>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator3a" runat="server" ControlToValidate="DescriptionTextBox"
                        ErrorMessage="Description is a required field">*</asp:RequiredFieldValidator>
                </InsertItemTemplate>
                <ItemTemplate>
                    <asp:Label ID="LblDescription" runat="server" Text='<%# Bind("Description") %>'></asp:Label>
                </ItemTemplate>
            </asp:TemplateField>                                                            


            <asp:TemplateField HeaderText="DisplaySeq">
                <EditItemTemplate>
                    <asp:TextBox ID="DisplaySeqTextBox" runat="server" Text='<%# Bind("DisplaySeq") %>' style="width: 70%"></asp:TextBox>
                    <asp:RangeValidator ID="RangeValidator3" runat="server" ControlToValidate="DisplaySeqTextBox"
                        ErrorMessage="DisplaySeq must be between 0 and 255" MaximumValue="255" MinimumValue="0" Type="Integer">*</asp:RangeValidator>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator4a" runat="server" ControlToValidate="DisplaySeqTextBox"
                        ErrorMessage="DisplaySeq is a required field">*</asp:RequiredFieldValidator><br />
                    &nbsp;
                </EditItemTemplate>
                <InsertItemTemplate>
                    <asp:TextBox ID="DisplaySeqTextBox" runat="server" Text='<%# Bind("DisplaySeq") %>' style="width: 70%"></asp:TextBox>
                    <asp:RangeValidator ID="RangeValidator3" runat="server" ControlToValidate="DisplaySeqTextBox"
                        ErrorMessage="DisplaySeq must be between 0 and 255" MaximumValue="255" MinimumValue="0" Type="Integer">*</asp:RangeValidator>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator4a" runat="server" ControlToValidate="DisplaySeqTextBox"
                        ErrorMessage="DisplaySeq is a required field">*</asp:RequiredFieldValidator>
                </InsertItemTemplate>
                <ItemTemplate>
                    <asp:Label ID="LblDisplaySeq" runat="server" Text='<%# Bind("DisplaySeq") %>'></asp:Label>
                </ItemTemplate>
            </asp:TemplateField>
            
            <asp:TemplateField HeaderText="CategoryParentID">
                <EditItemTemplate>
                    <asp:TextBox ID="CategoryParentIDTextBox" runat="server" Text='<%# Bind("CategoryParentID") %>' style="width: 70%"></asp:TextBox>
                    <asp:RangeValidator ID="RangeValidator9" runat="server" ControlToValidate="CategoryParentIDTextBox"
                        ErrorMessage="CategoryParentID must be between 0 and 255" MaximumValue="255" MinimumValue="0" Type="Integer">*</asp:RangeValidator>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator5a" runat="server" ControlToValidate="CategoryParentIDTextBox"
                        ErrorMessage="CategoryParentID is a required field">*</asp:RequiredFieldValidator>
                </EditItemTemplate>
                <InsertItemTemplate>
                    <asp:TextBox ID="CategoryParentIDTextBox" runat="server" Text='<%# Bind("CategoryParentID") %>' style="width: 70%"></asp:TextBox>
                    <asp:RangeValidator ID="RangeValidator9" runat="server" ControlToValidate="CategoryParentIDTextBox"
                        ErrorMessage="Display must be between 0 and 255" MaximumValue="255" MinimumValue="0" Type="Integer">*</asp:RangeValidator>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator5a" runat="server" ControlToValidate="CategoryParentIDTextBox"
                        ErrorMessage="CategoryParentID is a required field">*</asp:RequiredFieldValidator>
                </InsertItemTemplate>
                <ItemTemplate>
                    <asp:Label ID="LblCategoryParentID" runat="server" Text='<%# Bind("CategoryParentID") %>'></asp:Label>
                </ItemTemplate>
            </asp:TemplateField> 

        </Fields>
        <EmptyDataTemplate>
            <asp:LinkButton ID="LinkButton1" runat="server" CommandArgument="New" CommandName="New">New</asp:LinkButton>
            - Create your first category
        </EmptyDataTemplate>
    </asp:DetailsView>
    <asp:ObjectDataSource ID="ObjectDataSource1" runat="server" SelectMethod="GetCategories"
        TypeName="ProductClass.ProductClass" InsertMethod="InsertCategory" UpdateMethod="UpdateCategory"
         DeleteMethod="DeleteCategory" OnInserted="CatchError_ProductInserted" OnUpdated="CatchError_ProductUpdated" >
        <DeleteParameters>
            <asp:Parameter Name="CategoryID" Type="String" />
        </DeleteParameters>
        <InsertParameters>
            <asp:Parameter Name="CategoryID" Type="String" />
            <asp:Parameter Name="Name" Type="String" />
            <asp:Parameter Name="Description" Type="String" />
            <asp:Parameter Name="DisplaySeq" Type="String" />
            <asp:Parameter Name="CategoryParentID" Type="String" />
        </InsertParameters>
        <UpdateParameters>
            <asp:Parameter Name="CategoryID" Type="String" />
            <asp:Parameter Name="Name" Type="String" />
            <asp:Parameter Name="Description" Type="String" />
            <asp:Parameter Name="DisplaySeq" Type="String" />
            <asp:Parameter Name="CategoryParentID" Type="String" />
        </UpdateParameters>
    </asp:ObjectDataSource>
        <br />
        Warning: If you delete a category all associated products will be deleted automatically.</div>
    <br />
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

