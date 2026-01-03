import sys
import os
import time
sys.path.append(os.path.abspath(os.path.dirname(__file__)))
from utils import send_and_print, BASE_URL, save_config

# Generate a unique email to avoid conflict
unique_id = int(time.time())
email = f"testuser_{unique_id}@example.com"

print(f"--- REGISTERING NEW USER: {email} ---")

url = f"{BASE_URL}/auth/register"

payload = {
    "name": "Test User Automator",
    "email": email,
    "password": "password123",
    "role": "user",
}

response = send_and_print(
    url=url,
    method="POST",
    body=payload,
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)

# Optional: Save tokens if you want to use this user immediately
if response.status_code == 201:
    data = response.json()
    save_config("accessToken", data['tokens']['access']['token'])
    save_config("refreshToken", data['tokens']['refresh']['token'])
    print(">>> Registration successful. Tokens saved to secrets.json.")