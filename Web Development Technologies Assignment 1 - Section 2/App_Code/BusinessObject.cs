using System;
using System.Web;
using System.Data;
using System.Data.SqlClient;

/* 
 * Assignment 1 for Web Development Technologies COSC2276
 * Coded by Joseph Peter Landers
 * s3163776@student.rmit.edu.au
 * josephlanders@gmail.com
 *
 * <summary>
 * This class is a business object
 * It provides functions to the objectdatasource control
 * and calls stored procedures on the database
 * </summary>
 */
namespace ProductClass
{
    public class ProductClass
    {
        //Define variables
        private DataSet ds = null;
        private SqlConnection conn = null;
        private SqlCommand cmd = null;
        private SqlDataAdapter da = null;
        private int rows = 0;

        public ProductClass()
        {
            
        }

        public DataSet GetProducts ()
        {
            //Set variables
            conn = null;
            ds = new DataSet("jlanders");
            cmd = null;
            da = null;
            
            try
            {
                //Open connection
                conn = new
                    SqlConnection("Data Source=ZLAPTOP;Initial Catalog=jlanders;Integrated Security=True");

                //create SQL command
                cmd = new SqlCommand(
                    "GetAllProducts", conn);

                cmd.CommandType = CommandType.StoredProcedure;
                
                da = new SqlDataAdapter(cmd);

                //Fill the dataset
                da.Fill(ds, "Products");
            }
            finally
            {
            }
            return ds;
        }


        public DataSet GetProductsByCategory(String CategoryID)
        {
            //Set variables
            conn = null;
            ds = new DataSet("jlanders");
            cmd = null;
            da = null;

            ds = new DataSet("jlanders");

            try
            {
                //Open connection
                conn = new
                    SqlConnection("Data Source=ZLAPTOP;Initial Catalog=jlanders;Integrated Security=True");

                //Create SQL Command
                cmd = new SqlCommand(
                    "GetProductsByCategory", conn);

                cmd.CommandType = CommandType.StoredProcedure;
                cmd.Parameters.Add("@CategoryID", SqlDbType.Int).Value = CategoryID;

                da = new SqlDataAdapter(cmd);

                //Fill the dataset
                da.Fill(ds, "Products");
            }
            finally
            {
            }
            return ds;
        }

        public void InsertProduct(String ProductID, String Name,
            String ParentProductID, String DisplaySeq, String CategoryID, String ProductGroupID,
            String Display, String ShortDescription, String LongDescription, String Image,
            String Price, String StockLevel)
        {
            //Set variables
            conn = null;
            cmd = null;
            rows = 0;

            //Open connection
            conn = new SqlConnection("Data Source=ZLAPTOP;Initial Catalog=jlanders;Integrated Security=True");

            //Create SQL command
            cmd = new SqlCommand("InsertProduct", conn);

            cmd.CommandType = CommandType.StoredProcedure;
            cmd.Parameters.Add("@ProductID", SqlDbType.Int).Value = ProductID;
            cmd.Parameters.Add("@Name", SqlDbType.VarChar).Value = Name;
            cmd.Parameters.Add("@ParentProductID", SqlDbType.Int).Value = ParentProductID;
            cmd.Parameters.Add("@DisplaySeq", SqlDbType.SmallInt).Value = DisplaySeq;
            cmd.Parameters.Add("@CategoryID", SqlDbType.Int).Value = CategoryID;
            cmd.Parameters.Add("@ProductGroupID", SqlDbType.Int).Value = ProductGroupID;
            cmd.Parameters.Add("@Display", SqlDbType.SmallInt).Value = Display;
            cmd.Parameters.Add("@ShortDescription", SqlDbType.VarChar).Value = ShortDescription;
            cmd.Parameters.Add("@LongDescription", SqlDbType.VarChar).Value = LongDescription;
            cmd.Parameters.Add("@Image", SqlDbType.VarChar).Value = Image;
            cmd.Parameters.Add("@Price", SqlDbType.Decimal).Value = Price;
            cmd.Parameters.Add("@StockLevel", SqlDbType.Int).Value = StockLevel;

            conn.Open();

            //Execute the SQL command
            rows = cmd.ExecuteNonQuery();

            conn.Close();
        }

