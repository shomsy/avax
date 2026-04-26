# Security Glossary

Version: 1.0.0
Status: Normative / Local

## Threat Categories

| Term                    | Definition                            | Mitigation                         |
|-------------------------|---------------------------------------|------------------------------------|
| **Credential Stuffing** | Using leaked credentials across sites | Rate limiting, MFA                 |
| **Brute Force**         | Repeated password guessing            | Rate limiting, account lockout     |
| **Dictionary Attack**   | Wordlist-based password guessing      | Strong hashing, rate limiting      |
| **Rainbow Table**       | Precomputed hash lookup               | Salt usage                         |
| **Phishing**            | Deceptive credential request          | User training, MFA                 |
| **Man-in-the-Middle**   | Intercepting communication            | TLS, certificate pinning           |
| **Session fixation**    | Injecting session ID                  | Regenerate session on login        |
| **Session hijacking**   | Stealing active session               | Secure cookies, HttpOnly, SameSite |
| **CSRF**                | Cross-site request forgery            | CSRF tokens                        |
| **XSS**                 | Injecting malicious script            | Output encoding                    |
| **SQL Injection**       | Injecting SQL commands                | Parameterized queries              |
| **Command Injection**   | OS command injection                  | Input validation                   |
| **Path Traversal**      | Accessing files outside webroot       | Path validation                    |
| **Denial of Service**   | Making service unavailable            | Rate limiting, throttling          |
| **Replay Attack**       | Reusing captured data                 | Nonces, timestamps                 |
| **Token Theft**         | Stealing access tokens                | Sender constraints                 |
| **Key Extraction**      | Extracting keys from memory           | Secure memory handling             |

## Security Properties

| Property            | Definition                     |
|---------------------|--------------------------------|
| **Confidentiality** | Only authorized access         |
| **Integrity**       | Data unchanged/unmodified      |
| **Availability**    | Service accessible when needed |
| **Authentication**  | Proving identity               |
| **Authorization**   | Permitting actions             |
| **Non-repudiation** | Cannot deny actions            |

## Security Patterns

| Pattern                         | Definition                     |
|---------------------------------|--------------------------------|
| **Fail Closed**                 | Default deny on missing config |
| **Least Privilege**             | Minimum required permissions   |
| **Defense in Depth**            | Multiple security layers       |
| **Secure by Default**           | Safe out-of-box config         |
| **Principle of Least Surprise** | Unexpected = suspicious        |

## Key Management

| Term            | Definition                        |
|-----------------|-----------------------------------|
| **Key**         | Cryptographic secret              |
| **Key Pair**    | Public + private key              |
| **Certificate** | Public key + metadata + signature |
| **CA**          | Certificate Authority             |
| **Trust Chain** | Certificate → CA → root           |
| **PKI**         | Public Key Infrastructure         |
| **HSM**         | Hardware Security Module          |
| **KMS**         | Key Management Service            |

## TLS / mTLS

| Term                    | Definition                             |
|-------------------------|----------------------------------------|
| **TLS**                 | Transport Layer Security               |
| **mTLS**                | Mutual TLS (both parties present cert) |
| **Certificate Pinning** | Restricting accepted CAs               |
| **OCSP**                | Online Certificate Status Protocol     |
| **CRL**                 | Certificate Revocation List            |

---

*Part of Auth Glossary Suite*