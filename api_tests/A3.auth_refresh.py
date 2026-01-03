import sys
import os
sys.path.append(os.path.abspath(os.path.dirname(__file__)))
from utils import send_and_print, BASE_URL, save_config, load_config

print("--- REFRESHING TOKENS ---")

url = f"{BASE_URL}/auth/refresh-tokens"
refresh_token = load_config("refreshToken")

if not refresh_token:
    print("Error: No refresh token found. Run A2.auth_login.py first.")
    sys.exit(1)

payload = {
    "refreshToken": refresh_token
}

response = send_and_print(
    url=url,
    method="POST",
    body=payload,
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)

if response.status_code == 200:
    data = response.json()
    save_config("accessToken", data['access']['token'])
    save_config("refreshToken", data['refresh']['token'])
    print(">>> Tokens refreshed and saved.")