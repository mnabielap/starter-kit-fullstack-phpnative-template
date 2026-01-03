import sys
import os
sys.path.append(os.path.abspath(os.path.dirname(__file__)))
from utils import send_and_print, BASE_URL

print("--- FORGOT PASSWORD ---")

url = f"{BASE_URL}/auth/forgot-password"

payload = {
    "email": "admin@example.com"
}

response = send_and_print(
    url=url,
    method="POST",
    body=payload,
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)