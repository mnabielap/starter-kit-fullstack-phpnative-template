import sys
import os
import time
sys.path.append(os.path.abspath(os.path.dirname(__file__)))
from utils import send_and_print, BASE_URL

# --- COLORS ---
class Colors:
    OKGREEN = '\033[92m'
    FAIL = '\033[91m'
    ENDC = '\033[0m'
    BOLD = '\033[1m'

print(f"\n{Colors.BOLD}=== TEST: REGISTER ROLE OVERRIDE SECURITY ==={Colors.ENDC}")

# Generate unique email
unique_id = int(time.time())
email = f"hacker_role_{unique_id}@example.com"

url = f"{BASE_URL}/auth/register"

# Malicious Payload: Trying to force 'admin' role
payload = {
    "name": "Hacker WannaBe",
    "email": email,
    "password": "password123",
    "role": "admin"  # <--- The attack vector
}

print(f"Attempting to register with malicious payload: {Colors.FAIL}role='admin'{Colors.ENDC}")

response = send_and_print(
    url=url,
    method="POST",
    body=payload,
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)

if response.status_code == 201:
    data = response.json()
    
    # Check the actual role assigned by the server
    user_role = data['user']['role']
    
    print(f"\nUser created successfully.")
    print(f"Requested Role: 'admin'")
    print(f"Assigned Role:  '{user_role}'")
    
    if user_role == "user":
        print(f"\n{Colors.OKGREEN}[PASS] Security check passed! Server ignored the malicious role and assigned 'user'.{Colors.ENDC}")
    else:
        print(f"\n{Colors.FAIL}[FAIL] Security Vulnerability! User was created with role '{user_role}'.{Colors.ENDC}")

else:
    print(f"\n{Colors.FAIL}[FAIL] Registration failed with status {response.status_code}. Could not verify security.{Colors.ENDC}")