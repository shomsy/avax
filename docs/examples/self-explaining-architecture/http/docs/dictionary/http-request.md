# HTTP Request

## What It Is

An HTTP Request is the data structure that represents a single HTTP request from a client (browser, API caller, another service).

It contains:
- The URL the client wants
- The method (GET, POST, PUT, DELETE, etc.)
- Headers (metadata like Content-Type, Authorization, Accept)
- Body (the actual data sent by the client)
- Query parameters (?key=value&other=thing)
- Cookies (small pieces of state the client sends back)

## What It Is NOT

- It is NOT the business logic that handles the request
- It is NOT the response that goes back to the client
- It is NOT a session (a session spans many requests)
- It is NOT authentication (authentication uses request data to decide who you are)

## Common Confusion

**Confusion**: "The request object should know who the user is."
**Reality**: The Request only carries data. A separate Identity/Capability reads the request's Authorization header and decides who the user is. The Request itself doesn't "know" anything.

**Confusion**: "I should put business logic in the request object."
**Reality**: The Request is a data carrier. Business logic belongs in Flows. The Request just brings the data to the Flow.

**Confusion**: "Request and Session are the same thing."
**Reality**: A Request is one single call from a client. A Session is a series of related requests from the same user over time. One request is like one sentence. A session is like a conversation.
