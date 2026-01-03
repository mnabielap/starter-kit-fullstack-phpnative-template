import sys
import os
import time
sys.path.append(os.path.abspath(os.path.dirname(__file__)))
from utils import send_and_print, BASE_URL

# --- COLORS ---
class Colors:
    OKGREEN = '\033[92m'
    FAIL = '\033[91m'
    WARNING = '\033[93m'
    ENDC = '\033[0m'
    BOLD = '\033[1m'

print(f"\n{Colors.BOLD}=== TEST: RBAC SECURITY (STANDARD USER CANNOT CRUD) ==={Colors.ENDC}")

# 1. SETUP: Register a Standard User to get a Non-Admin Token
timestamp = int(time.time())
email = f"standard_user_{timestamp}@test.com"
print(f"\n>> Step 1: Registering a Standard User ({email})...")

reg_url = f"{BASE_URL}/auth/register"
reg_payload = {
    "name": "Standard User",
    "email": email,
    "password": "password123"
}

# We intentionally do NOT use load_config() here because we don't want the Admin token.
# We want the token of this specific new standard user.
reg_response = send_and_print(reg_url, method="POST", body=reg_payload, output_file="temp_rbac_user.json")

if reg_response.status_code != 201:
    print(f"{Colors.FAIL}Critical: Failed to register standard user. Cannot proceed.{Colors.ENDC}")
    sys.exit(1)

# Extract Token
user_token = reg_response.json()['tokens']['access']['token']
user_headers = {"Authorization": f"Bearer {user_token}"}
print(f"{Colors.OKGREEN}Standard User Registered & Token Acquired.{Colors.ENDC}")

# --- ATTACK VECTOR 1: TRY TO GET ALL USERS ---
print(f"\n>> Step 2: Attempting to GET ALL USERS (Admin Route)...")
url_get = f"{BASE_URL}/users"
resp_get = send_and_print(url_get, headers=user_headers, method="GET", output_file="test_rbac_get.json")

if resp_get.status_code == 403:
    print(f"{Colors.OKGREEN}[PASS] GET /users was Forbidden (403).{Colors.ENDC}")
else:
    print(f"{Colors.FAIL}[FAIL] Security Breach! Standard user accessed GET /users (Status: {resp_get.status_code}).{Colors.ENDC}")

# --- ATTACK VECTOR 2: TRY TO CREATE A USER ---
print(f"\n>> Step 3: Attempting to CREATE A USER (Admin Route)...")
url_create = f"{BASE_URL}/users"
payload_create = {
    "name": "Malicious User",
    "email": f"malicious_{timestamp}@test.com",
    "password": "password123",
    "role": "admin"
}
resp_create = send_and_print(url_create, headers=user_headers, method="POST", body=payload_create, output_file="test_rbac_create.json")

if resp_create.status_code == 403:
    print(f"{Colors.OKGREEN}[PASS] POST /users was Forbidden (403).{Colors.ENDC}")
else:
    print(f"{Colors.FAIL}[FAIL] Security Breach! Standard user created a user (Status: {resp_create.status_code}).{Colors.ENDC}")

# --- ATTACK VECTOR 3: TRY TO DELETE A USER ---
# We try to delete ID 1 (usually admin) or any random ID
print(f"\n>> Step 4: Attempting to DELETE USER ID 1 (Admin Route)...")
url_delete = f"{BASE_URL}/users/1"
resp_delete = send_and_print(url_delete, headers=user_headers, method="DELETE", output_file="test_rbac_delete.json")

if resp_delete.status_code == 403:
    print(f"{Colors.OKGREEN}[PASS] DELETE /users/:id was Forbidden (403).{Colors.ENDC}")
else:
    print(f"{Colors.FAIL}[FAIL] Security Breach! Standard user deleted a user (Status: {resp_delete.status_code}).{Colors.ENDC}")

print(f"\n{Colors.BOLD}=== RBAC TEST COMPLETE ==={Colors.ENDC}")