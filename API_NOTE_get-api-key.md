# API Note: Get API Key (signed request)

## Endpoint

```
GET https://aitutor.aivibecode.in/api/get-api-key
```

## Credentials (sent to you separately)

| Name | Value |
|---|---|
| `API_KEY` | `<API_KEY>` |
| `API_SECRET` | `<API_SECRET>` (never send this in a request) |

## Headers to send on every request

| Header | Value |
|---|---|
| `Accept` | `application/json` |
| `X-API-KEY` | your `API_KEY` |
| `X-TIMESTAMP` | current Unix time in **seconds**, e.g. `1700000000` |
| `X-NONCE` | 32 random hex characters (16 random bytes), **new every request** |
| `X-SIGNATURE` | HMAC-SHA256 signature (64 lowercase hex characters), see below |

## How to make the signature

1. Build this text, joined with newlines (`\n`), with no extra spaces and no trailing newline:

   ```
   GET
   /api/get-api-key
   <X-TIMESTAMP>
   <X-NONCE>
   e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855
   ```

   The last line is fixed: it is the SHA-256 of an empty body.

2. `X-SIGNATURE = HMAC-SHA256(key = API_SECRET, message = text above)`, output as **lowercase hex**.

### Test your code with this example

With these sample inputs (not the real secret):

- secret: `test_secret_123`
- timestamp: `1700000000`
- nonce: `00112233445566778899aabbccddeeff`

your code must produce exactly:

```
76da813e3f01e6c9037ef63d5930c28ae83f28ed0cbad9fc5228ef18d37faddd
```

## Rules

- Make a **new timestamp and new nonce for every call**. A nonce can be used only once.
- The timestamp must be within **60 seconds** of server time, so the device clock must be correct.
- Limit: **30 requests per minute**.

## Success response (HTTP 200)

```json
{
  "success": true,
  "message": "API request verified",
  "data": {
    "status": "authorized",
    "api_key": "..."
  }
}
```

## Error responses

| HTTP | `message` | Meaning |
|---|---|---|
| 401 | `Unauthorized` | Missing header or wrong `X-API-KEY` |
| 401 | `Request expired` | Timestamp is more than 60 seconds off. Check the device clock |
| 401 | `Invalid nonce` | Nonce is not 32 hex characters |
| 401 | `Invalid signature` | Signature is wrong. Check the text format and the secret |
| 401 | `Request already used` | Same nonce sent twice. Make a new one each call |
| 429 | (Too Many Attempts) | More than 30 requests per minute |
| 500 | `API key is not configured on the server` | Server-side setup issue |

---

## Code examples

### Android (Kotlin)

```kotlin
import java.security.SecureRandom
import javax.crypto.Mac
import javax.crypto.spec.SecretKeySpec

fun buildSignedHeaders(apiKey: String, apiSecret: String): Map<String, String> {
    val timestamp = (System.currentTimeMillis() / 1000).toString()
    val nonce = ByteArray(16).also { SecureRandom().nextBytes(it) }
        .joinToString("") { "%02x".format(it) }
    val bodyHash = "e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855"
    val text = "GET\n/api/get-api-key\n$timestamp\n$nonce\n$bodyHash"

    val mac = Mac.getInstance("HmacSHA256")
    mac.init(SecretKeySpec(apiSecret.toByteArray(Charsets.UTF_8), "HmacSHA256"))
    val signature = mac.doFinal(text.toByteArray(Charsets.UTF_8))
        .joinToString("") { "%02x".format(it) }

    return mapOf(
        "Accept" to "application/json",
        "X-API-KEY" to apiKey,
        "X-TIMESTAMP" to timestamp,
        "X-NONCE" to nonce,
        "X-SIGNATURE" to signature,
    )
}
// Send these headers with a GET request to /api/get-api-key (OkHttp / Retrofit).
```

### iOS (Swift)

```swift
import CryptoKit
import Foundation
import Security

func buildSignedHeaders(apiKey: String, apiSecret: String) -> [String: String] {
    let timestamp = String(Int(Date().timeIntervalSince1970))
    var bytes = [UInt8](repeating: 0, count: 16)
    _ = SecRandomCopyBytes(kSecRandomDefault, 16, &bytes)
    let nonce = bytes.map { String(format: "%02x", $0) }.joined()
    let bodyHash = "e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855"
    let text = "GET\n/api/get-api-key\n\(timestamp)\n\(nonce)\n\(bodyHash)"

    let key = SymmetricKey(data: Data(apiSecret.utf8))
    let mac = HMAC<SHA256>.authenticationCode(for: Data(text.utf8), using: key)
    let signature = mac.map { String(format: "%02x", $0) }.joined()

    return [
        "Accept": "application/json",
        "X-API-KEY": apiKey,
        "X-TIMESTAMP": timestamp,
        "X-NONCE": nonce,
        "X-SIGNATURE": signature,
    ]
}
// Send these headers with a GET URLRequest to /api/get-api-key.
```

### Flutter (Dart), needs the `crypto` and `http` packages

```dart
import 'dart:convert';
import 'dart:math';
import 'package:crypto/crypto.dart';
import 'package:http/http.dart' as http;

Future<http.Response> getApiKey(String apiKey, String apiSecret) {
  final timestamp = (DateTime.now().millisecondsSinceEpoch ~/ 1000).toString();
  final rnd = Random.secure();
  final nonce = List.generate(16, (_) => rnd.nextInt(256))
      .map((b) => b.toRadixString(16).padLeft(2, '0'))
      .join();
  const bodyHash = 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855';
  final text = 'GET\n/api/get-api-key\n$timestamp\n$nonce\n$bodyHash';

  final signature = Hmac(sha256, utf8.encode(apiSecret))
      .convert(utf8.encode(text))
      .toString();

  return http.get(
    Uri.parse('https://aitutor.aivibecode.in/api/get-api-key'),
    headers: {
      'Accept': 'application/json',
      'X-API-KEY': apiKey,
      'X-TIMESTAMP': timestamp,
      'X-NONCE': nonce,
      'X-SIGNATURE': signature,
    },
  );
}
```

## Security

- Never log or print `API_SECRET` or the returned `api_key`.
- Do not hardcode the secret in plain text. Keep it obfuscated or loaded securely.
- Always use HTTPS. Do not disable SSL certificate checks.