        public void UpdateProduct(String ProductID, String Name,
            String ParentProductID, String DisplaySeq, String CategoryID, String ProductGroupID,
            String Display, String ShortDescription, String LongDescription, String Image,
            String Price, String StockLevel)
        {
            //Set variables
            conn = null;
            cmd = null;
            rows = 0;

            //Open connection
            conn = new SqlConnection("Data Source=ZLAPTOP;Initial Catalog=jlanders;Integrated Security=True");

            //Create SQL command
            cmd = new SqlCommand("UpdateProduct", conn);

            cmd.CommandType = CommandType.StoredProcedure;
            cmd.Parameters.Add("@ProductID", SqlDbType.Int).Value = ProductID;
            cmd.Parameters.Add("@Name", SqlDbType.VarChar).Value = Name;
            cmd.Parameters.Add("@ParentProductID", SqlDbType.Int).Value = ParentProductID;
            cmd.Parameters.Add("@DisplaySeq", SqlDbType.SmallInt).Value = DisplaySeq;
            cmd.Parameters.Add("@CategoryID", SqlDbType.Int).Value = CategoryID;
            cmd.Parameters.Add("@ProductGroupID", SqlDbType.Int).Value = ProductGroupID;
            cmd.Parameters.Add("@Display", SqlDbType.SmallInt).Value = Display;
            cmd.Parameters.Add("@ShortDescription", SqlDbType.VarChar).Value = ShortDescription;
            cmd.Parameters.Add("@LongDescription", SqlDbType.VarChar).Value = LongDescription;
            cmd.Parameters.Add("@Image", SqlDbType.VarChar).Value = Image;
            cmd.Parameters.Add("@Price", SqlDbType.Decimal).Value = Price;
            cmd.Parameters.Add("@StockLevel", SqlDbType.Int).Value = StockLevel;

            conn.Open();

            //Execute query
            rows = cmd.ExecuteNonQuery();

            conn.Close();
        }

        public void DeleteProduct(String ProductID)
        {
            //Set variables
            conn = null;
            cmd = null;
            rows = 0;

            //Open connection
            conn = new SqlConnection("Data Source=ZLAPTOP;Initial Catalog=jlanders;Integrated Security=True");

            //Create SQL command
            cmd = new SqlCommand("DeleteProduct", conn);

            cmd.CommandType = CommandType.StoredProcedure;
            cmd.Parameters.Add("@ProductID", SqlDbType.Int).Value = ProductID;


            conn.Open();

            //Execute query
            rows = cmd.ExecuteNonQuery();

            conn.Close();
        }

        public DataSet GetUsers()
        {
            //Set variables
            conn = null;
            ds = new DataSet("jlanders");
            cmd = null;
            da = null;

            try
            {
                //Open connection
                conn = new
                    SqlConnection("Data Source=ZLAPTOP;Initial Catalog=jlanders;Integrated Security=True");

                //Create SQL command
                cmd = new SqlCommand("GetAllUsers", conn);

                cmd.CommandType = CommandType.StoredProcedure;


                da = new SqlDataAdapter(cmd);

                //Fill dataset
                da.Fill(ds, "Users");
            }
            finally
            {
            }
            return ds;
        }

        public void InsertUser(String Username,
            String EmailAddress, String Password,
            String Forename, String Surname)
        {
            InsertUser(Username, "Customer", EmailAddress, Password, Forename, Surname);
        }

        public void InsertUser(String Username, String UserType,
            String EmailAddress, String Password,
            String Forename, String Surname)
        {
            //Set variables
            conn = null;
            cmd = null;
            rows = 0;

            //Open connection
            conn = new SqlConnection("Data Source=ZLAPTOP;Initial Catalog=jlanders;Integrated Security=True");

            //Create SQL command
            cmd = new SqlCommand("InsertUser", conn);

            cmd.CommandType = CommandType.StoredProcedure;
            cmd.Parameters.Add("@Username", SqlDbType.VarChar).Value = Username;
            cmd.Parameters.Add("@UserType", SqlDbType.VarChar).Value = UserType;
            cmd.Parameters.Add("@EmailAddress", SqlDbType.VarChar).Value = EmailAddress;
            cmd.Parameters.Add("@Password", SqlDbType.VarChar).Value = Password;
            cmd.Parameters.Add("@Forename", SqlDbType.VarChar).Value = Forename;
            cmd.Parameters.Add("@Surname", SqlDbType.VarChar).Value = Surname;

            conn.Open();

            //Execute SQL command
            rows = cmd.ExecuteNonQuery();

            conn.Close();
        }

