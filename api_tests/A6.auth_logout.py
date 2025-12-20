import sys
import os

sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

refresh_token = utils.load_config("refreshToken")

if not refresh_token:
    print("[ERROR] No refresh token found.")
    sys.exit(1)

url = f"{utils.BASE_URL}/auth/logout"
body = {
    "refreshToken": refresh_token
}

response = utils.send_and_print(
    url=url,
    method="POST",
    body=body,
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)

if response.status_code == 204:
    # Clear tokens locally since they are invalidated
    utils.save_config("accessToken", "")
    utils.save_config("refreshToken", "")
    print("[INFO] Logout successful. Local tokens cleared.")