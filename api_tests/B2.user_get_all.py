import sys
import os

sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

token = utils.load_config("accessToken")
if not token:
    print("[ERROR] No access token.")
    sys.exit(1)

# Query params example
url = f"{utils.BASE_URL}/users?limit=10&page=1&sortBy=created_at:desc"
headers = {
    "Authorization": f"Bearer {token}"
}

response = utils.send_and_print(
    url=url,
    headers=headers,
    method="GET",
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)