        public void UpdateUser(String Username, String UserType,
            String EmailAddress, String Password,
            String Forename, String Surname)
        {
            //Set variables
            conn = null;
            cmd = null;
            rows = 0;

            //Open connection
            conn = new SqlConnection("Data Source=ZLAPTOP;Initial Catalog=jlanders;Integrated Security=True");

            //Create SQL command
            cmd = new SqlCommand("UpdateUser", conn);

            cmd.CommandType = CommandType.StoredProcedure;
            cmd.Parameters.Add("@Username", SqlDbType.VarChar).Value = Username;
            cmd.Parameters.Add("@UserType", SqlDbType.VarChar).Value = UserType;
            cmd.Parameters.Add("@EmailAddress", SqlDbType.VarChar).Value = EmailAddress;
            cmd.Parameters.Add("@Password", SqlDbType.VarChar).Value = Password;
            cmd.Parameters.Add("@Forename", SqlDbType.VarChar).Value = Forename;
            cmd.Parameters.Add("@Surname", SqlDbType.VarChar).Value = Surname;

            conn.Open();

            //Execute query
            rows = cmd.ExecuteNonQuery();

            conn.Close();
        }

        public void DeleteUser(String Username)
        {
            //Set variables
            conn = null;
            cmd = null;
            rows = 0;

            //Open connection
            conn = new SqlConnection("Data Source=ZLAPTOP;Initial Catalog=jlanders;Integrated Security=True");

            //Create SQL command
            cmd = new SqlCommand("DeleteUser", conn);

            cmd.CommandType = CommandType.StoredProcedure;
            cmd.Parameters.Add("@Username", SqlDbType.VarChar).Value = Username;

            conn.Open();

            //Execute query
            rows = cmd.ExecuteNonQuery();

            conn.Close();
        }


        public DataSet GetCategories()
        {
            //Set variables
            conn = null;
            ds = new DataSet("jlanders");
            cmd = null;
            da = null;

            try
            {
                //Open connection
                conn = new
                    SqlConnection("Data Source=ZLAPTOP;Initial Catalog=jlanders;Integrated Security=True");

                //Create SQL command
                cmd = new SqlCommand("GetAllCategories", conn);

                cmd.CommandType = CommandType.StoredProcedure;

                da = new SqlDataAdapter(cmd);

                //Fill dataset
                da.Fill(ds, "Categories");
            }
            finally
            {
            }
            return ds;
        }

        public void InsertCategory(String CategoryID, String Name,
            String Description, String DisplaySeq, String CategoryParentID)
        {
            //Set variables
            conn = null;
            cmd = null;
            rows = 0;

            //Open SQL connection
            conn = new SqlConnection("Data Source=ZLAPTOP;Initial Catalog=jlanders;Integrated Security=True");

            //Create SQL command
            cmd = new SqlCommand("InsertCategory", conn);

            cmd.CommandType = CommandType.StoredProcedure;
            cmd.Parameters.Add("@CategoryID", SqlDbType.Int).Value = CategoryID;
            cmd.Parameters.Add("@Name", SqlDbType.VarChar).Value = Name;
            cmd.Parameters.Add("@Description", SqlDbType.VarChar).Value = Description;
            cmd.Parameters.Add("@DisplaySeq", SqlDbType.SmallInt).Value = DisplaySeq;
            cmd.Parameters.Add("@CategoryParentID", SqlDbType.Int).Value = CategoryParentID;

            conn.Open();

            //Execute SQL query
            rows = cmd.ExecuteNonQuery();

            conn.Close();
        }

