import sys
import os
import time
import random

sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

# Load Admin Token
token = utils.load_config("accessToken")
if not token:
    print("[ERROR] No access token. Run B0.admin_login.py first.")
    sys.exit(1)

# Generate unique user data
timestamp = int(time.time())
random_number = random.randint(1000, 9999)
email = f"created_by_admin_{timestamp}@example.com"

url = f"{utils.BASE_URL}/users"
headers = {
    "Authorization": f"Bearer {token}"
}
body = {
    "name": f"Admin Created User {random_number}",
    "email": email,
    "password": "password1",
    "role": "user"
}

response = utils.send_and_print(
    url=url,
    headers=headers,
    method="POST",
    body=body,
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)

if response.status_code == 201:
    data = response.json()
    user_id = data.get("id")
    # Save ID for Get/Update/Delete tests
    utils.save_config("target_user_id", user_id)
    print(f"[INFO] Created User ID {user_id} saved to config.")