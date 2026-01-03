import sys
import os
import time
import json
from datetime import datetime
from urllib.parse import quote

# Add current directory to path to import utils
sys.path.append(os.path.abspath(os.path.dirname(__file__)))
from utils import send_and_print, BASE_URL, load_config

# --- CONFIGURATION ---
TIMESTAMP = int(time.time())
# Names are chosen to be easily sortable: A, B, C
TEST_USERS = [
    {"name": f"AutoTest Alice {TIMESTAMP}", "email": f"alice.{TIMESTAMP}@test.com", "role": "admin", "password": "password123"},
    {"name": f"AutoTest Bob {TIMESTAMP}", "email": f"bob.{TIMESTAMP}@test.com", "role": "user", "password": "password123"},
    {"name": f"AutoTest Charlie {TIMESTAMP}", "email": f"charlie.{TIMESTAMP}@test.com", "role": "user", "password": "password123"},
]
CREATED_USERS = [] 

# --- COLORS & HELPERS ---
class Colors:
    HEADER = '\033[95m'
    OKGREEN = '\033[92m'
    FAIL = '\033[91m'
    ENDC = '\033[0m'
    BOLD = '\033[1m'

def print_header(msg):
    print(f"\n{Colors.HEADER}{Colors.BOLD}=== {msg} ==={Colors.ENDC}")

def print_pass(msg):
    print(f"{Colors.OKGREEN}[PASS] {msg}{Colors.ENDC}")

def print_fail(msg):
    print(f"{Colors.FAIL}[FAIL] {msg}{Colors.ENDC}")

def get_token():
    token = load_config("accessToken")
    if not token:
        print_fail("No access token found. Please run A2.auth_login.py first.")
        sys.exit(1)
    return token

# --- 1. SEEDING ---
def create_seed_users(token):
    print_header("1. SEEDING DATA")
    url = f"{BASE_URL}/users"
    headers = {"Authorization": f"Bearer {token}"}
    
    for user_data in TEST_USERS:
        resp = send_and_print(url, headers, method="POST", body=user_data, output_file="temp_seed.json")
        if resp.status_code == 201:
            full_user = resp.json()
            full_user['id'] = str(full_user['id']) 
            CREATED_USERS.append(full_user)
            print(f"Created: {full_user['name']} (ID: {full_user['id']})")
            time.sleep(1) 
        else:
            print_fail(f"Failed to create user {user_data['name']}")
            cleanup_users(token)
            sys.exit(1)

def cleanup_users(token):
    print_header("CLEANUP")
    headers = {"Authorization": f"Bearer {token}"}
    for user in CREATED_USERS:
        url = f"{BASE_URL}/users/{user['id']}"
        send_and_print(url, headers, method="DELETE", output_file="temp_cleanup.json")
        print(f"Deleted user ID: {user['id']}")

# --- 2. SEARCH TESTS ---
def test_search_scopes(token):
    print_header("2. SEARCH SCOPES (All, Name, Email, ID)")
    headers = {"Authorization": f"Bearer {token}"}
    search_base = quote(str(TIMESTAMP))

    # 2.1 Scope: ALL
    print(f">> Case A: Scope 'all' (Search string '{TIMESTAMP}')")
    url = f"{BASE_URL}/users?search={search_base}&scope=all&limit=100"
    resp = send_and_print(url, headers, output_file="test_search_all.json")
    
    if resp.status_code != 200:
        print_fail(f"API Error {resp.status_code}")
        return

    results = resp.json()['results']
    found_ids = [str(u['id']) for u in results]
    expected_ids = [str(u['id']) for u in CREATED_USERS]
    
    if all(uid in found_ids for uid in expected_ids):
        print_pass("Found all seeded users using scope 'all'.")
    else:
        print_fail(f"Scope 'all' failed. Found {found_ids}, Expected subset {expected_ids}")

    # 2.2 Scope: NAME
    target = CREATED_USERS[0] 
    search_name = quote(target['name'])
    print(f">> Case B: Scope 'name' (Search '{target['name']}')")
    url = f"{BASE_URL}/users?search={search_name}&scope=name"
    resp = send_and_print(url, headers, output_file="test_search_name.json")
    results = resp.json()['results']
    
    if len(results) == 1 and str(results[0]['id']) == str(target['id']):
        print_pass("Found specific user by Name.")
    else:
        print_fail("Failed to find user by Name.")

    # 2.3 Scope: EMAIL
    target = CREATED_USERS[1] 
    search_email = quote(target['email'])
    print(f">> Case C: Scope 'email' (Search '{target['email']}')")
    url = f"{BASE_URL}/users?search={search_email}&scope=email"
    resp = send_and_print(url, headers, output_file="test_search_email.json")
    results = resp.json()['results']
    
    # UBAH DISINI: Casting ke str
    if len(results) == 1 and str(results[0]['id']) == str(target['id']):
        print_pass("Found specific user by Email.")
    else:
        print_fail("Failed to find user by Email.")

    # 2.4 Scope: ID
    target = CREATED_USERS[2] 
    print(f">> Case D: Scope 'id' (Search ID '{target['id']}')")
    url = f"{BASE_URL}/users?search={target['id']}&scope=id"
    resp = send_and_print(url, headers, output_file="test_search_id.json")
    results = resp.json()['results']
    
    if len(results) == 1 and str(results[0]['id']) == str(target['id']):
        print_pass("Found specific user by ID.")
    else:
        print_fail(f"Failed to find user by ID. Search term: {target['id']}, Results: {len(results)}")

