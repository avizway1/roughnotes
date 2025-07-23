import boto3
import json
import pymysql  # Example: using PyMySQL for MySQL database connection

def get_secret(secret_name, region_name):
    """
    Retrieve the secret value from AWS Secrets Manager.
    """
    # Create a Secrets Manager client
    session = boto3.session.Session()
    client = session.client(service_name='secretsmanager', region_name=region_name)
    
    try:
        get_secret_value_response = client.get_secret_value(SecretId=secret_name)
    except Exception as e:
        print(f"Error retrieving secret: {e}")
        raise e

    # Check if the secret is a string or binary and return as JSON object
    if 'SecretString' in get_secret_value_response:
        secret = get_secret_value_response['SecretString']
    else:
        secret = get_secret_value_response['SecretBinary'].decode('utf-8')
    
    return json.loads(secret)

def connect_to_database():
    secret_name = "my-database-secret"  # Replace with your secret name
    region_name = "us-east-1"           # Replace with your region
    
    # Retrieve secret
    secret = get_secret(secret_name, region_name)
    
    # Extract credentials (ensure your secret has these keys)
    db_username = secret['username']
    db_password = secret['password']
    db_host = secret['host']
    db_name = secret['dbname']
    
    # Connect to the database using PyMySQL
    try:
        connection = pymysql.connect(
            host=db_host,
            user=db_username,
            password=db_password,
            database=db_name,
            port=3306  # Change the port if needed
        )
        print("Connected to the database successfully!")
        # Use the connection as needed (e.g., query, update)
    except Exception as e:
        print(f"Database connection error: {e}")
    finally:
        # Close connection if it was established
        if 'connection' in locals() and connection.open:
            connection.close()

if __name__ == "__main__":
    connect_to_database()