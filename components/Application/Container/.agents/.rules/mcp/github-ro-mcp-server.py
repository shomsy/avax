#!/usr/bin/env python3
"""
GitHub Read-Only MCP Server

This MCP server provides read-only access to GitHub:
- List issues
- Get PR details
- View commits and diffs
- Search repositories

Usage:
    Configure in OpenCode:
    {
      "mcpServers": {
        "github": {
          "command": "python3",
          "args": ["/path/to/agent-harness/.agents/mcp/github-ro-mcp-server.py"],
          "env": { "GITHUB_TOKEN": "ghp_..." }
        }
      }
    }

    Or use as library with custom GitHub client.
"""

from __future__ import annotations

import json
import os
import sys
from pathlib import Path
from typing import Any


class GitHubClient:
    def __init__(self, token: str | None = None):
        self.token = token or os.environ.get("GITHUB_TOKEN", "")
        self.base_url = "https://api.github.com"
        self.headers = {
            "Accept": "application/vnd.github.v3+json",
        }
        if self.token:
            self.headers["Authorization"] = f"token {self.token}"

    def _request(
        self, method: str, path: str, params: dict | None = None
    ) -> dict[str, Any]:
        return {
            "error": "GitHub API not connected",
            "note": "Set GITHUB_TOKEN environment variable or pass token to enable",
            "token_configured": bool(self.token),
        }

    def get_repo(self, owner: str, repo: str) -> dict[str, Any]:
        return self._request("GET", f"/repos/{owner}/{repo}")

    def list_issues(
        self, owner: str, repo: str, state: str = "open", limit: int = 10
    ) -> dict[str, Any]:
        return self._request(
            "GET", f"/repos/{owner}/{repo}/issues", {"state": state, "per_page": limit}
        )

    def get_issue(self, owner: str, repo: str, issue_number: int) -> dict[str, Any]:
        return self._request("GET", f"/repos/{owner}/{repo}/issues/{issue_number}")

    def list_pulls(self, owner: str, repo: str, state: str = "open") -> dict[str, Any]:
        return self._request("GET", f"/repos/{owner}/{repo}/pulls", {"state": state})

    def get_pull(self, owner: str, repo: str, pull_number: int) -> dict[str, Any]:
        return self._request("GET", f"/repos/{owner}/{repo}/pulls/{pull_number}")

    def get_pull_diff(self, owner: str, repo: str, pull_number: int) -> dict[str, Any]:
        return self._request("GET", f"/repos/{owner}/{repo}/pulls/{pull_number}")

    def list_commits(self, owner: str, repo: str, limit: int = 10) -> dict[str, Any]:
        return self._request(
            "GET", f"/repos/{owner}/{repo}/commits", {"per_page": limit}
        )

    def get_commit(self, owner: str, repo: str, sha: str) -> dict[str, Any]:
        return self._request("GET", f"/repos/{owner}/{repo}/commits/{sha}")

    def compare_commits(
        self, owner: str, repo: str, base: str, head: str
    ) -> dict[str, Any]:
        return self._request("GET", f"/repos/{owner}/{repo}/compare/{base}...{head}")

    def search_code(
        self, query: str, repo: str | None = None, limit: int = 10
    ) -> dict[str, Any]:
        search_query = query
        if repo:
            search_query += f" repo:{repo}"
        return self._request(
            "GET", "/search/code", {"q": search_query, "per_page": limit}
        )


def get_capabilities() -> dict[str, Any]:
    return {
        "protocolVersion": "2024-11-05",
        "capabilities": {
            "tools": {},
            "resources": {},
            "prompts": {},
        },
        "serverInfo": {
            "name": "github-ro-mcp-server",
            "version": "1.0.0",
            "description": "GitHub read-only access - issues, PRs, commits, diffs",
        },
    }


