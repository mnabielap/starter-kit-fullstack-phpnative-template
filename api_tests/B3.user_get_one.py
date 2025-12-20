import sys
import os

sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

token = utils.load_config("accessToken")
user_id = utils.load_config("target_user_id")

if not token:
    print("[ERROR] No access token.")
    sys.exit(1)
if not user_id:
    print("[ERROR] No target_user_id found. Run B1 first.")
    sys.exit(1)

url = f"{utils.BASE_URL}/users/{user_id}"
headers = {
    "Authorization": f"Bearer {token}"
}

response = utils.send_and_print(
    url=url,
    headers=headers,
    method="GET",
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)