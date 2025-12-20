import sys
import os
import time
import random

# Add current directory to sys.path to import utils
sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

# Generate unique user data
timestamp = int(time.time())
random_number = random.randint(100, 999)
email = f"user_{timestamp}_{random_number}@example.com"
password = "password1"
name = f"Test User {random_number}"

# Save this email to config so A2 can login with it
utils.save_config("last_registered_email", email)
utils.save_config("last_registered_password", password)

url = f"{utils.BASE_URL}/auth/register"
body = {
    "name": name,
    "email": email,
    "password": password
}

print(f"--- Attempting to register: {email} ---")

response = utils.send_and_print(
    url=url,
    method="POST",
    body=body,
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)

# If successful, we can optionally save tokens, but A2 will test login explicitly.
if response.status_code == 201:
    data = response.json()
    print("\n[SUCCESS] User registered.")