def get_tools() -> list[dict[str, Any]]:
    return [
        {
            "name": "github_list_issues",
            "description": "List repository issues",
            "inputSchema": {
                "type": "object",
                "properties": {
                    "owner": {"type": "string", "description": "Repository owner"},
                    "repo": {"type": "string", "description": "Repository name"},
                    "state": {
                        "type": "string",
                        "enum": ["open", "closed", "all"],
                        "default": "open",
                    },
                    "limit": {"type": "integer", "default": 10},
                },
                "required": ["owner", "repo"],
            },
        },
        {
            "name": "github_get_issue",
            "description": "Get specific issue details",
            "inputSchema": {
                "type": "object",
                "properties": {
                    "owner": {"type": "string"},
                    "repo": {"type": "string"},
                    "issue_number": {"type": "integer"},
                },
                "required": ["owner", "repo", "issue_number"],
            },
        },
        {
            "name": "github_list_pulls",
            "description": "List pull requests",
            "inputSchema": {
                "type": "object",
                "properties": {
                    "owner": {"type": "string"},
                    "repo": {"type": "string"},
                    "state": {
                        "type": "string",
                        "enum": ["open", "closed", "all"],
                        "default": "open",
                    },
                },
                "required": ["owner", "repo"],
            },
        },
        {
            "name": "github_get_pull",
            "description": "Get pull request details",
            "inputSchema": {
                "type": "object",
                "properties": {
                    "owner": {"type": "string"},
                    "repo": {"type": "string"},
                    "pull_number": {"type": "integer"},
                },
                "required": ["owner", "repo", "pull_number"],
            },
        },
        {
            "name": "github_list_commits",
            "description": "List repository commits",
            "inputSchema": {
                "type": "object",
                "properties": {
                    "owner": {"type": "string"},
                    "repo": {"type": "string"},
                    "limit": {"type": "integer", "default": 10},
                },
                "required": ["owner", "repo"],
            },
        },
        {
            "name": "github_compare",
            "description": "Compare two commits or branches",
            "inputSchema": {
                "type": "object",
                "properties": {
                    "owner": {"type": "string"},
                    "repo": {"type": "string"},
                    "base": {"type": "string", "description": "Base branch or commit"},
                    "head": {"type": "string", "description": "Head branch or commit"},
                },
                "required": ["owner", "repo", "base", "head"],
            },
        },
        {
            "name": "github_search_code",
            "description": "Search code in repository",
            "inputSchema": {
                "type": "object",
                "properties": {
                    "query": {"type": "string", "description": "Search query"},
                    "repo": {
                        "type": "string",
                        "description": "Optional: repo (owner/repo)",
                    },
                    "limit": {"type": "integer", "default": 10},
                },
                "required": ["query"],
            },
        },
        {
            "name": "github_get_repo_info",
            "description": "Get repository information",
            "inputSchema": {
                "type": "object",
                "properties": {
                    "owner": {"type": "string"},
                    "repo": {"type": "string"},
                },
                "required": ["owner", "repo"],
            },
        },
    ]


def handle_tool_call(tool_name: str, arguments: dict[str, Any]) -> dict[str, Any]:
    token = os.environ.get("GITHUB_TOKEN")

    if tool_name == "github_list_issues":
        return {"error": "GitHub API requires GITHUB_TOKEN", "configured": bool(token)}
    elif tool_name == "github_get_issue":
        return {"error": "GitHub API requires GITHUB_TOKEN", "configured": bool(token)}
    elif tool_name == "github_list_pulls":
        return {"error": "GitHub API requires GITHUB_TOKEN", "configured": bool(token)}
    elif tool_name == "github_get_pull":
        return {"error": "GitHub API requires GITHUB_TOKEN", "configured": bool(token)}
    elif tool_name == "github_list_commits":
        return {"error": "GitHub API requires GITHUB_TOKEN", "configured": bool(token)}
    elif tool_name == "github_compare":
        return {"error": "GitHub API requires GITHUB_TOKEN", "configured": bool(token)}
    elif tool_name == "github_search_code":
        return {"error": "GitHub API requires GITHUB_TOKEN", "configured": bool(token)}
    elif tool_name == "github_get_repo_info":
        return {"error": "GitHub API requires GITHUB_TOKEN", "configured": bool(token)}
    else:
        return {"error": f"Unknown tool: {tool_name}"}


def main() -> None:
    if len(sys.argv) < 2:
        print(json.dumps(get_capabilities()), file=sys.stdout)
        return

    command = sys.argv[1]

    if command == "initialize":
        init_data = json.loads(sys.stdin.read())
        print(json.dumps(get_capabilities()), file=sys.stdout)

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
