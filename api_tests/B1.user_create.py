import sys
import os
import time
sys.path.append(os.path.abspath(os.path.dirname(__file__)))
from utils import send_and_print, BASE_URL, load_config, save_config

print("--- CREATE USER (ADMIN) ---")

token = load_config("accessToken")
if not token:
    print("Error: No access token. Run A2.auth_login.py first.")
    sys.exit(1)

# Unique email
unique_id = int(time.time())
email = f"created_by_admin_{unique_id}@example.com"

url = f"{BASE_URL}/users"
headers = {
    "Authorization": f"Bearer {token}"
}
payload = {
    "name": "Created Via Python",
    "email": email,
    "password": "password123",
    "role": "user"
}

response = send_and_print(
    url=url,
    headers=headers,
    method="POST",
    body=payload,
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)

if response.status_code == 201:
    data = response.json()
    # Save this ID to use in Get/Update/Delete scripts
    save_config("target_user_id", data['id'])
    print(f">>> User created with ID {data['id']}. ID saved to secrets.json for testing B3/B4/B5.")