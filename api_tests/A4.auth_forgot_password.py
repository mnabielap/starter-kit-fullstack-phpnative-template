import sys
import os

sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

# Use the email from A1
email = utils.load_config("last_registered_email") or "admin@example.com"

url = f"{utils.BASE_URL}/auth/forgot-password"
body = {
    "email": email
}

response = utils.send_and_print(
    url=url,
    method="POST",
    body=body,
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)