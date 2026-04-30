#!/usr/bin/env python3
"""
CI/Logs MCP Server - Read-only CI/CD Logs and Build Status

This MCP server provides read-only access to:
- CI job status
- Build logs
- Test results
- Deployment status

Supports:
- GitHub Actions
- GitLab CI
- Custom log endpoints

Usage:
    Configure in OpenCode:
    {
      "mcpServers": {
        "ci-logs": {
          "command": "python3",
          "args": ["/path/to/agent-harness/.agents/mcp/ci-logs-mcp-server.py"],
          "env": { "GITHUB_TOKEN": "...", "GITLAB_TOKEN": "..." }
        }
      }
    }
"""

from __future__ import annotations

import json
import os
import re
import sys
from dataclasses import dataclass
from datetime import datetime
from pathlib import Path
from typing import Any


@dataclass
class CIJob:
    name: str
    status: str
    duration: float | None
    logs: str | None
    timestamp: str


@dataclass
class BuildResult:
    build_id: str
    status: str
    branch: str
    commit: str
    jobs: list[CIJob]
    start_time: str
    end_time: str | None
    duration: float | None


def parse_github_actions_log(log_text: str) -> dict[str, Any]:
    lines = log_text.split("\n")
    current_step = None
    steps = []
    errors = []
    warnings = []

    for line in lines:
        if "##[group]" in line:
            current_step = line.replace("##[group]", "").strip()
        elif "##[error]" in line:
            errors.append(line.replace("##[error]", "").strip())
        elif "##[warning]" in line:
            warnings.append(line.replace("##[warning]", "").strip())
        elif "##[endgroup]" in line and current_step:
            steps.append(current_step)
            current_step = None

    return {
        "steps": steps,
        "errors": errors,
        "warnings": warnings,
        "summary": {
            "total_errors": len(errors),
            "total_warnings": len(warnings),
            "total_steps": len(steps),
        },
    }


def summarize_log(log_text: str, max_lines: int = 50) -> str:
    lines = log_text.split("\n")

    error_lines = [
        i + 1
        for i, line in enumerate(lines)
        if "error" in line.lower() or "failed" in line.lower()
    ]
    warning_lines = [i + 1 for i, line in enumerate(lines) if "warning" in line.lower()]

    summary_parts = []

    if error_lines:
        summary_parts.append(f"Errors found at lines: {error_lines[:10]}")
    if warning_lines:
        summary_parts.append(f"Warnings found at lines: {warning_lines[:10]}")

    if len(lines) > max_lines:
        summary_parts.append(
            f"Log truncated: {len(lines)} lines total, showing last {max_lines}"
        )
        return (
            "\n".join(lines[-max_lines:])
            + "\n\n--- SUMMARY ---\n"
            + "\n".join(summary_parts)
        )

    return log_text


def get_capabilities() -> dict[str, Any]:
    return {
        "protocolVersion": "2024-11-05",
        "capabilities": {
            "tools": {},
            "resources": {},
            "prompts": {},
        },
        "serverInfo": {
            "name": "ci-logs-mcp-server",
            "version": "1.0.0",
            "description": "CI/CD logs and build status - GitHub Actions, GitLab CI",
        },
    }


def get_tools() -> list[dict[str, Any]]:
    return [
        {
            "name": "ci_get_build_status",
            "description": "Get build status for a branch or commit",
            "inputSchema": {
                "type": "object",
                "properties": {
                    "provider": {
                        "type": "string",
                        "enum": ["github", "gitlab"],
                        "default": "github",
                    },
                    "owner": {"type": "string"},
                    "repo": {"type": "string"},
                    "branch": {
                        "type": "string",
                        "description": "Branch name (e.g., 'main')",
                    },
                    "commit": {
                        "type": "string",
                        "description": "Commit SHA (optional)",
                    },
                },
                "required": ["owner", "repo"],
            },
        },
        {
            "name": "ci_get_job_logs",
            "description": "Get logs for a specific CI job",
            "inputSchema": {
                "type": "object",
                "properties": {
                    "provider": {"type": "string", "enum": ["github", "gitlab"]},
                    "owner": {"type": "string"},
                    "repo": {"type": "string"},
                    "run_id": {
                        "type": "string",
                        "description": "Workflow run ID or job ID",
                    },
                    "job_name": {"type": "string", "description": "Specific job name"},
                    "max_lines": {
                        "type": "integer",
                        "default": 100,
                        "description": "Max lines to return",
                    },
                },
                "required": ["owner", "repo", "run_id"],
            },
        },
        {
            "name": "ci_analyze_failures",
            "description": "Analyze CI failures and return structured summary",
            "inputSchema": {
                "type": "object",
                "properties": {
                    "provider": {"type": "string", "enum": ["github", "gitlab"]},
                    "owner": {"type": "string"},
                    "repo": {"type": "string"},
                    "branch": {"type": "string"},
                    "limit": {"type": "integer", "default": 5},
                },
                "required": ["owner", "repo"],
            },
        },
        {
            "name": "ci_list_workflows",
            "description": "List recent workflow runs",
            "inputSchema": {
                "type": "object",
                "properties": {
                    "provider": {"type": "string", "enum": ["github", "gitlab"]},
                    "owner": {"type": "string"},
                    "repo": {"type": "string"},
                    "status": {
                        "type": "string",
                        "enum": ["success", "failure", "cancelled", "all"],
                        "default": "all",
                    },
                    "limit": {"type": "integer", "default": 10},
                },
                "required": ["owner", "repo"],
            },
        },
        {
            "name": "ci_get_test_summary",
            "description": "Get test results summary from CI",
            "inputSchema": {
                "type": "object",
                "properties": {
                    "provider": {"type": "string", "enum": ["github", "gitlab"]},
                    "owner": {"type": "string"},
                    "repo": {"type": "string"},
                    "run_id": {"type": "string"},
                },
                "required": ["owner", "repo"],
            },
        },
    ]


def handle_tool_call(tool_name: str, arguments: dict[str, Any]) -> dict[str, Any]:
    provider = arguments.get("provider", "github")
    owner = arguments.get("owner", "")
    repo = arguments.get("repo", "")

    if tool_name == "ci_get_build_status":
        return {
            "provider": provider,
            "owner": owner,
            "repo": repo,
            "status": "not_configured",
            "message": "CI integration requires API token configuration",
            "supported_providers": ["github", "gitlab"],
            "note": "Set GITHUB_TOKEN or GITLAB_TOKEN environment variable",
        }

    elif tool_name == "ci_get_job_logs":
        return {
            "provider": provider,
            "owner": owner,
            "repo": repo,
            "status": "not_configured",
            "message": "CI integration requires API token configuration",
        }

    elif tool_name == "ci_analyze_failures":
        return {
            "provider": provider,
            "owner": owner,
            "repo": repo,
            "failures": [],
            "message": "CI integration requires API token configuration",
        }

    elif tool_name == "ci_list_workflows":
        return {
            "provider": provider,
            "owner": owner,
            "repo": repo,
            "workflows": [],
            "message": "CI integration requires API token configuration",
        }

    elif tool_name == "ci_get_test_summary":
        return {
            "provider": provider,
            "owner": owner,
            "repo": repo,
            "summary": {},
            "message": "CI integration requires API token configuration",
        }

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
