import sys
import os

sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

# Default admin credentials (from Seeder/Readme)
email = "admin@example.com" 
password = "password1"

url = f"{utils.BASE_URL}/auth/login"
body = {
    "email": email,
    "password": password
}

print(f"--- Logging in as ADMIN: {email} ---")

response = utils.send_and_print(
    url=url,
    method="POST",
    body=body,
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)

if response.status_code == 200:
    data = response.json()
    tokens = data.get("tokens", {})
    if "access" in tokens:
        utils.save_config("accessToken", tokens["access"]["token"])
        print("[INFO] ADMIN Access Token saved. Ready for User CRUD.")