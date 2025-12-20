import binascii
import http.client
import json
import os
import re
import time
from urllib.parse import urlparse

# --- CONFIGURATION constants ---
BASE_URL = "http://localhost/starter-kit-fullstack-phpnative-template/public/v1"
CONFIG_FILE_BASE = "secrets.json"

# --- HELPER: Config Management (Secrets) ---

def save_config(key, value):
    """Saves a key-value pair to secrets.json for token persistence."""
    global CONFIG_FILE_BASE
    CONFIG_FILE = os.path.join(os.path.dirname(__file__), CONFIG_FILE_BASE)
    data = {}
    if os.path.exists(CONFIG_FILE):
        try:
            with open(CONFIG_FILE, 'r') as f:
                data = json.load(f)
        except:
            pass
    data[key] = value
    with open(CONFIG_FILE, 'w') as f:
        json.dump(data, f, indent=4)

def load_config(key):
    """Loads a value from secrets.json."""
    global CONFIG_FILE_BASE
    CONFIG_FILE = os.path.join(os.path.dirname(__file__), CONFIG_FILE_BASE)
    if not os.path.exists(CONFIG_FILE):
        return None
    try:
        with open(CONFIG_FILE, 'r') as f:
            data = json.load(f)
        return data.get(key)
    except:
        return None

# --- HELPER: CSRF & Session Management ---

def get_csrf_headers():
    """
    Simulates a browser visit to the login page to initialize a PHP Session
    and scrape the CSRF token from the <meta> tag.
    Returns a dictionary of headers containing the Cookie and X-CSRF-TOKEN.
    """
    print("\n--- [AUTO] Fetching CSRF Token & Session Cookie ---")
    
    if BASE_URL.endswith("/v1"):
        web_login_url = BASE_URL[:-3] + "/login"
    else:
        web_login_url = BASE_URL + "/../login"

    print(f"[AUTO] Target Web URL: {web_login_url}")
    
    parsed = urlparse(web_login_url)
    scheme = parsed.scheme or "http"
    host = parsed.hostname
    port = parsed.port or (443 if scheme == "https" else 80)
    path = parsed.path
    
    try:
        if scheme == "https":
            conn = http.client.HTTPSConnection(host, port, timeout=10)
        else:
            conn = http.client.HTTPConnection(host, port, timeout=10)
            
        conn.request("GET", path)
        response = conn.getresponse()
        
        # 1. ROBUST COOKIE EXTRACTION
        all_headers = response.getheaders()
        session_cookie = ""
        
        print("[AUTO] Debugging Headers received:")
        for k, v in all_headers:
            print(f"  - {k}: {v}")
            if k.lower() == 'set-cookie':
                if 'PHPSESSID' in v:
                    parts = v.split(';')
                    for part in parts:
                        if 'PHPSESSID' in part:
                            session_cookie = part.strip()
                            break
        
        if session_cookie:
            print(f"[AUTO] Session Cookie found: {session_cookie}")
        else:
            print("[AUTO] CRITICAL WARNING: No PHPSESSID found in headers! Session will fail.")

        # 2. Get CSRF Token from Body
        html_content = response.read().decode('utf-8', errors='ignore')
        conn.close()
        
        match = re.search(r'<meta name="csrf-token" content="([^"]+)"', html_content)
        
        csrf_token = ""
        if match:
            csrf_token = match.group(1)
            print(f"[AUTO] CSRF Token found: {csrf_token}")
        else:
            print("[AUTO] WARNING: CSRF meta tag not found in HTML body")
            
        return {
            "Cookie": session_cookie,
            "X-CSRF-TOKEN": csrf_token,
            "Content-Type": "application/json"
        }

    except Exception as e:
        print(f"[AUTO] Error fetching CSRF data: {e}")
        return {}

# --- HELPER: Compatibility Wrapper ---

class ResponseProxy:
    """
    Wraps the dictionary result from send_and_print to mimic a requests.Response object.
    This ensures compatibility with scripts checking .status_code and calling .json().
    """
    def __init__(self, result_dict):
        self.result_dict = result_dict
        # Safe navigation to get status
        self.status_code = result_dict.get("response", {}).get("status", 0)
        self._json_body = result_dict.get("response", {}).get("body")

    def json(self):
        """Returns the parsed JSON body."""
        return self._json_body

# --- MAIN FUNCTION ---