        public void UpdateCategory(String CategoryID, String Name,
            String Description, String DisplaySeq, String CategoryParentID)
        {
            //Set variables
            conn = null;
            cmd = null;
            rows = 0;
            
            //Open SQL connection
            conn = new SqlConnection("Data Source=ZLAPTOP;Initial Catalog=jlanders;Integrated Security=True");

            //Create SQL command
            cmd = new SqlCommand("UpdateCategory", conn);

            cmd.CommandType = CommandType.StoredProcedure;
            cmd.Parameters.Add("@CategoryID", SqlDbType.Int).Value = CategoryID;
            cmd.Parameters.Add("@Name", SqlDbType.VarChar).Value = Name;
            cmd.Parameters.Add("@Description", SqlDbType.VarChar).Value = Description;
            cmd.Parameters.Add("@DisplaySeq", SqlDbType.SmallInt).Value = DisplaySeq;
            cmd.Parameters.Add("@CategoryParentID", SqlDbType.Int).Value = CategoryParentID;

            conn.Open();

            //Execute SQL query
            rows = cmd.ExecuteNonQuery();

            conn.Close();
        }

        public void DeleteCategory(String CategoryID)
        {
            //Set variables
            conn = null;
            cmd = null;
            rows = 0;

            //Open SQL connection
            conn = new SqlConnection("Data Source=ZLAPTOP;Initial Catalog=jlanders;Integrated Security=True");

            //Create SQL command
            cmd = new SqlCommand("DeleteCategory", conn);

            cmd.CommandType = CommandType.StoredProcedure;
            cmd.Parameters.Add("@CategoryID", SqlDbType.Int).Value = CategoryID;

            conn.Open();

            //Execute SQL query
            rows = cmd.ExecuteNonQuery();

            conn.Close();
        }

        //String OrderNo, AUTO INCREMENT USED so not ORDERNO reqd
        public void InsertOrder( String Username,
    String ProductID, String Quantity, String Price, String ShippingName,
    String ShippingAddress, String ShippingType)
        {
            //Set variables
            conn = null;
            cmd = null;
            rows = 0;

            //Open connection
            conn = new SqlConnection("Data Source=ZLAPTOP;Initial Catalog=jlanders;Integrated Security=True");

            //Create SQL command
            cmd = new SqlCommand("InsertOrder", conn);

            cmd.CommandType = CommandType.StoredProcedure;
            //cmd.Parameters.Add("@OrderNo", SqlDbType.Int).Value = OrderNo;
            cmd.Parameters.Add("@Username", SqlDbType.VarChar).Value = Username;
            cmd.Parameters.Add("@ProductID", SqlDbType.Int).Value = ProductID;
            cmd.Parameters.Add("@Quantity", SqlDbType.Int).Value = Quantity;
            cmd.Parameters.Add("@Price", SqlDbType.Int).Value = Price;
            cmd.Parameters.Add("@ShippingName", SqlDbType.VarChar).Value = ShippingName;
            cmd.Parameters.Add("@ShippingAddress", SqlDbType.VarChar).Value = ShippingAddress;
            cmd.Parameters.Add("@ShippingType", SqlDbType.VarChar).Value = ShippingType;
            conn.Open();

            //Execute the SQL command
            rows = cmd.ExecuteNonQuery();

            conn.Close();
        }

        public void UpdateProductStockLevel(String ProductID, String StockLevel)
        {
            //Set variables
            conn = null;
            cmd = null;
            rows = 0;

            //Open SQL connection
            conn = new SqlConnection("Data Source=ZLAPTOP;Initial Catalog=jlanders;Integrated Security=True");

            //Create SQL command
            cmd = new SqlCommand("UpdateProductStockLevel", conn);

            cmd.CommandType = CommandType.StoredProcedure;
            cmd.Parameters.Add("@ProductID", SqlDbType.Int).Value = ProductID;
            cmd.Parameters.Add("@StockLevel", SqlDbType.Int).Value = StockLevel;

            conn.Open();

            //Execute SQL query
            rows = cmd.ExecuteNonQuery();

            conn.Close();
        }
    }
}
