import sys
import os

sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

token = utils.load_config("accessToken")
user_id = utils.load_config("target_user_id")

if not token or not user_id:
    print("[ERROR] Missing token or target_user_id.")
    sys.exit(1)

url = f"{utils.BASE_URL}/users/{user_id}"
headers = {
    "Authorization": f"Bearer {token}"
}
body = {
    "name": "Updated Name By Python Script"
}

response = utils.send_and_print(
    url=url,
    headers=headers,
    method="PATCH",
    body=body,
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)