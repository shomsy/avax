#!/usr/bin/env python3
"""
Docs MCP Server - Documentation and API Reference Lookup

This MCP server provides access to documentation sources:
- OpenAI API documentation
- Python documentation
- JavaScript/TypeScript docs
- Other common API references

Usage:
    npx or uvx to run this server:
    uvx mcp-server-docs

Configuration in OpenCode:
{
  "mcpServers": {
    "docs": {
      "command": "python3",
      "args": ["/path/to/agent-harness/.agents/mcp/docs-mcp-server.py"]
    }
  }
}
"""

from __future__ import annotations

import json
import re
import sys
from pathlib import Path
from typing import Any


DOCS_SOURCES = {
    "openai": {
        "base_url": "https://platform.openai.com/docs",
        "endpoints": {
            "chat": "/guides/text-generation",
            "embeddings": "/guides/embeddings",
            "rate_limits": "/guides/rate-limits",
            "models": "/guides/models",
        },
    },
    "python": {
        "base_url": "https://docs.python.org/3",
        "endpoints": {
            "stdlib": "/library",
            "typing": "/library/typing.html",
            "dataclasses": "/library/dataclasses.html",
        },
    },
    "javascript": {
        "base_url": "https://developer.mozilla.org/en-US/docs/Web/JavaScript",
        "endpoints": {
            "guide": "/Guide",
            "reference": "/Reference",
        },
    },
    "typescript": {
        "base_url": "https://www.typescriptlang.org/docs",
        "endpoints": {
            "handbook": "/handbook",
            "docs": "/docs",
        },
    },
    "nodejs": {
        "base_url": "https://nodejs.org/api",
        "endpoints": {
            "fs": "fs.html",
            "http": "http.html",
            "path": "path.html",
        },
    },
}


def get_capabilities() -> dict[str, Any]:
    return {
        "protocolVersion": "2024-11-05",
        "capabilities": {
            "tools": {},
            "resources": {},
            "prompts": {},
        },
        "serverInfo": {
            "name": "docs-mcp-server",
            "version": "1.0.0",
            "description": "Documentation and API reference lookup",
        },
    }


def get_tools() -> list[dict[str, Any]]:
    return [
        {
            "name": "search_docs",
            "description": "Search documentation sources for a topic",
            "inputSchema": {
                "type": "object",
                "properties": {
                    "query": {
                        "type": "string",
                        "description": "Search query (e.g., 'rate limits', 'async await')",
                    },
                    "source": {
                        "type": "string",
                        "enum": [
                            "openai",
                            "python",
                            "javascript",
                            "typescript",
                            "nodejs",
                        ],
                        "description": "Documentation source to search",
                    },
                    "max_results": {
                        "type": "integer",
                        "default": 5,
                        "description": "Maximum number of results",
                    },
                },
                "required": ["query"],
            },
        },
        {
            "name": "get_doc_endpoint",
            "description": "Get documentation endpoint URL for a topic",
            "inputSchema": {
                "type": "object",
                "properties": {
                    "source": {
                        "type": "string",
                        "enum": list(DOCS_SOURCES.keys()),
                        "description": "Documentation source",
                    },
                    "topic": {
                        "type": "string",
                        "description": "Topic within the source (e.g., 'rate_limits', 'fs')",
                    },
                },
                "required": ["source", "topic"],
            },
        },
        {
            "name": "list_sources",
            "description": "List available documentation sources",
            "inputSchema": {
                "type": "object",
                "properties": {},
            },
        },
    ]


def search_docs(
    query: str, source: str | None = None, max_results: int = 5
) -> dict[str, Any]:
    results = []

    sources_to_search = (
        [source] if source and source in DOCS_SOURCES else DOCS_SOURCES.keys()
    )

    for src in sources_to_search:
        src_info = DOCS_SOURCES.get(src, {})
        base_url = src_info.get("base_url", "")
        endpoints = src_info.get("endpoints", {})

        for topic, path in endpoints.items():
            if (
                query.lower() in topic.replace("_", " ").lower()
                or query.lower() in path.lower()
            ):
                results.append(
                    {
                        "source": src,
                        "topic": topic,
                        "url": f"{base_url}{path}"
                        if path.startswith("http")
                        else f"{base_url}/{path}",
                        "relevance": "high"
                        if query.lower() in topic.lower()
                        else "medium",
                    }
                )

        results.append(
            {
                "source": src,
                "topic": "general",
                "url": base_url,
                "relevance": "low",
                "note": f"Base URL for {src}. Use search with specific topic.",
            }
        )

    results = results[:max_results]

    return {
        "query": query,
        "source": source,
        "results": results,
        "count": len(results),
    }


def get_doc_endpoint(source: str, topic: str) -> dict[str, Any]:
    src_info = DOCS_SOURCES.get(source)
    if not src_info:
        return {"error": f"Unknown source: {source}"}

    endpoints = src_info.get("endpoints", {})
    path = endpoints.get(topic)

    if not path:
        return {
            "available_topics": list(endpoints.keys()),
            "error": f"Topic '{topic}' not found in {source}",
        }

    base_url = src_info.get("base_url", "")
    full_url = path if path.startswith("http") else f"{base_url}/{path}"

    return {
        "source": source,
        "topic": topic,
        "url": full_url,
    }


def list_sources() -> dict[str, Any]:
    return {
        "sources": [
            {
                "name": name,
                "base_url": info.get("base_url", ""),
                "topics": list(info.get("endpoints", {}).keys()),
            }
            for name, info in DOCS_SOURCES.items()
        ]
    }


def handle_tool_call(tool_name: str, arguments: dict[str, Any]) -> dict[str, Any]:
    if tool_name == "search_docs":
        return search_docs(
            query=arguments.get("query", ""),
            source=arguments.get("source"),
            max_results=arguments.get("max_results", 5),
        )
    elif tool_name == "get_doc_endpoint":
        return get_doc_endpoint(
            source=arguments.get("source", ""),
            topic=arguments.get("topic", ""),
        )
    elif tool_name == "list_sources":
        return list_sources()
    else:
        return {"error": f"Unknown tool: {tool_name}"}


def main() -> None:
    import sys

    if len(sys.argv) < 2:
        print(json.dumps(get_capabilities()), file=sys.stdout)
        return

    command = sys.argv[1]

    if command == "initialize":
        init_data = json.loads(sys.stdin.read())
        response = get_capabilities()
        print(json.dumps(response), file=sys.stdout)

    elif command == "tools/list":
        print(json.dumps(get_tools()), file=sys.stdout)

    elif command == "tools/call":
        call_data = json.loads(sys.stdin.read())
        tool_name = call_data.get("name")
        arguments = call_data.get("arguments", {})
        result = handle_tool_call(tool_name, arguments)
        print(
            json.dumps({"content": [{"type": "text", "text": json.dumps(result)}]}),
            file=sys.stdout,
        )

    else:
        print(json.dumps({"error": f"Unknown command: {command}"}), file=sys.stderr)


if __name__ == "__main__":
    main()
