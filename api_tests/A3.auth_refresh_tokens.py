import sys
import os

sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

# Load refresh token
refresh_token = utils.load_config("refreshToken")

if not refresh_token:
    print("[ERROR] No refresh token found in secrets.json. Run A2 first.")
    sys.exit(1)

url = f"{utils.BASE_URL}/auth/refresh-tokens"
body = {
    "refreshToken": refresh_token
}

response = utils.send_and_print(
    url=url,
    method="POST",
    body=body,
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)

if response.status_code == 200:
    data = response.json()
    # Update tokens in config
    if "access" in data:
        utils.save_config("accessToken", data["access"]["token"])
    if "refresh" in data:
        utils.save_config("refreshToken", data["refresh"]["token"])
    print("[INFO] Tokens refreshed and saved.")