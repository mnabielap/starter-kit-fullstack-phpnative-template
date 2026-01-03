import sys
import os
sys.path.append(os.path.abspath(os.path.dirname(__file__)))
from utils import send_and_print, BASE_URL, load_config

print("--- DELETE USER ---")

token = load_config("accessToken")
target_id = load_config("target_user_id")

if not token:
    print("Error: No access token. Run A2.auth_login.py first.")
    sys.exit(1)
if not target_id:
    print("Error: No target User ID. Run B1.user_create.py first.")
    sys.exit(1)

url = f"{BASE_URL}/users/{target_id}"
headers = {
    "Authorization": f"Bearer {token}"
}

response = send_and_print(
    url=url,
    headers=headers,
    method="DELETE",
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)

if response.status_code == 204:
    print(">>> User successfully deleted.")