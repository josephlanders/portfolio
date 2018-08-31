<%@ Page Language="C#" MasterPageFile="~/Master page/MasterPage.master" AutoEventWireup="true" CodeFile="ManageProfiles.aspx.cs" Inherits="_Default" Title="Untitled Page" %>

<asp:Content ID="Content1" ContentPlaceHolderID="ContentPlaceHolder1" Runat="Server">
<script language="javascript" type="text/javascript">
// <!CDATA[

function DIV1_onclick() {

}

// ]]>
</script>

    <div style="width: 100px; height: 100px">
    <div style="width: 315px; height: 100px; margin-left: 10%; margin-right: 10%;">
        <br />
        <asp:GridView ID="GvProfiles" runat="server" AllowPaging="True" AllowSorting="True"
            DataSourceID="ObjectDataSource1" OnSelectedIndexChanged="RowSelect" OnRowCommand="ItemCommand" style="text-align: center"  DataKeyNames="Username">
            <Columns>
                <asp:CommandField ShowSelectButton="True" ShowDeleteButton="True" />
            </Columns>
        </asp:GridView>
        <br />
        <asp:ValidationSummary ID="ValidationSummary1" runat="server" Width="190px" />
        <br />
        &nbsp;&nbsp;
        <asp:Label ID="LblStatus" runat="server" Width="295px"></asp:Label>
        <br />
        <br />
    <asp:DetailsView ID="DvProfiles" runat="server" AutoGenerateRows="False" DataSourceID="ObjectDataSource1"
     DataKeyNames="Username" OnItemCommand="ItemCommand"
     
        Height="50px" Width="311px" AllowPaging="True">
        <Fields>
            <asp:CommandField ShowInsertButton="True" ShowEditButton="True" ShowDeleteButton="True" />            
            
            <asp:TemplateField HeaderText="Username">
                <EditItemTemplate>
                    <asp:Label ID="LblUsername" runat="server" Text='<%# Bind("Username") %>' style="width: 70px"></asp:Label>
                </EditItemTemplate>
                <InsertItemTemplate>
                    <asp:TextBox ID="UsernameTextBox" runat="server" Text='<%# Bind("Username") %>' MaxLength="25" style="width: 70%"></asp:TextBox>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator1" runat="server" ErrorMessage="Username can not be empty"
                        Width="7px" ControlToValidate="UsernameTextBox">*</asp:RequiredFieldValidator>
                </InsertItemTemplate>
                <ItemTemplate>
                    <asp:Label ID="LblUsername" runat="server" Text='<%# Bind("Username") %>'></asp:Label>
                </ItemTemplate>
            </asp:TemplateField>
            
            <asp:TemplateField HeaderText="UserType">
                <EditItemTemplate>
                    <asp:TextBox ID="UserTypeTextBox" runat="server" Text='<%# Bind("UserType") %>' MaxLength="25" style="width: 70px"></asp:TextBox>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator6" runat="server" ControlToValidate="UserTypeTextBox"
                        ErrorMessage="User Type must not be empty">*</asp:RequiredFieldValidator><asp:RegularExpressionValidator
                            ID="RegularExpressionValidator2" runat="server" ControlToValidate="UserTypeTextBox"
                            ErrorMessage="UserType must be Customer or Admin" ValidationExpression="(Customer|Admin){1}">*</asp:RegularExpressionValidator>
                </EditItemTemplate>
                <InsertItemTemplate>
                    <asp:TextBox ID="UserTypeTextBox" runat="server" Text='<%# Bind("UserType") %>' MaxLength="25" style="width: 70%"></asp:TextBox>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator6" runat="server" ControlToValidate="UserTypeTextBox"
                        ErrorMessage="User Type must not be empty">*</asp:RequiredFieldValidator><asp:RegularExpressionValidator
                            ID="RegularExpressionValidator2" runat="server" ControlToValidate="UserTypeTextBox"
                            ErrorMessage="UserType must be Customer or Admin" ValidationExpression="(Customer|Admin){1}">*</asp:RegularExpressionValidator>
                </InsertItemTemplate>
                <ItemTemplate>
                    <asp:Label ID="LblUserType" runat="server" Text='<%# Bind("UserType") %>'></asp:Label>
                </ItemTemplate>
            </asp:TemplateField>
            
            <asp:TemplateField HeaderText="EmailAddress">
                <EditItemTemplate>
                    <asp:TextBox ID="EmailAddressTextBox" runat="server" Text='<%# Bind("EmailAddress") %>' MaxLength="25" style="width: 70px"></asp:TextBox>
                    <asp:RegularExpressionValidator ID="RegularExpressionValidator1" runat="server" ControlToValidate="EmailAddressTextBox"
                        ErrorMessage="Email address must be valid" ValidationExpression="\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*">*</asp:RegularExpressionValidator>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator3" runat="server" ControlToValidate="EmailAddressTextBox"
                        ErrorMessage="Email Address can not be empty">*</asp:RequiredFieldValidator>
                </EditItemTemplate>
                <InsertItemTemplate>
                    <asp:TextBox ID="EmailAddressTextBox" runat="server" Text='<%# Bind("EmailAddress") %>' MaxLength="25" style="width: 70%"></asp:TextBox>
                    <asp:RegularExpressionValidator ID="RegularExpressionValidator1" runat="server" ErrorMessage="Email address must be valid"
                        ValidationExpression="\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*" ControlToValidate="EmailAddressTextBox">*</asp:RegularExpressionValidator>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator3" runat="server" ControlToValidate="EmailAddressTextBox"
                        ErrorMessage="Email Address can not be empty">*</asp:RequiredFieldValidator>
                </InsertItemTemplate>
                <ItemTemplate>
                    <asp:Label ID="LblEmailAddress" runat="server" Text='<%# Bind("EmailAddress") %>'></asp:Label>
                </ItemTemplate>
            </asp:TemplateField>
            
            <asp:TemplateField HeaderText="Password">
                <EditItemTemplate>
                    <asp:TextBox ID="PasswordTextBox" runat="server" MaxLength="25" Style="width: 70px"
                        Text='<%# Bind("Password") %>'></asp:TextBox>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator2" runat="server" ControlToValidate="PasswordTextBox"
                        ErrorMessage="Password can not be empty">*</asp:RequiredFieldValidator>
                </EditItemTemplate>
                <InsertItemTemplate>
                    <asp:TextBox ID="PasswordTextBox" runat="server" MaxLength="25" Style="width: 70%"
                        Text='<%# Bind("Password") %>'></asp:TextBox>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator2" runat="server" ControlToValidate="PasswordTextBox"
                        ErrorMessage="Password can not be empty">*</asp:RequiredFieldValidator>
                </InsertItemTemplate>
                <ItemTemplate>
                    <asp:Label ID="LblPassword" runat="server" Text='<%# Bind("Password") %>'></asp:Label>
                </ItemTemplate>
            </asp:TemplateField>
            
           <asp:TemplateField HeaderText="Forename">
                <EditItemTemplate>
                    <asp:TextBox ID="ForenameTextBox" runat="server" Text='<%# Bind("Forename") %>' style="width: 70px"></asp:TextBox>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator4" runat="server" ControlToValidate="ForenameTextBox"
                        ErrorMessage="Forename is a required field">*</asp:RequiredFieldValidator>
                </EditItemTemplate>
                <InsertItemTemplate>
                    <asp:TextBox ID="ForenameTextBox" runat="server" Text='<%# Bind("Forename") %>' MaxLength="25" style="width: 70%"></asp:TextBox>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator4" runat="server" ControlToValidate="ForenameTextBox"
                        ErrorMessage="Forename is a required field">*</asp:RequiredFieldValidator>
                </InsertItemTemplate>
                <ItemTemplate>
                    <asp:Label ID="LblForename" runat="server" Text='<%# Bind("Forename") %>' style="width: 70px"></asp:Label>
                </ItemTemplate>
            </asp:TemplateField>
            
            <asp:TemplateField HeaderText="Surname">
                <EditItemTemplate>
                    <asp:TextBox ID="SurnameTextBox" runat="server" Text='<%# Bind("Surname") %>' style="width: 70px"></asp:TextBox>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator5" runat="server" ControlToValidate="SurnameTextBox"
                        ErrorMessage="Surname is a required field">*</asp:RequiredFieldValidator>
                </EditItemTemplate>
                <InsertItemTemplate>
                    <asp:TextBox ID="SurnameTextBox" runat="server" Text='<%# Bind("Surname") %>' MaxLength="25" style="width: 70%"></asp:TextBox>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator5" runat="server" ControlToValidate="SurnameTextBox"
                        ErrorMessage="Surname is a required field">*</asp:RequiredFieldValidator>
                </InsertItemTemplate>
                <ItemTemplate>
                    <asp:Label ID="LblSurname" runat="server" Text='<%# Bind("Surname") %>' style="width: 70px"></asp:Label>
                </ItemTemplate>
            </asp:TemplateField>
            

        </Fields>
        <EmptyDataTemplate>
            <asp:LinkButton ID="LinkButton1" runat="server" CommandArgument="New" CommandName="New">New</asp:LinkButton>
            - Create your first user profile
        </EmptyDataTemplate>
    </asp:DetailsView>
    <asp:ObjectDataSource ID="ObjectDataSource1" runat="server" SelectMethod="GetUsers"
        TypeName="ProductClass.ProductClass" InsertMethod="InsertUser" UpdateMethod="UpdateUser"
         DeleteMethod="DeleteUser" OnInserted="CatchError_ProductInserted" OnUpdated="CatchError_ProductUpdated" >
        <DeleteParameters>
            <asp:Parameter Name="Username" Type="String" />
        </DeleteParameters>
        <InsertParameters>
            <asp:Parameter Name="Username" Type="String" />
            <asp:Parameter Name="EmailAddress" Type="String" />
            <asp:Parameter Name="Password" Type="String" />
            <asp:Parameter Name="Forename" Type="String" />
            <asp:Parameter Name="Surname" Type="String" />
        </InsertParameters>
        <UpdateParameters>
            <asp:Parameter Name="Username" Type="String" />
            <asp:Parameter Name="UserType" Type="String" />
            <asp:Parameter Name="EmailAddress" Type="String" />
            <asp:Parameter Name="Password" Type="String" />
            <asp:Parameter Name="Forename" Type="String" />
            <asp:Parameter Name="Surname" Type="String" />
        </UpdateParameters>
    </asp:ObjectDataSource>
    </div>
    </div>

    <br />
    &nbsp; &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;
    &nbsp; &nbsp;&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br />
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

