import sys
import os
sys.path.append(os.path.abspath(os.path.dirname(__file__)))
from utils import send_and_print, BASE_URL

print("--- RESET PASSWORD ---")

mock_token = "PUT_VALID_TOKEN_HERE_FROM_LOGS" 

url = f"{BASE_URL}/auth/reset-password?token={mock_token}"

payload = {
    "password": "newpassword123"
}

response = send_and_print(
    url=url,
    method="POST",
    body=payload,
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)