def send_and_print(url: str,
                   headers: dict = None,
                   body: dict = None,
                   method: str = "GET",
                   output_file: str = "response.json",
                   write_mode: str = "w",
                   timeout: int = 10 * 60 * 60,
                   max_binary_preview: int = 256, 
                   print_pretty_response: bool = False):
    """
    Sends request and prints ALL details using http.client.
    Prints: request info, raw request body, response status, headers, raw response bytes, etc.
    Saves a summary to output_file.
    """
    output_file = os.path.join(os.path.dirname(__file__), output_file)

    def pretty_json_if_possible(data):
        try:
            if isinstance(data, (bytes, bytearray)):
                text = data.decode("utf-8")
            else:
                text = data
            parsed = json.loads(text)
            return parsed
        except Exception:
            return None

    timestamp_start = time.time()
    print("\n===== REQUEST START =====")
    try:
        parsed = urlparse(url)
        scheme = parsed.scheme or "http"
        host = parsed.hostname
        port = parsed.port or (443 if scheme == "https" else 80)
        path = parsed.path or "/"
        if parsed.query:
            path += "?" + parsed.query

        print(f"URL: {url}")
        print(f"Parsed: scheme={scheme}, host={host}, port={port}, path={path}, params={parsed.params}, fragment={parsed.fragment}")
        print(f"Method: {method}")
        print("Request Headers:")
        if headers:
            print(json.dumps(headers, indent=4, ensure_ascii=False))
        else:
            print("  <no headers provided>")

        # Prepare request body for printing and sending
        if body is None:
            send_body = None
            print("Request Body: <none>")
        else:
            # If body is dict, dump to JSON string
            if isinstance(body, (dict, list)):
                send_body = json.dumps(body)
                print("Request Body (interpreted as JSON):")
                print(json.dumps(body, indent=4, ensure_ascii=False))
            elif isinstance(body, (bytes, bytearray)):
                send_body = body
                # binary preview
                preview = binascii.hexlify(body[:max_binary_preview]).decode()
                print(f"Request Body: <binary> length={len(body)} bytes; hex preview (up to {max_binary_preview} bytes): {preview}")
            else:
                send_body = str(body)
                print("Request Body (string):")
                print(send_body)

        # Show content-length if determinable
        try:
            size_send = len(send_body) if send_body is not None else 0
            print(f"Request Body Size (bytes): {size_send}")
        except Exception:
            print("Request Body Size (bytes): <unknown>")

        # Open connection
        print("\nEstablishing connection...")
        if scheme == "https":
            conn = http.client.HTTPSConnection(host, port, timeout=timeout)
        else:
            conn = http.client.HTTPConnection(host, port, timeout=timeout)

        # Ensure headers is a dict
        headers_to_send = dict(headers) if headers else {}
        # If sending a JSON body but no content-type provided, set it
        if send_body is not None and isinstance(send_body, str) and "Content-Type" not in {k.title(): v for k,v in headers_to_send.items()}:
            headers_to_send.setdefault("Content-Type", "application/json; charset=utf-8")
        headers_to_send.update(get_csrf_headers())

        # Print final headers to be sent
        print("Final Headers to send:")
        print(json.dumps(headers_to_send, indent=4, ensure_ascii=False))

        # Send request
        print("\nSending request...")
        send_time = time.time()
        conn.request(method.upper(), path, body=send_body, headers=headers_to_send)
        print(f"Request sent at {time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(send_time))}")

        # Get response
        response = conn.getresponse()
        receive_time = time.time()
        duration = receive_time - send_time
        print(f"Response received at {time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(receive_time))} (round-trip {duration:.3f}s)")

        status = response.status
        reason = response.reason
        resp_headers = dict(response.getheaders())

        # Read raw bytes
        raw_bytes = response.read()
        size_received = len(raw_bytes) if raw_bytes is not None else 0

        # Close connection
        conn.close()

        print("\n===== RESPONSE SUMMARY =====")
        print(f"Status: {status} {reason}")
        print(f"Response Headers:")
        print(json.dumps(resp_headers, indent=4, ensure_ascii=False))
        print(f"Raw Response Size (bytes): {size_received}")

        # Print raw body preview
        print("\nRaw Response Body Preview (hex or text depending on content):")
        content_type = resp_headers.get("Content-Type", "")
        is_text_like = content_type.startswith("text") or "json" in content_type or "xml" in content_type or "html" in content_type

        if size_received == 0:
            print("<empty body>")
            raw_body_preview = ""
        else:
            if is_text_like:
                try:
                    text = raw_bytes.decode("utf-8")
                except UnicodeDecodeError:
                    try:
                        text = raw_bytes.decode("latin-1")
                    except Exception:
                        text = None
                if text is not None:
                    if len(text) > 10000:
                        print(f"<text body too large to fully print; showing first 10000 chars>") 
                        print(text[:10000])
                    else:
                        print(text)
                    raw_body_preview = text
                else:
                    # fallback to hex preview
                    hex_preview = binascii.hexlify(raw_bytes[:max_binary_preview]).decode()
                    print(f"<binary data; hex preview up to {max_binary_preview} bytes>: {hex_preview}")
                    raw_body_preview = hex_preview
            else:
                hex_preview = binascii.hexlify(raw_bytes[:max_binary_preview]).decode()
                print(f"<binary data; hex preview up to {max_binary_preview} bytes>: {hex_preview}")
                raw_body_preview = hex_preview

        # Try prettifying JSON body
        prettified = pretty_json_if_possible(raw_bytes)
        if prettified is not None:
            if print_pretty_response:
                print("\nParsed JSON Body (prettified):")
                print(json.dumps(prettified, indent=4, ensure_ascii=False))
            else:
                print("\nParsed JSON Body:")
                print(json.dumps(prettified, ensure_ascii=False))
            body_to_store = prettified
        else:
            # If it's decodable text, store as text; else store hex preview and mark binary
            try:
                text_repr = raw_bytes.decode("utf-8")
                body_to_store = text_repr
            except Exception:
                body_to_store = {"binary_preview_hex": raw_body_preview, "binary_length": size_received}

        # Handle redirects (follow one level by default)
        if status in (301, 302, 303, 307, 308) and "location" in resp_headers:
            redirect_url = resp_headers["location"]
            print(f"\nRedirect detected to: {redirect_url}")
            # Save current partial result before redirect
            summary = {
                "request": {
                    "url": url,
                    "method": method,
                    "headers": headers_to_send,
                    "body": body if body is not None else None
                },
                "response": {
                    "status": status,
                    "reason": reason,
                    "headers": resp_headers,
                    "body_preview": body_to_store,
                    "size_bytes": size_received,
                    "duration_seconds": duration
                },
                "redirect_to": redirect_url,
                "timestamp": time.time()
            }
            # Attempt to save summary
            try:
                with open(output_file, write_mode, encoding="utf-8") as f:
                    json.dump(summary, f, indent=4, ensure_ascii=False)
                print(f"Partial summary saved to {output_file} (write_mode={write_mode})")
            except Exception as e:
                print(f"Failed to save partial summary to {output_file}: {e}")

            # Follow redirect once (same method becomes GET for 303)
            new_method = method
            if status == 303:
                new_method = "GET"
            print(f"\nFollowing redirect with method {new_method}...")
            return send_and_print(redirect_url, headers, body if new_method != "GET" else None, new_method, output_file, write_mode, timeout, max_binary_preview)

        # Final result object
        result = {
            "request": {
                "url": url,
                "parsed": {
                    "scheme": scheme,
                    "host": host,
                    "port": port,
                    "path": path
                },
                "method": method,
                "headers": headers_to_send,
                "body": body if body is not None else None,
                "sent_bytes": size_send
            },
            "response": {
                "status": status,
                "reason": reason,
                "headers": resp_headers,
                "body": body_to_store,
                "raw_body_preview": raw_body_preview,
                "received_bytes": size_received,
                "roundtrip_seconds": duration
            },
            "meta": {
                "saved_to": os.path.abspath(output_file),
                "saved_with_mode": write_mode,
                "timestamp": time.time()
            }
        }

        # Save to file
        try:
            # If user requested exclusive creation, handle FileExistsError
            with open(output_file, write_mode, encoding="utf-8") as f:
                json.dump(result, f, indent=4, ensure_ascii=False)
            print(f"\nFull result saved to {os.path.abspath(output_file)} (write_mode={write_mode})")
        except FileExistsError:
            print(f"\nERROR: output_file already exists and write_mode='{write_mode}' prevents overwrite.")
        except Exception as e:
            print(f"\nFailed to save full result to {output_file}: {e}")

        timestamp_end = time.time()
        total_elapsed = timestamp_end - timestamp_start
        print(f"\nTotal elapsed time (function): {total_elapsed:.3f}s")
        print("===== REQUEST END =====\n")

        # Return a Wrapper that has .status_code and .json() methods
        return ResponseProxy(result)

    except Exception as exc:
        # Print full exception info
        import traceback
        print("\n===== EXCEPTION =====")
        print("An exception occurred during send_and_print:")
        traceback.print_exc()

        error_result = {
            "request": {
                "url": url,
                "method": method,
                "headers": headers,
                "body": body
            },
            "error": {
                "type": type(exc).__name__,
                "message": str(exc),
                "traceback": traceback.format_exc()
            },
            "meta": {
                "saved_to": os.path.abspath(output_file),
                "saved_with_mode": write_mode,
                "timestamp": time.time()
            }
        }

        # Attempt to save error to file
        try:
            with open(output_file, write_mode, encoding="utf-8") as f:
                json.dump(error_result, f, indent=4, ensure_ascii=False)
            print(f"Error details saved to {os.path.abspath(output_file)}")
        except Exception as e:
            print(f"Failed to write error to {output_file}: {e}")

        print("===== END EXCEPTION =====\n")
        # Return wrapper even on error (status 0)
        return ResponseProxy({"response": {"status": 0, "body": None}, "error": error_result})