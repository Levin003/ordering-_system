import qrcode
import requests
import mysql.connector
import uuid

# Function to create the tables if not exists
def create_tables_if_not_exist(cursor):
    cursor.execute('''CREATE TABLE IF NOT EXISTS customers (
                        customer_id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255),
                        email VARCHAR(255),
                        phone_number VARCHAR(255)
                    )''')
    cursor.execute('''CREATE TABLE IF NOT EXISTS table_assignments (
                        assignment_id INT AUTO_INCREMENT PRIMARY KEY,
                        table_number INT NOT NULL,
                        customer_id INT NOT NULL,
                        UNIQUE (table_number)
                    )''')

# Function to generate a unique identifier for the customer
def generate_unique_identifier():
    return str(uuid.uuid4())

# Function to assign a table to a customer
def assign_table_to_customer(table_number, customer_id, db_connection):
    cursor = db_connection.cursor()
    cursor.execute("SELECT * FROM table_assignments WHERE table_number = %s", (table_number,))
    if cursor.fetchone() is None:
        cursor.execute("INSERT INTO table_assignments (table_number, customer_id) VALUES (%s, %s)",
                       (table_number, customer_id))
        db_connection.commit()
        print(f"Table {table_number} assigned to customer {customer_id}.")
    else:
        print(f"Table {table_number} is already assigned.")


# Function to send customer ID to backend server
def send_customer_id_to_backend(customer_id, table_number):
    backend_url = "http://qrcodefoodorderingsystem.onlinewebshop.net//route=/sql&pos=0&db=qr_code_food_ordering&table=orders"
    data = {"customer_id": customer_id, "table_number": table_number}
    response = requests.post(backend_url, json=data)
    if response.status_code == 200:
        print("Customer ID sent to backend successfully.")
    else:
        print("Failed to send customer ID to backend.")

# Function to generate QR code
def generate_custom_qr_code(table_number, db_connection):
    unique_identifier = generate_unique_identifier()
    assign_table_to_customer(table_number, unique_identifier, db_connection)
    send_customer_id_to_backend(unique_identifier, table_number)
    url = f"http://qrcodefoodorderingsystem.onlinewebshop.net//?identifier={unique_identifier}&table={table_number}"
    qr = qrcode.QRCode(version=1, error_correction=qrcode.constants.ERROR_CORRECT_L, box_size=10, border=4)
    qr.add_data(url)
    qr.make(fit=True)
    img = qr.make_image(fill_color="black", back_color="white")
    img.save(f"table_{table_number}_qr.png")
    print(f"QR code for Table {table_number} generated successfully. Customer should scan {url}")

# Main function
def main():
    # Connect to the MySQL database (assuming XAMPP is running on default port)
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",  # Enter your MySQL password here
        database="qr_code_food_ordering"  # Enter your database name here
    )

    cursor = conn.cursor()
    create_tables_if_not_exist(cursor)

    # Generate QR codes for tables 1 to 4
    for table_number in range(1, 5):
        generate_custom_qr_code(table_number, conn)

    # Close database connection
    conn.close()

# Entry point of the script
if __name__ == "__main__":
    main()
