<%@ Page Language="C#" MasterPageFile="~/Master page/MasterPage.master" AutoEventWireup="true" CodeFile="CreateProfile.aspx.cs" Inherits="CreateProfile" Title="Untitled Page" %>

<%@ Register Assembly="AjaxControlToolkit" Namespace="AjaxControlToolkit" TagPrefix="cc1" %>
<asp:Content ID="Content1" ContentPlaceHolderID="ContentPlaceHolder1" Runat="Server">
    &nbsp;
    <asp:Label ID="LblInfo" runat="server" Text="You must log out to create a new profile"></asp:Label>
    <div style="margin-left: 20%; width: 100%; margin-right: 20%; height: 100%">
    <asp:ValidationSummary ID="ValidationSummary1" runat="server" Width="190px" style="width: 100%" />
    <asp:Label ID="LblStatus" runat="server" Width="100%"></asp:Label><asp:DetailsView
        ID="DvProfile" runat="server" AllowPaging="True" AutoGenerateRows="False"
        DataKeyNames="Username" DataSourceID="ObjectDataSource1" Height="50px" OnItemCommand="ItemCommand"
        Width="408px" DefaultMode="Insert">
        <EmptyDataTemplate>
            <asp:LinkButton ID="LinkButton1" runat="server" CommandArgument="New" CommandName="New">New</asp:LinkButton>
            - Create your first product
        </EmptyDataTemplate>
        <Fields>
            <asp:CommandField ShowInsertButton="True" />

            <asp:TemplateField HeaderText="Username">
                <EditItemTemplate>
                    <asp:Label ID="LblUsername" runat="server" Text='<%# Bind("Username") %>' style="width: 70px"></asp:Label>
                </EditItemTemplate>
                <InsertItemTemplate>
                    <asp:TextBox ID="UsernameTextBox" runat="server" Text='<%# Bind("Username") %>' MaxLength="25" style="width: 50%"></asp:TextBox>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator1" runat="server" ErrorMessage="Username can not be empty"
                        Width="7px" ControlToValidate="UsernameTextBox">*</asp:RequiredFieldValidator>
                </InsertItemTemplate>
                <ItemTemplate>
                    <asp:Label ID="LblUsername" runat="server" Text='<%# Bind("Username") %>'></asp:Label>
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
                    <asp:TextBox ID="EmailAddressTextBox" runat="server" Text='<%# Bind("EmailAddress") %>' MaxLength="25" style="width: 50%"></asp:TextBox>
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
                        Text='<%# Bind("Password") %>' TextMode="Password"></asp:TextBox>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator2" runat="server" ControlToValidate="PasswordTextBox"
                        ErrorMessage="Password can not be empty">*</asp:RequiredFieldValidator>
                    <cc1:passwordstrength id="PasswordStrength1" runat="server" targetcontrolid="PasswordTextBox"></cc1:passwordstrength>
                </EditItemTemplate>
                <InsertItemTemplate>
                    <asp:TextBox ID="PasswordTextBox" runat="server" MaxLength="25" Style="width: 30%"
                        Text='<%# Bind("Password") %>' TextMode="Password"></asp:TextBox>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator2" runat="server" ControlToValidate="PasswordTextBox"
                        ErrorMessage="Password can not be empty">*</asp:RequiredFieldValidator>
                    <cc1:passwordstrength id="PasswordStrength2" runat="server" targetcontrolid="PasswordTextBox"></cc1:passwordstrength>
                </InsertItemTemplate>
                <ItemTemplate>
                    <asp:Label ID="LblPassword" runat="server" Text='<%# Bind("Password") %>'></asp:Label>
                </ItemTemplate>
            </asp:TemplateField>
           <asp:TemplateField HeaderText="Forename">
                <EditItemTemplate>
                    <asp:TextBox ID="ForenameTextBox" runat="server" Text='<%# Bind("Forename") %>' style="width: 70px"></asp:TextBox>
                </EditItemTemplate>
                <InsertItemTemplate>
                    <asp:TextBox ID="ForenameTextBox" runat="server" Text='<%# Bind("Forename") %>' MaxLength="25" style="width: 50%"></asp:TextBox>
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
                </EditItemTemplate>
                <InsertItemTemplate>
                    <asp:TextBox ID="SurnameTextBox" runat="server" Text='<%# Bind("Surname") %>' MaxLength="25" style="width: 50%"></asp:TextBox>
                    <asp:RequiredFieldValidator ID="RequiredFieldValidator5" runat="server" ControlToValidate="SurnameTextBox"
                        ErrorMessage="Surname is a required field">*</asp:RequiredFieldValidator>
                </InsertItemTemplate>
                <ItemTemplate>
                    <asp:Label ID="LblSurname" runat="server" Text='<%# Bind("Surname") %>' style="width: 70px"></asp:Label>
                </ItemTemplate>
            </asp:TemplateField>
        </Fields>
    </asp:DetailsView>
    <asp:ObjectDataSource ID="ObjectDataSource1" runat="server"
        InsertMethod="InsertUser" OnInserted="CatchError_ProductInserted" OnUpdated="CatchError_ProductUpdated"
        SelectMethod="GetUsers" TypeName="ProductClass.ProductClass">
        <InsertParameters>
            <asp:Parameter Name="Username" Type="String" />
            <asp:Parameter Name="EmailAddress" Type="String" />
            <asp:Parameter Name="Password" Type="String" />
            <asp:Parameter Name="Forename" Type="String" />
            <asp:Parameter Name="Surname" Type="String" />
        </InsertParameters>
    </asp:ObjectDataSource>
    </div>
    &nbsp;
</asp:Content>