# --- 3. FILTER TESTS ---
def test_filters(token):
    print_header("3. ROLE FILTERING")
    headers = {"Authorization": f"Bearer {token}"}
    search_base = quote(str(TIMESTAMP))
    
    # 3.1 Role: Admin
    print(">> Case A: Role 'admin'")
    url = f"{BASE_URL}/users?role=admin&search={search_base}&scope=all"
    resp = send_and_print(url, headers, output_file="test_filter_admin.json")
    results = resp.json()['results']
    
    has_alice = any(str(u['id']) == str(CREATED_USERS[0]['id']) for u in results)
    has_bob = any(str(u['id']) == str(CREATED_USERS[1]['id']) for u in results)
    
    if has_alice and not has_bob:
        print_pass("Filter 'admin' returned correct users.")
    else:
        print_fail("Filter 'admin' failed.")

# --- 4. SORTING TESTS ---
def test_sorting(token):
    print_header("4. SORTING (All Columns: ASC & DESC)")
    headers = {"Authorization": f"Bearer {token}"}
    search_base = quote(str(TIMESTAMP))
    
    # Generic sort verifier
    def verify_sort(field, order, expected_top_user):
        print(f">> Testing Sort: {field} ({order.upper()})")
        url = f"{BASE_URL}/users?sortBy={field}:{order}&search={search_base}&scope=all"
        resp = send_and_print(url, headers, output_file=f"test_sort_{field}.json")
        results = resp.json()['results']
        
        if not results:
            print_fail("No results returned for sorting test.")
            return

        top_user = results[0]
        # Allow checking either name or ID
        if str(top_user['id']) == str(expected_top_user['id']):
             print_pass(f"Correctly sorted {field} {order}. Top is {top_user['name']}")
        else:
             print_fail(f"Sort {field} {order} wrong. Top is {top_user['name']}, Expected {expected_top_user['name']}")

    # 4.1 ID
    verify_sort('id', 'asc', CREATED_USERS[0])  # Alice
    verify_sort('id', 'desc', CREATED_USERS[2]) # Charlie

    # 4.2 Name
    verify_sort('name', 'asc', CREATED_USERS[0])
    verify_sort('name', 'desc', CREATED_USERS[2])

    # 4.3 Email
    verify_sort('email', 'asc', CREATED_USERS[0])
    verify_sort('email', 'desc', CREATED_USERS[2])

    # 4.4 Created At
    verify_sort('created_at', 'asc', CREATED_USERS[0])
    verify_sort('created_at', 'desc', CREATED_USERS[2])

    # 4.5 Role
    # NOTE: MySQL ENUM('user', 'admin') -> user=1, admin=2
    # So ASC order: 'user' < 'admin'.
    # Bob (User) should come BEFORE Alice (Admin) in ASC sort.
    # We check if the top user is Bob (or Charlie, both are users).
    
    print(">> Testing Sort: role (ASC) - Expecting User (Index 1) then Admin (Index 2)")
    url = f"{BASE_URL}/users?sortBy=role:asc&search={search_base}&scope=all"
    resp = send_and_print(url, headers, output_file="test_sort_role.json")
    results = resp.json()['results']
    if results and results[0]['role'] == 'user':
        print_pass(f"Correctly sorted role ASC (User < Admin in MySQL Enum). Top is {results[0]['role']}")
    else:
        print_fail(f"Sort role ASC wrong. Top is {results[0]['role']}, Expected user")

# --- 5. PAGINATION TESTS ---
def test_pagination(token):
    print_header("5. PAGINATION (Limit & Navigation)")
    headers = {"Authorization": f"Bearer {token}"}
    search_base = quote(str(TIMESTAMP))

    base_url = f"{BASE_URL}/users?sortBy=created_at:asc&search={search_base}&scope=all"

    # 5.1 Page 1, Limit 1 -> Alice
    print(">> Case A: Page 1, Limit 1")
    url = f"{base_url}&limit=1&page=1"
    resp = send_and_print(url, headers, output_file="test_page_1.json")
    results = resp.json()['results']
    
    if len(results) == 1 and str(results[0]['id']) == str(CREATED_USERS[0]['id']):
        print_pass("Page 1 correctly returned Alice.")
    else:
        print_fail(f"Page 1 failed. Got {len(results)} items.")

    # 5.2 Page 2, Limit 1 -> Bob
    print(">> Case B: Page 2, Limit 1")
    url = f"{base_url}&limit=1&page=2"
    resp = send_and_print(url, headers, output_file="test_page_2.json")
    results = resp.json()['results']
    
    if len(results) == 1 and str(results[0]['id']) == str(CREATED_USERS[1]['id']):
        print_pass("Page 2 correctly returned Bob.")
    else:
        print_fail(f"Page 2 failed. Got {len(results)} items.")

    # 5.3 Page 3, Limit 1 -> Charlie
    print(">> Case C: Page 3, Limit 1")
    url = f"{base_url}&limit=1&page=3"
    resp = send_and_print(url, headers, output_file="test_page_3.json")
    results = resp.json()['results']

    if len(results) == 1 and str(results[0]['id']) == str(CREATED_USERS[2]['id']):
        print_pass("Page 3 correctly returned Charlie.")
    else:
        print_fail("Page 3 failed.")


# --- MAIN EXECUTION ---
if __name__ == "__main__":
    try:
        access_token = get_token()
        create_seed_users(access_token)
        test_search_scopes(access_token)
        test_filters(access_token)
        test_sorting(access_token)
        test_pagination(access_token)
    except Exception as e:
        print(f"\n{Colors.FAIL}[FAIL] CRITICAL ERROR: {e}{Colors.ENDC}")
        import traceback
        traceback.print_exc()
    finally:
        try:
            cleanup_users(access_token)
        except:
            pass