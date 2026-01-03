import sys
import os
import time
import json

# Add current directory to path to import utils
sys.path.append(os.path.abspath(os.path.dirname(__file__)))
from utils import send_and_print, BASE_URL, load_config

# --- COLORS ---
class Colors:
    OKGREEN = '\033[92m'
    FAIL = '\033[91m'
    ENDC = '\033[0m'
    BOLD = '\033[1m'

def print_header(msg):
    print(f"\n{Colors.BOLD}=== {msg} ==={Colors.ENDC}")

def print_pass(msg):
    print(f"{Colors.OKGREEN}[PASS] {msg}{Colors.ENDC}")

def print_fail(msg):
    print(f"{Colors.FAIL}[FAIL] {msg}{Colors.ENDC}")
    # We don't exit immediately to allow cleanup if possible

def assert_payload_matches_response(payload, response_data, context):
    """
    Strictly checks that every key in payload exists in response and has the same value.
    Exceptions: 'password' is never returned by API.
    """
    all_match = True
    print(f">> Verifying {context} data integrity...")
    
    for key, input_val in payload.items():
        if key == "password":
            continue # API security standard: never echo password
            
        if key not in response_data:
            print_fail(f"Field '{key}' missing in response.")
            all_match = False
            continue
            
        api_val = response_data[key]
        
        # Determine if match
        if api_val == input_val:
            # OK
            pass
        else:
            print_fail(f"Field '{key}' mismatch! Input: '{input_val}' | Output: '{api_val}'")
            all_match = False
    
    if all_match:
        print_pass(f"{context}: All input fields match API response.")
    return all_match

# --- MAIN TEST FLOW ---

def run_test():
    print_header("TEST: ADMIN CRUD DATA INTEGRITY")
    
    # 0. Load Admin Token
    token = load_config("accessToken")
    if not token:
        print_fail("No access token found. Run A2.auth_login.py first.")
        sys.exit(1)

    headers = {"Authorization": f"Bearer {token}"}
    timestamp = int(time.time())
    
    # --- STEP 1: CREATE USER ---
    print_header("1. CREATE USER (POST)")
    
    create_payload = {
        "name": f"Admin Test {timestamp}",
        "email": f"admintest.{timestamp}@check.com",
        "role": "user",
        "password": "password123"
    }
    
    url_create = f"{BASE_URL}/users"
    resp_create = send_and_print(url_create, headers, method="POST", body=create_payload, output_file="test_admin_1_create.json")
    
    if resp_create.status_code != 201:
        print_fail(f"Create failed with status {resp_create.status_code}")
        sys.exit(1)
        
    user_data = resp_create.json()
    created_id = user_data.get('id')
    
    # ASSERTION 1: Verify Response matches Input
    if not assert_payload_matches_response(create_payload, user_data, "Create User"):
        print_fail("Stopping test due to data mismatch in Create.")
        # Attempt cleanup
        send_and_print(f"{BASE_URL}/users/{created_id}", headers, method="DELETE")
        sys.exit(1)

    # --- STEP 2: GET USER ---
    print_header("2. GET USER (GET)")
    
    url_get = f"{BASE_URL}/users/{created_id}"
    resp_get = send_and_print(url_get, headers, method="GET", output_file="test_admin_2_get.json")
    
    if resp_get.status_code != 200:
        print_fail(f"Get failed with status {resp_get.status_code}")
    else:
        get_data = resp_get.json()
        # ASSERTION 2: Verify GET data matches original Input
        assert_payload_matches_response(create_payload, get_data, "Get User")

    # --- STEP 3: UPDATE USER ---
    print_header("3. UPDATE USER (PATCH)")
    
    update_payload = {
        "name": f"Updated Name {timestamp}",
        "email": f"updated.{timestamp}@check.com"
        # Note: We don't send role or password here to test partial update
    }
    
    url_update = f"{BASE_URL}/users/{created_id}"
    resp_update = send_and_print(url_update, headers, method="PATCH", body=update_payload, output_file="test_admin_3_update.json")
    
    if resp_update.status_code != 200:
        print_fail(f"Update failed with status {resp_update.status_code}")
    else:
        update_data = resp_update.json()
        # ASSERTION 3: Verify Response matches Update Input
        assert_payload_matches_response(update_payload, update_data, "Update User")
        
        # Verify non-updated fields (role) remain same
        if update_data['role'] == create_payload['role']:
            print_pass("Non-updated field 'role' preserved correctly.")
        else:
            print_fail(f"Non-updated field 'role' changed unexpectedly! {update_data['role']}")

    # --- STEP 4: DELETE USER ---
    print_header("4. DELETE USER (DELETE)")
    
    url_delete = f"{BASE_URL}/users/{created_id}"
    resp_delete = send_and_print(url_delete, headers, method="DELETE", output_file="test_admin_4_delete.json")
    
    if resp_delete.status_code == 204:
        print_pass("User deleted successfully (204 No Content).")
    else:
        print_fail(f"Delete failed with status {resp_delete.status_code}")

    # --- STEP 5: VERIFY DELETION ---
    print_header("5. VERIFY DELETION (GET 404)")
    
    resp_verify = send_and_print(url_get, headers, method="GET", output_file="test_admin_5_verify.json")
    
    if resp_verify.status_code == 404:
        print_pass("Get deleted user returned 404 (Correct).")
    else:
        print_fail(f"Expected 404 but got {resp_verify.status_code}")

if __name__ == "__main__":
    try:
        run_test()
    except Exception as e:
        print(f"\n{Colors.FAIL}CRITICAL ERROR: {e}{Colors.ENDC}")
        import traceback
        traceback.print_exc()