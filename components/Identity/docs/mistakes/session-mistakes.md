# Session Mistakes

## 1. Storing Session State in Static/Mutable Globals

**Mistake**: Keeping session data in static variables, global state, or singleton instances in long-lived workers.

**Problem**: Session state leaks between requests, causing one user's identity to appear in another user's request.

**Correct Approach**: Load session state per-request from the session capability. Never persist session data in static or mutable state between requests.

## 2. Not Invalidating Sessions on Security Events

**Mistake**: Leaving sessions active after password changes, security setting changes, or suspected compromise.

**Problem**: An attacker with a valid session retains access even after the legitimate user changes their password.

**Correct Approach**: Invalidate all sessions (or targeted sessions) on password change, MFA enrollment, and suspected compromise. Provide a mechanism for users to view and terminate their active sessions.

## 3. Using Predictable Session IDs

**Mistake**: Generating session IDs from predictable values (timestamps, user IDs, sequential counters).

**Problem**: Predictable session IDs enable session hijacking through enumeration.

**Correct Approach**: Generate session IDs using cryptographically secure random number generators. Session IDs must be unpredictable and unique.

## 4. Not Setting Secure Cookie Attributes

**Mistake**: Transmitting session cookies without security attributes.

**Problem**: Cookies without security attributes are vulnerable to interception, cross-site access, and XSS theft.

**Correct Approach**: Set httpOnly (prevent JavaScript access), secure (HTTPS only), sameSite (cross-site protection), and appropriate expiry on session cookies.

## 5. Storing Too Much in Sessions

**Mistake**: Storing application data, preferences, or business state in sessions.

**Problem**: Overloaded sessions are difficult to invalidate, migrate, and reason about. Session data may contain sensitive information that should have its own lifecycle.

**Correct Approach**: Store identity claims and minimal context in sessions. Application data belongs in application storage, not sessions.

## 6. Not Handling Session Fixation

**Mistake**: Reusing the same session ID across authentication state changes.

**Problem**: An attacker can pre-establish a session ID and trick a user into authenticating into it (session fixation attack).

**Correct Approach**: Regenerate the session ID on authentication state changes (login, privilege escalation). Destroy the old session and create a new one with a fresh ID.

## 7. No Concurrent Session Management

**Mistake**: Not tracking or managing multiple active sessions per user.

**Problem**: Users cannot see or terminate their active sessions. Compromised sessions persist undetected.

**Correct Approach**: Track active sessions per user. Provide visibility (device, location, last active) and termination capability. Allow bulk session invalidation.

## 8. Assuming Session Means Authorization

**Mistake**: Treating an active session as proof of permission to perform any action.

**Problem**: An active session proves authentication, not authorization. The user may be authenticated but lack permission for specific resources or actions.

**Correct Approach**: Use sessions for authentication state. Use authorization boundaries for permission decisions. Never skip authorization because a session exists.
