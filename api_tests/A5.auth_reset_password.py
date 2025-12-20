import sys
import os

sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

# Dummy token (Replace this manually if you want to test success)
token = "dummy_reset_token_from_email_logs"
new_password = "newpassword123"

url = f"{utils.BASE_URL}/auth/reset-password?token={token}"
body = {
    "password": new_password
}

print("--- Testing Reset Password (Expect 401 if token is invalid) ---")

response = utils.send_and_print(
    url=url,
    method="POST",
    body=body,
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)