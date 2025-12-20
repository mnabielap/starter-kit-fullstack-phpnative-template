import sys
import os

sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

# Load the email registered in A1, or use a default
email = utils.load_config("last_registered_email") or "admin@example.com"
password = utils.load_config("last_registered_password") or "password1"

url = f"{utils.BASE_URL}/auth/login"
body = {
    "email": email,
    "password": password
}

print(f"--- Logging in as: {email} ---")

response = utils.send_and_print(
    url=url,
    method="POST",
    body=body,
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)

if response.status_code == 200:
    data = response.json()
    tokens = data.get("tokens", {})
    
    # Save tokens for subsequent requests
    if "access" in tokens:
        utils.save_config("accessToken", tokens["access"]["token"])
        print("[INFO] Access Token saved to secrets.json")
    
    if "refresh" in tokens:
        utils.save_config("refreshToken", tokens["refresh"]["token"])
        print("[INFO] Refresh Token saved to secrets.json")