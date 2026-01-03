import sys
import os
sys.path.append(os.path.abspath(os.path.dirname(__file__)))
from utils import send_and_print, BASE_URL, load_config

print("--- GET ALL USERS ---")

token = load_config("accessToken")
if not token:
    print("Error: No access token. Run A2.auth_login.py first.")
    sys.exit(1)

# Query parameters: Page 1, Limit 10, Sort by created_at DESC
url = f"{BASE_URL}/users?page=1&limit=10&sortBy=created_at:desc"
headers = {
    "Authorization": f"Bearer {token}"
}

response = send_and_print(
    url=url,
    headers=headers,
    method="GET",
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)