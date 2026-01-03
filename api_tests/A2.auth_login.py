import sys
import os
sys.path.append(os.path.abspath(os.path.dirname(__file__)))
from utils import send_and_print, BASE_URL, save_config

print("--- LOGGING IN (AS ADMIN) ---")

url = f"{BASE_URL}/auth/login"

# Using default admin credentials to ensure we have permissions for B* scripts
payload = {
    "email": "admin@example.com", 
    "password": "password123" 
}

response = send_and_print(
    url=url,
    method="POST",
    body=payload,
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)

if response.status_code == 200:
    data = response.json()
    # Save tokens to secrets.json for subsequent requests
    save_config("accessToken", data['tokens']['access']['token'])
    save_config("refreshToken", data['tokens']['refresh']['token'])
    print(">>> Login successful. Access and Refresh tokens saved.")
else:
    print(">>> Login Failed.")