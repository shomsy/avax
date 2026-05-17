#!/usr/bin/env python3
# trust-identity.py — V5.2.0 Multi-Agent Trust & Identity Engine (Phase 5)
#
# Architectural Planes:
# 1. Identity Plane: Execution identity chains, delegation lineage, authority inheritance.
# 2. Delegation Plane: Capability narrowing proofs, transitive delegation validation.
# 3. Security Plane: Confused deputy protection, ephemeral authority tokens.
# 4. Audit Plane: Full delegation audit graph, authorization path resolution.

import os
import sys
import json
import time
import uuid
import hashlib


# ---------------------------------------------------------------------------
# Storage paths (relative to target_dir)
# ---------------------------------------------------------------------------
IDENTITY_DIR = ".agents/management/evidence/identity"
IDENTITIES_PATH = os.path.join(IDENTITY_DIR, "identities.jsonl")
DELEGATION_LINEAGE_PATH = os.path.join(IDENTITY_DIR, "delegation-lineage.jsonl")
AUTHORITY_GRAPH_PATH = os.path.join(IDENTITY_DIR, "authority-graph.json")
NARROWING_PROOFS_PATH = os.path.join(IDENTITY_DIR, "narrowing-proofs.jsonl")
EPHEMERAL_TOKENS_PATH = os.path.join(IDENTITY_DIR, "ephemeral-tokens.jsonl")
AUDIT_GRAPH_PATH = os.path.join(IDENTITY_DIR, "delegation-audit-graph.json")

# Tier ranking (same as execution-substrate.py)
TIER_RANKS = {
    "READ_ONLY": 1,
    "WORKSPACE_WRITE": 2,
    "GOVERNANCE_WRITE": 3,
    "TRUSTED": 4,
}


# ---------------------------------------------------------------------------
# Utility helpers
# ---------------------------------------------------------------------------
def _ensure_dir(path):
    os.makedirs(os.path.dirname(path) if os.path.dirname(path) else path, exist_ok=True)


def _jsonl_append(path, record):
    _ensure_dir(path)
    with open(path, "a", encoding="utf-8") as f:
        f.write(json.dumps(record) + "\n")


def _jsonl_read(path):
    if not os.path.exists(path):
        return []
    records = []
    with open(path, "r", encoding="utf-8") as f:
        for line in f:
            line = line.strip()
            if line:
                records.append(json.loads(line))
    return records


def _json_read(path):
    if not os.path.exists(path):
        return None
    with open(path, "r", encoding="utf-8") as f:
        return json.load(f)


def _json_write(path, data):
    _ensure_dir(path)
    with open(path, "w", encoding="utf-8") as f:
        json.dump(data, f, indent=2)


def _compute_hash(data_str):
    return hashlib.sha256(data_str.encode("utf-8")).hexdigest()


def _generate_id(prefix=""):
    return f"{prefix}-{uuid.uuid4()}"


# ---------------------------------------------------------------------------
# 1. ExecutionIdentityChain
# ---------------------------------------------------------------------------
class ExecutionIdentityChain:
    """Tracks execution identity: every execution has a unique identity derived
    from token_id + nonce + timestamp + executor_hash."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)
        self.path = os.path.join(self.target_dir, IDENTITIES_PATH)
        _ensure_dir(self.path)

    def create_identity(self, token, nonce, metadata=None):
        """Creates an execution identity and persists it.

        Args:
            token: Capability token dict (must have 'token_id').
            nonce: Unique execution nonce string.
            metadata: Optional dict of additional metadata.

        Returns:
            The identity dict including exec_id.
        """
        token_id = token.get("token_id", token) if isinstance(token, dict) else token
        timestamp = time.time()
        executor_hash = _compute_hash(f"{os.getpid()}:{os.uname().nodename}")

        identity_str = f"{token_id}:{nonce}:{timestamp}:{executor_hash}"
        identity_hash = _compute_hash(identity_str)

        identity = {
            "exec_id": _generate_id("exec"),
            "token_id": token_id,
            "nonce": nonce,
            "timestamp": timestamp,
            "executor_hash": executor_hash,
            "identity_hash": identity_hash,
            "metadata": metadata or {},
        }

        _jsonl_append(self.path, identity)
        return identity

    def verify_identity(self, identity):
        """Verifies identity is valid (hash matches and record exists).

        Args:
            identity: Dict with identity fields.

        Returns:
            Tuple of (valid: bool, reason: str).
        """
        required = ("token_id", "nonce", "timestamp", "executor_hash", "identity_hash")
        for field in required:
            if field not in identity:
                return False, f"Missing required field: {field}"

        # Recompute hash
        token_id = identity["token_id"]
        nonce = identity["nonce"]
        timestamp = identity["timestamp"]
        executor_hash = identity["executor_hash"]
        expected_hash = _compute_hash(f"{token_id}:{nonce}:{timestamp}:{executor_hash}")

        if identity["identity_hash"] != expected_hash:
            return False, "Identity hash mismatch — possible tampering"

        # Check it exists in the store
        exec_id = identity.get("exec_id")
        if exec_id:
            stored = self._find_by_exec_id(exec_id)
            if stored is None:
                return False, f"Execution identity not found in store: {exec_id}"

        return True, "Identity verified"

    def get_identity_chain(self, exec_id):
        """Returns the full chain of identities for an execution.

        The chain includes the identity itself plus any parent identities
        linked via the authority lineage metadata.

        Args:
            exec_id: The execution identifier.

        Returns:
            List of identity dicts from root to leaf.
        """
        records = _jsonl_read(self.path)
        chain = []

        # Find the target identity
        target = None
        for r in records:
            if r.get("exec_id") == exec_id:
                target = r
                break

        if target is None:
            return chain

        chain.append(target)

        # Walk parent links via metadata
        current = target
        while True:
            parent_exec_id = current.get("metadata", {}).get("parent_exec_id")
            if not parent_exec_id:
                break
            parent = None
            for r in records:
                if r.get("exec_id") == parent_exec_id:
                    parent = r
                    break
            if parent is None:
                break
            chain.insert(0, parent)
            current = parent

        return chain

    def _find_by_exec_id(self, exec_id):
        for r in _jsonl_read(self.path):
            if r.get("exec_id") == exec_id:
                return r
        return None

    def get_by_exec_id(self, exec_id):
        """Public getter for a single identity by exec_id."""
        return self._find_by_exec_id(exec_id)


# ---------------------------------------------------------------------------
# 2. DelegationLineage
# ---------------------------------------------------------------------------
class DelegationLineage:
    """Tracks delegation lineage from root authority to leaf tokens."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)
        self.path = os.path.join(self.target_dir, DELEGATION_LINEAGE_PATH)
        _ensure_dir(self.path)

    def record_delegation(self, parent_token_id, child_token_id, narrowing_proof):
        """Records a delegation event.

        Args:
            parent_token_id: The delegating (parent) token identifier.
            child_token_id: The delegated (child) token identifier.
            narrowing_proof: Dict proving child authority is narrowed.

        Returns:
            The delegation record dict.
        """
        record = {
            "delegation_id": _generate_id("deleg"),
            "parent_token_id": parent_token_id,
            "child_token_id": child_token_id,
            "narrowing_proof_hash": _compute_hash(json.dumps(narrowing_proof, sort_keys=True)),
            "narrowing_proof": narrowing_proof,
            "timestamp": time.time(),
        }

        _jsonl_append(self.path, record)
        return record

    def get_delegation_chain(self, token_id):
        """Returns full delegation chain back to root for a given token.

        Args:
            token_id: The token identifier to trace.

        Returns:
            List of delegation records from root to the given token.
            Returns empty list if token not found.
        """
        records = _jsonl_read(self.path)
        chain = []

        # Build a lookup: child_token_id -> delegation record
        child_map = {}
        for r in records:
            child_map[r["child_token_id"]] = r

        # Walk backwards from the token to root
        current_id = token_id
        path = []
        visited = set()
        while current_id in child_map:
            if current_id in visited:
                # Loop detected — break
                break
            visited.add(current_id)
            rec = child_map[current_id]
            path.append(rec)
            current_id = rec["parent_token_id"]

        # Reverse to get root -> leaf order
        chain = list(reversed(path))
        return chain

    def verify_delegation_chain(self, token_id):
        """Verifies entire chain is valid (no gaps, proofs intact).

        Args:
            token_id: The leaf token identifier.

        Returns:
            Tuple of (valid: bool, reason: str).
        """
        chain = self.get_delegation_chain(token_id)

        if not chain:
            return False, f"No delegation chain found for token: {token_id}"

        # Verify each link
        for i, rec in enumerate(chain):
            # Check narrowing proof exists
            if not rec.get("narrowing_proof"):
                return False, f"Delegation link {i} missing narrowing proof"

            # Verify proof hash
            expected_hash = _compute_hash(json.dumps(rec["narrowing_proof"], sort_keys=True))
            if rec.get("narrowing_proof_hash") != expected_hash:
                return False, f"Delegation link {i} narrowing proof hash mismatch"

            # Check chain continuity (root -> leaf: each link's parent = prev link's child)
            if i > 0:
                prev_child = chain[i - 1]["child_token_id"]
                current_parent = rec["parent_token_id"]
                if current_parent != prev_child:
                    return False, (
                        f"Chain break between link {i-1} ({prev_child}) "
                        f"and link {i} parent ({current_parent})"
                    )

        return True, "Delegation chain verified"

    def detect_broken_chain(self, token_id):
        """Finds breaks in delegation chain.

        Args:
            token_id: The leaf token identifier.

        Returns:
            Dict with break details, or None if chain is intact.
        """
        records = _jsonl_read(self.path)
        child_map = {}
        for r in records:
            child_map[r["child_token_id"]] = r

        current_id = token_id
        visited = set()
        breaks = []

        while current_id in child_map:
            if current_id in visited:
                breaks.append({
                    "type": "loop",
                    "token_id": current_id,
                    "message": f"Circular delegation detected at {current_id}",
                })
                break
            visited.add(current_id)

            rec = child_map[current_id]
            parent_id = rec["parent_token_id"]

            # Check if parent exists in any chain (either as child or known root)
            all_children = set(child_map.keys())
            if parent_id not in all_children and parent_id != "operator-root":
                # Check if parent is a known root (has no parent itself)
                parent_is_root = parent_id not in child_map
                if not parent_is_root:
                    breaks.append({
                        "type": "missing_parent",
                        "token_id": current_id,
                        "missing_parent": parent_id,
                        "message": f"Parent {parent_id} not found in delegation records",
                    })
                    break

            current_id = parent_id

        if breaks:
            return {
                "token_id": token_id,
                "chain_broken": True,
                "breaks": breaks,
            }
        return None


# ---------------------------------------------------------------------------
# 3. AuthorityInheritance
# ---------------------------------------------------------------------------
class AuthorityInheritance:
    """Tracks authority inheritance through delegation chains."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)
        self.path = os.path.join(self.target_dir, AUTHORITY_GRAPH_PATH)
        _ensure_dir(self.path)
        self.graph = _json_read(self.path) or {"nodes": {}, "edges": []}

    def _save(self):
        _json_write(self.path, self.graph)

    def compute_inherited_authority(self, child_token, parent_token):
        """Computes what authority child inherits from parent.

        Authority is the intersection of parent's authority with child's
        requested scope, narrowed by trust tier.

        Args:
            child_token: Dict with allowed_tools, allowed_scopes, trust_tier.
            parent_token: Dict with allowed_tools, allowed_scopes, trust_tier.

        Returns:
            Dict representing the inherited authority scope.
        """
        parent_tools = set(parent_token.get("allowed_tools", []))
        parent_scopes = set(parent_token.get("allowed_scopes", []))
        parent_tier = parent_token.get("trust_tier", "READ_ONLY")

        child_tools = set(child_token.get("allowed_tools", []))
        child_scopes = set(child_token.get("allowed_scopes", []))
        child_tier = child_token.get("trust_tier", "READ_ONLY")

        # Intersection: child gets only what parent has AND child requests
        inherited_tools = sorted(parent_tools & child_tools)
        inherited_scopes = sorted(parent_scopes & child_scopes)

        # Tier is the minimum of parent and child
        parent_rank = TIER_RANKS.get(parent_tier, 0)
        child_rank = TIER_RANKS.get(child_tier, 0)
        inherited_tier_rank = min(parent_rank, child_rank)
        inherited_tier = [t for t, r in TIER_RANKS.items() if r == inherited_tier_rank]
        inherited_tier = inherited_tier[0] if inherited_tier else "READ_ONLY"

        result = {
            "allowed_tools": inherited_tools,
            "allowed_scopes": inherited_scopes,
            "trust_tier": inherited_tier,
            "parent_tier": parent_tier,
            "child_tier": child_tier,
        }

        # Update graph
        child_id = child_token.get("token_id", "unknown")
        parent_id = parent_token.get("token_id", "unknown")
        self.graph["nodes"][child_id] = {
            "token_id": child_id,
            "authority": result,
            "timestamp": time.time(),
        }
        self.graph["edges"].append({
            "source": parent_id,
            "target": child_id,
            "inherited_tools": inherited_tools,
            "inherited_scopes": inherited_scopes,
            "inherited_tier": inherited_tier,
        })
        self._save()

        return result

    def verify_narrowing(self, parent, child):
        """Verifies child authority is a strict subset of parent.

        Args:
            parent: Dict with allowed_tools, allowed_scopes, trust_tier.
            child: Dict with allowed_tools, allowed_scopes, trust_tier.

        Returns:
            Tuple of (valid: bool, reason: str).
        """
        parent_tools = set(parent.get("allowed_tools", []))
        parent_scopes = set(parent.get("allowed_scopes", []))
        parent_tier = parent.get("trust_tier", "READ_ONLY")

        child_tools = set(child.get("allowed_tools", []))
        child_scopes = set(child.get("allowed_scopes", []))
        child_tier = child.get("trust_tier", "READ_ONLY")

        # Tools must be subset
        if not child_tools.issubset(parent_tools):
            escalated = child_tools - parent_tools
            return False, f"Tool escalation: {escalated} not in parent"

        # Scopes must be subset
        if not child_scopes.issubset(parent_scopes):
            escalated = child_scopes - parent_scopes
            return False, f"Scope escalation: {escalated} not in parent"

        # Tier must not escalate
        parent_rank = TIER_RANKS.get(parent_tier, 0)
        child_rank = TIER_RANKS.get(child_tier, 0)
        if child_rank > parent_rank:
            return False, f"Tier escalation: {child_tier} > {parent_tier}"

        # Strict narrowing: at least one dimension must be strictly smaller
        strict_narrowing = (
            child_tools < parent_tools
            or child_scopes < parent_scopes
            or child_rank < parent_rank
        )

        if not strict_narrowing:
            return False, "No narrowing — child has identical authority to parent"

        return True, "Valid narrowing"

    def get_authority_scope(self, token_id):
        """Returns effective authority scope for a token.

        Args:
            token_id: The token identifier.

        Returns:
            Dict with authority scope, or None if not found.
        """
        return self.graph.get("nodes", {}).get(token_id)


# ---------------------------------------------------------------------------
# 4. CapabilityNarrowingProof
# ---------------------------------------------------------------------------
class CapabilityNarrowingProof:
    """Cryptographic proofs of capability narrowing."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)
        self.path = os.path.join(self.target_dir, NARROWING_PROOFS_PATH)
        _ensure_dir(self.path)

    def create_narrowing_proof(self, parent_token, child_token):
        """Creates proof that child authority is strictly narrowed from parent.

        Args:
            parent_token: Dict with token_id, allowed_tools, allowed_scopes, trust_tier.
            child_token: Dict with token_id, allowed_tools, allowed_scopes, trust_tier.

        Returns:
            Dict containing the narrowing proof.
        """
        parent_tools = set(parent_token.get("allowed_tools", []))
        parent_scopes = set(parent_token.get("allowed_scopes", []))
        parent_tier = TIER_RANKS.get(parent_token.get("trust_tier", "READ_ONLY"), 0)

        child_tools = set(child_token.get("allowed_tools", []))
        child_scopes = set(child_token.get("allowed_scopes", []))
        child_tier = TIER_RANKS.get(child_token.get("trust_tier", "READ_ONLY"), 0)

        # Compute what was removed (narrowed away)
        removed_tools = sorted(parent_tools - child_tools)
        removed_scopes = sorted(parent_scopes - child_scopes)
        tier_delta = parent_tier - child_tier

        # Proof payload
        proof_payload = {
            "parent_token_id": parent_token.get("token_id", "unknown"),
            "child_token_id": child_token.get("token_id", "unknown"),
            "parent_tools": sorted(parent_tools),
            "child_tools": sorted(child_tools),
            "removed_tools": removed_tools,
            "parent_scopes": sorted(parent_scopes),
            "child_scopes": sorted(child_scopes),
            "removed_scopes": removed_scopes,
            "parent_tier_rank": parent_tier,
            "child_tier_rank": child_tier,
            "tier_delta": tier_delta,
            "timestamp": time.time(),
        }

        # Sign the proof
        proof_hash = _compute_hash(json.dumps(proof_payload, sort_keys=True))
        proof_payload["proof_hash"] = proof_hash

        # Persist
        _jsonl_append(self.path, proof_payload)

        return proof_payload

    def verify_narrowing_proof(self, proof):
        """Verifies a narrowing proof is valid.

        Args:
            proof: Dict containing a narrowing proof.

        Returns:
            Tuple of (valid: bool, reason: str).
        """
        required = (
            "parent_token_id", "child_token_id",
            "parent_tools", "child_tools", "removed_tools",
            "parent_scopes", "child_scopes", "removed_scopes",
            "parent_tier_rank", "child_tier_rank", "tier_delta",
            "proof_hash",
        )
        for field in required:
            if field not in proof:
                return False, f"Missing proof field: {field}"

        # Verify hash
        verify_payload = {k: v for k, v in proof.items() if k != "proof_hash"}
        expected_hash = _compute_hash(json.dumps(verify_payload, sort_keys=True))
        if proof["proof_hash"] != expected_hash:
            return False, "Proof hash mismatch — proof was tampered"

        # Verify narrowing logic
        parent_tools = set(proof["parent_tools"])
        child_tools = set(proof["child_tools"])
        removed_tools = set(proof["removed_tools"])

        if child_tools | removed_tools != parent_tools:
            return False, "Tool set inconsistency: child + removed != parent"
        if child_tools & removed_tools:
            return False, "Tool set error: child and removed overlap"

        parent_scopes = set(proof["parent_scopes"])
        child_scopes = set(proof["child_scopes"])
        removed_scopes = set(proof["removed_scopes"])

        if child_scopes | removed_scopes != parent_scopes:
            return False, "Scope set inconsistency: child + removed != parent"
        if child_scopes & removed_scopes:
            return False, "Scope set error: child and removed overlap"

        # Verify tier delta
        if proof["tier_delta"] != proof["parent_tier_rank"] - proof["child_tier_rank"]:
            return False, "Tier delta mismatch"

        # Must be strictly narrowing (at least one dimension reduced)
        if not (removed_tools or removed_scopes or proof["tier_delta"] > 0):
            return False, "Proof shows no narrowing — authority not reduced"

        return True, "Narrowing proof verified"

    def detect_confused_deputy(self, exec_manifest):
        """Detects confused deputy attacks in an execution manifest.

        A confused deputy occurs when an agent uses inherited authority
        for purposes outside the delegation intent.

        Args:
            exec_manifest: Dict with execution details including
                capability_token, domain_scope, and authority_lineage.

        Returns:
            Dict with violation report (empty if no violations).
        """
        violations = []

        token = exec_manifest.get("capability_token", {})
        domain_scope = exec_manifest.get("domain_scope", "")
        lineage = exec_manifest.get("authority_lineage", {})

        # Check 1: Execution scope matches token scope
        token_scopes = set(token.get("allowed_scopes", []))
        if domain_scope and domain_scope not in token_scopes:
            violations.append({
                "type": "scope_mismatch",
                "message": f"Execution domain '{domain_scope}' not in token scopes {token_scopes}",
                "severity": "HIGH",
            })

        # Check 2: Tools used match allowed tools
        tools_used = exec_manifest.get("tools_used", [])
        allowed_tools = set(token.get("allowed_tools", []))
        if tools_used:
            unauthorized_tools = set(tools_used) - allowed_tools
            if unauthorized_tools:
                violations.append({
                    "type": "unauthorized_tool",
                    "message": f"Tools used outside token authority: {unauthorized_tools}",
                    "severity": "CRITICAL",
                })

        # Check 3: Trust tier appropriate for action
        trust_tier = token.get("trust_tier", "READ_ONLY")
        task = exec_manifest.get("task", "")
        if trust_tier == "READ_ONLY" and any(
            kw in task.lower() for kw in ["write", "create", "delete", "modify", "update"]
        ):
            violations.append({
                "type": "tier_insufficient",
                "message": f"READ_ONLY token used for potentially mutating task: {task}",
                "severity": "MEDIUM",
            })

        return {
            "exec_id": exec_manifest.get("execution_id", "unknown"),
            "confused_deputy_detected": len(violations) > 0,
            "violations": violations,
            "timestamp": time.time(),
        }


# ---------------------------------------------------------------------------
# 5. ConfusedDeputyProtection
# ---------------------------------------------------------------------------
class ConfusedDeputyProtection:
    """Prevents confused deputy attacks by validating scope-purpose alignment."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)
        self.narrowing_proof = CapabilityNarrowingProof(target_dir)

    def check_confused_deputy(self, exec_manifest):
        """Checks if execution scope matches delegation intent.

        Args:
            exec_manifest: Dict with execution details.

        Returns:
            Dict with violation report.
        """
        return self.narrowing_proof.detect_confused_deputy(exec_manifest)

    def validate_scope_purpose(self, exec_manifest, delegation_intent):
        """Validates that execution purpose aligns with delegation intent.

        Args:
            exec_manifest: Dict with execution details.
            delegation_intent: Dict describing intended purpose
                (purpose, scopes, tools, trust_tier).

        Returns:
            Tuple of (valid: bool, reason: str).
        """
        token = exec_manifest.get("capability_token", {})
        domain_scope = exec_manifest.get("domain_scope", "")

        intent_scopes = set(delegation_intent.get("scopes", []))
        intent_tools = set(delegation_intent.get("tools", []))
        intent_purpose = delegation_intent.get("purpose", "")

        # Check scope alignment
        token_scopes = set(token.get("allowed_scopes", []))
        if intent_scopes and not (intent_scopes & token_scopes):
            return False, (
                f"Delegation intent scopes {intent_scopes} do not overlap "
                f"with token scopes {token_scopes}"
            )

        # Check tool alignment
        token_tools = set(token.get("allowed_tools", []))
        if intent_tools and not (intent_tools & token_tools):
            return False, (
                f"Delegation intent tools {intent_tools} do not overlap "
                f"with token tools {token_tools}"
            )

        # Check domain scope matches intent
        if intent_scopes and domain_scope and domain_scope not in intent_scopes:
            return False, (
                f"Execution domain '{domain_scope}' not in delegation intent "
                f"scopes {intent_scopes}"
            )

        return True, "Purpose alignment verified"

    def detect_scope_drift(self, exec_manifest):
        """Detects scope drift from original delegation intent.

        Args:
            exec_manifest: Dict with execution details and optional
                original_intent field.

        Returns:
            Dict with drift analysis.
        """
        token = exec_manifest.get("capability_token", {})
        original_intent = exec_manifest.get("original_intent", {})

        drift_report = {
            "exec_id": exec_manifest.get("execution_id", "unknown"),
            "drift_detected": False,
            "drifts": [],
        }

        if not original_intent:
            drift_report["drifts"].append({
                "type": "no_original_intent",
                "message": "No original intent recorded — cannot detect drift",
                "severity": "LOW",
            })
            return drift_report

        # Compare current token scope to original intent
        current_tools = set(token.get("allowed_tools", []))
        original_tools = set(original_intent.get("tools", []))
        current_scopes = set(token.get("allowed_scopes", []))
        original_scopes = set(original_intent.get("scopes", []))

        # Scope expansion (drift)
        if original_tools and not current_tools.issubset(original_tools):
            expanded = current_tools - original_tools
            drift_report["drift_detected"] = True
            drift_report["drifts"].append({
                "type": "tool_expansion",
                "expanded_tools": sorted(expanded),
                "message": f"Tools expanded beyond original intent: {expanded}",
                "severity": "HIGH",
            })

        if original_scopes and not current_scopes.issubset(original_scopes):
            expanded = current_scopes - original_scopes
            drift_report["drift_detected"] = True
            drift_report["drifts"].append({
                "type": "scope_expansion",
                "expanded_scopes": sorted(expanded),
                "message": f"Scopes expanded beyond original intent: {expanded}",
                "severity": "HIGH",
            })

        drift_report["timestamp"] = time.time()
        return drift_report


# ---------------------------------------------------------------------------
# 6. TransitiveDelegationValidator
# ---------------------------------------------------------------------------
class TransitiveDelegationValidator:
    """Validates transitive delegation chains."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)
        self.lineage = DelegationLineage(target_dir)
        self.authority = AuthorityInheritance(target_dir)

    def validate_transitive_delegation(self, token_id):
        """Validates full transitive delegation chain for a token.

        Args:
            token_id: The leaf token identifier.

        Returns:
            Tuple of (valid: bool, reason: str).
        """
        chain = self.lineage.get_delegation_chain(token_id)

        if not chain:
            return False, f"No delegation chain found for token: {token_id}"

        # Check chain validity
        valid, reason = self.lineage.verify_delegation_chain(token_id)
        if not valid:
            return False, f"Chain invalid: {reason}"

        # Check depth
        broken = self.lineage.detect_broken_chain(token_id)
        if broken:
            return False, f"Broken chain: {broken['breaks']}"

        return True, f"Transitive delegation valid ({len(chain)} hops)"

    def compute_trust_path(self, root_token_id, target_token_id):
        """Computes trust path from root to target token.

        Args:
            root_token_id: The root authority token identifier.
            target_token_id: The target token identifier.

        Returns:
            List of token_ids forming the trust path, or empty list if no path.
        """
        chain = self.lineage.get_delegation_chain(target_token_id)

        if not chain:
            return []

        # Check if the chain starts from the specified root
        if chain and chain[0]["parent_token_id"] == root_token_id:
            path = [root_token_id]
            for rec in chain:
                path.append(rec["child_token_id"])
            return path

        # Check if root is anywhere in the chain
        for i, rec in enumerate(chain):
            if rec["parent_token_id"] == root_token_id:
                path = [root_token_id]
                for r in chain[i:]:
                    path.append(r["child_token_id"])
                return path

        return []

    def detect_delegation_loops(self):
        """Detects circular delegation in the entire delegation graph.

        Returns:
            List of loop descriptions.
        """
        records = _jsonl_read(self.lineage.path)
        child_map = {}
        for r in records:
            child_map[r["child_token_id"]] = r["parent_token_id"]

        loops = []
        visited_global = set()

        for start_token in child_map:
            if start_token in visited_global:
                continue

            visited = set()
            current = start_token
            path = []

            while current in child_map:
                if current in visited:
                    # Found a loop
                    loop_start_idx = path.index(current)
                    loop = path[loop_start_idx:]
                    loop_desc = " -> ".join(loop) + " -> " + current
                    if loop_desc not in [l["loop_path"] for l in loops]:
                        loops.append({
                            "loop_tokens": loop,
                            "loop_path": loop_desc,
                        })
                    break

                visited.add(current)
                visited_global.add(current)
                path.append(current)
                current = child_map[current]

        return loops

    def validate_delegation_depth(self, token_id, max_depth=5):
        """Ensures delegation depth is bounded.

        Args:
            token_id: The leaf token identifier.
            max_depth: Maximum allowed delegation depth (default 5).

        Returns:
            Tuple of (valid: bool, reason: str, depth: int).
        """
        chain = self.lineage.get_delegation_chain(token_id)
        depth = len(chain)

        if depth > max_depth:
            return False, (
                f"Delegation depth {depth} exceeds maximum allowed depth {max_depth}"
            ), depth

        return True, f"Delegation depth {depth} within bounds (max {max_depth})", depth


# ---------------------------------------------------------------------------
# 7. EphemeralAuthorityToken
# ---------------------------------------------------------------------------
class EphemeralAuthorityToken:
    """Manages short-lived authority tokens."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)
        self.path = os.path.join(self.target_dir, EPHEMERAL_TOKENS_PATH)
        _ensure_dir(self.path)

    def create_ephemeral_token(self, parent, purpose, ttl_seconds=300):
        """Creates a short-lived ephemeral authority token.

        Args:
            parent: Dict representing parent token (must have token_id).
            purpose: String describing the purpose of this ephemeral token.
            ttl_seconds: Time-to-live in seconds (default 300 = 5 minutes).

        Returns:
            Dict representing the ephemeral token.
        """
        token_id = _generate_id("ephem")
        now = time.time()
        expires_at = now + ttl_seconds

        # Ephemeral tokens inherit narrowed authority from parent
        parent_tools = parent.get("allowed_tools", [])
        parent_scopes = parent.get("allowed_scopes", [])
        parent_tier = parent.get("trust_tier", "READ_ONLY")

        token = {
            "token_id": token_id,
            "parent_token_id": parent.get("token_id", "unknown"),
            "purpose": purpose,
            "allowed_tools": parent_tools,
            "allowed_scopes": parent_scopes,
            "trust_tier": parent_tier,
            "issued_at": now,
            "expires_at": expires_at,
            "ttl_seconds": ttl_seconds,
            "revoked": False,
            "ephemeral": True,
            "signature": _compute_hash(f"{token_id}:{now}:{ttl_seconds}:{purpose}"),
        }

        _jsonl_append(self.path, token)
        return token

    def is_ephemeral_valid(self, token_id):
        """Checks if an ephemeral token is still valid.

        Args:
            token_id: The ephemeral token identifier.

        Returns:
            Tuple of (valid: bool, reason: str).
        """
        tokens = _jsonl_read(self.path)
        token = None
        for t in tokens:
            if t.get("token_id") == token_id:
                token = t
                break

        if token is None:
            return False, f"Ephemeral token not found: {token_id}"

        if token.get("revoked", False):
            return False, f"Ephemeral token revoked: {token_id}"

        now = time.time()
        if now > token.get("expires_at", 0):
            return False, f"Ephemeral token expired: {token_id}"

        return True, f"Ephemeral token valid (expires in {token['expires_at'] - now:.0f}s)"

    def revoke_ephemeral(self, token_id):
        """Revokes an ephemeral token.

        Args:
            token_id: The ephemeral token identifier.

        Returns:
            Tuple of (success: bool, reason: str).
        """
        tokens = _jsonl_read(self.path)
        found = False

        for t in tokens:
            if t.get("token_id") == token_id:
                t["revoked"] = True
                t["revoked_at"] = time.time()
                found = True
                break

        if not found:
            return False, f"Ephemeral token not found: {token_id}"

        # Rewrite the file in JSONL format
        _ensure_dir(self.path)
        with open(self.path, "w", encoding="utf-8") as f:
            for t in tokens:
                f.write(json.dumps(t) + "\n")
        return True, f"Ephemeral token revoked: {token_id}"


# ---------------------------------------------------------------------------
# 8. DelegationAuditGraph
# ---------------------------------------------------------------------------
class DelegationAuditGraph:
    """Full delegation audit graph for external audit export."""

    def __init__(self, target_dir="."):
        self.target_dir = os.path.normpath(target_dir)
        self.path = os.path.join(self.target_dir, AUDIT_GRAPH_PATH)
        _ensure_dir(self.path)
        self.identity_chain = ExecutionIdentityChain(target_dir)
        self.delegation_lineage = DelegationLineage(target_dir)
        self.authority = AuthorityInheritance(target_dir)
        self.narrowing_proof = CapabilityNarrowingProof(target_dir)
        self.ephemeral = EphemeralAuthorityToken(target_dir)

    def build_audit_graph(self):
        """Builds complete delegation graph from all evidence sources.

        Returns:
            Dict representing the full audit graph.
        """
        identities = _jsonl_read(self.identity_chain.path)
        delegations = _jsonl_read(self.delegation_lineage.path)
        narrowing_proofs = _jsonl_read(self.narrowing_proof.path)
        ephemeral_tokens = _jsonl_read(self.ephemeral.path)

        graph = {
            "graph_id": _generate_id("audit"),
            "built_at": time.time(),
            "target_dir": self.target_dir,
            "identities": identities,
            "delegations": delegations,
            "narrowing_proofs": narrowing_proofs,
            "ephemeral_tokens": ephemeral_tokens,
            "authority_graph": self.authority.graph,
            "summary": {
                "total_identities": len(identities),
                "total_delegations": len(delegations),
                "total_narrowing_proofs": len(narrowing_proofs),
                "total_ephemeral_tokens": len(ephemeral_tokens),
                "authority_nodes": len(self.authority.graph.get("nodes", {})),
                "authority_edges": len(self.authority.graph.get("edges", [])),
            },
        }

        # Persist
        _json_write(self.path, graph)
        return graph

    def find_authorization_path(self, requested_action, token_id):
        """Finds a path that authorizes a given action for a token.

        Args:
            requested_action: Dict with action details (tool, scope, tier).
            token_id: The token identifier to check.

        Returns:
            Dict with authorization path or denial.
        """
        chain = self.delegation_lineage.get_delegation_chain(token_id)

        # Collect effective authority from the chain
        effective_tools = set()
        effective_scopes = set()
        effective_tier = 0

        for rec in chain:
            proof = rec.get("narrowing_proof", {})
            effective_tools.update(proof.get("child_tools", []))
            effective_scopes.update(proof.get("child_scopes", []))
            tier = proof.get("child_tier_rank", 0)
            if tier > 0:
                effective_tier = max(effective_tier, tier)

        # If no chain, check authority graph directly
        if not chain:
            auth_scope = self.authority.get_authority_scope(token_id)
            if auth_scope:
                authority = auth_scope.get("authority", {})
                effective_tools = set(authority.get("allowed_tools", []))
                effective_scopes = set(authority.get("allowed_scopes", []))
                effective_tier = TIER_RANKS.get(authority.get("trust_tier", ""), 0)

        action_tool = requested_action.get("tool", "")
        action_scope = requested_action.get("scope", "")
        action_tier = requested_action.get("tier", "READ_ONLY")
        action_tier_rank = TIER_RANKS.get(action_tier, 0)

        # Check authorization
        authorized = True
        denials = []

        if action_tool and action_tool not in effective_tools:
            authorized = False
            denials.append(f"Tool '{action_tool}' not in effective authority")

        if action_scope and action_scope not in effective_scopes:
            authorized = False
            denials.append(f"Scope '{action_scope}' not in effective authority")

        if action_tier_rank > effective_tier:
            authorized = False
            denials.append(
                f"Tier '{action_tier}' (rank {action_tier_rank}) exceeds "
                f"effective tier (rank {effective_tier})"
            )

        result = {
            "token_id": token_id,
            "requested_action": requested_action,
            "authorized": authorized,
            "effective_authority": {
                "tools": sorted(effective_tools),
                "scopes": sorted(effective_scopes),
                "tier_rank": effective_tier,
            },
            "chain_length": len(chain),
        }

        if authorized:
            result["authorization_path"] = [
                rec.get("parent_token_id") for rec in chain
            ] + [token_id]
        else:
            result["denials"] = denials

        return result

    def detect_unauthorized_executions(self):
        """Finds executions without valid authorization.

        Returns:
            List of unauthorized execution reports.
        """
        identities = _jsonl_read(self.identity_chain.path)
        unauthorized = []

        for identity in identities:
            exec_id = identity.get("exec_id")
            token_id = identity.get("token_id")

            # Check if token has a valid delegation chain
            chain = self.delegation_lineage.get_delegation_chain(token_id)
            if not chain and token_id != "operator-root":
                unauthorized.append({
                    "exec_id": exec_id,
                    "token_id": token_id,
                    "reason": "No delegation chain found",
                })
                continue

            # Verify chain
            if chain:
                valid, reason = self.delegation_lineage.verify_delegation_chain(token_id)
                if not valid:
                    unauthorized.append({
                        "exec_id": exec_id,
                        "token_id": token_id,
                        "reason": f"Invalid delegation chain: {reason}",
                    })

        return unauthorized

    def export_audit_graph(self):
        """Exports the audit graph for external audit.

        Returns:
            The full audit graph dict (also persisted to disk).
        """
        return self.build_audit_graph()


# ---------------------------------------------------------------------------
# CLI
# ---------------------------------------------------------------------------
def _resolve_target_dir(args):
    for idx in range(len(args)):
        if args[idx] == "--dir" and idx + 1 < len(args):
            return args[idx + 1]
    return "."


def cmd_identity(args):
    """python3 trust-identity.py identity <exec_id> [--dir <dir>]"""
    if not args:
        print("Usage: trust-identity.py identity <exec_id> [--dir <dir>]")
        sys.exit(1)

    exec_id = args[0]
    target_dir = _resolve_target_dir(args)

    ic = ExecutionIdentityChain(target_dir)
    identity = ic.get_by_exec_id(exec_id)

    if identity is None:
        print(f"❌ Identity not found: {exec_id}")
        sys.exit(1)

    valid, reason = ic.verify_identity(identity)
    print(f"Execution Identity: {exec_id}")
    print(f"  Token ID:         {identity['token_id']}")
    print(f"  Nonce:            {identity['nonce']}")
    print(f"  Timestamp:        {identity['timestamp']}")
    print(f"  Executor Hash:    {identity['executor_hash'][:16]}...")
    print(f"  Identity Hash:    {identity['identity_hash'][:16]}...")
    print(f"  Verified:         {valid} ({reason})")

    chain = ic.get_identity_chain(exec_id)
    if len(chain) > 1:
        print(f"  Chain Length:     {len(chain)}")
        for i, c in enumerate(chain):
            print(f"    [{i}] {c['exec_id']} (token: {c['token_id']})")


def cmd_lineage(args):
    """python3 trust-identity.py lineage <token_id> [--dir <dir>]"""
    if not args:
        print("Usage: trust-identity.py lineage <token_id> [--dir <dir>]")
        sys.exit(1)

    token_id = args[0]
    target_dir = _resolve_target_dir(args)

    dl = DelegationLineage(target_dir)
    chain = dl.get_delegation_chain(token_id)

    if not chain:
        print(f"❌ No delegation chain found for token: {token_id}")
        sys.exit(1)

    print(f"Delegation Lineage: {token_id}")
    print(f"  Chain Length:     {len(chain)}")
    for i, rec in enumerate(chain):
        print(f"    [{i}] {rec['parent_token_id']} -> {rec['child_token_id']}")
        print(f"        Proof Hash: {rec['narrowing_proof_hash'][:16]}...")

    valid, reason = dl.verify_delegation_chain(token_id)
    print(f"  Chain Valid:      {valid} ({reason})")

    broken = dl.detect_broken_chain(token_id)
    if broken:
        print(f"  ⚠️  Broken Chain: {broken['breaks']}")


def cmd_verify(args):
    """python3 trust-identity.py verify <token_id> [--dir <dir>]"""
    if not args:
        print("Usage: trust-identity.py verify <token_id> [--dir <dir>]")
        sys.exit(1)

    token_id = args[0]
    target_dir = _resolve_target_dir(args)

    tdv = TransitiveDelegationValidator(target_dir)
    cdp = ConfusedDeputyProtection(target_dir)

    # Validate transitive delegation
    valid, reason = tdv.validate_transitive_delegation(token_id)
    print(f"Transitive Delegation: {valid} ({reason})")

    # Check depth
    depth_valid, depth_reason, depth = tdv.validate_delegation_depth(token_id)
    print(f"Delegation Depth:      {depth_valid} ({depth_reason})")

    # Check for loops
    loops = tdv.detect_delegation_loops()
    if loops:
        print(f"⚠️  Delegation Loops:  {len(loops)} detected")
        for loop in loops:
            print(f"    {loop['loop_path']}")
    else:
        print("Delegation Loops:      None detected")

    # Authority scope
    auth = AuthorityInheritance(target_dir)
    scope = auth.get_authority_scope(token_id)
    if scope:
        print(f"Authority Scope:       {json.dumps(scope['authority'], indent=2)}")
    else:
        print("Authority Scope:       Not found in authority graph")


def cmd_deputy(args):
    """python3 trust-identity.py deputy <exec_id> [--dir <dir>]"""
    if not args:
        print("Usage: trust-identity.py deputy <exec_id> [--dir <dir>]")
        sys.exit(1)

    exec_id = args[0]
    target_dir = _resolve_target_dir(args)

    # Look up the execution manifest
    exec_dir = os.path.join(target_dir, ".agents/management/evidence/execution")
    manifest_path = None
    if os.path.exists(exec_dir):
        for f in os.listdir(exec_dir):
            if f.startswith("execution-manifest-") and f.endswith(".json"):
                with open(os.path.join(exec_dir, f), "r") as fh:
                    m = json.load(fh)
                    if m.get("execution_id") == exec_id:
                        manifest_path = os.path.join(exec_dir, f)
                        break

    if manifest_path is None:
        print(f"❌ Execution manifest not found: {exec_id}")
        sys.exit(1)

    with open(manifest_path, "r") as f:
        manifest = json.load(f)

    cdp = ConfusedDeputyProtection(target_dir)

    # Check confused deputy
    report = cdp.check_confused_deputy(manifest)
    print(f"Confused Deputy Check: {exec_id}")
    print(f"  Detected:          {report['confused_deputy_detected']}")

    if report["violations"]:
        for v in report["violations"]:
            print(f"  [{v['severity']}] {v['type']}: {v['message']}")
    else:
        print("  No violations found")

    # Scope drift
    drift = cdp.detect_scope_drift(manifest)
    if drift["drift_detected"]:
        print(f"  ⚠️  Scope Drift:     {len(drift['drifts'])} drift(s) detected")
        for d in drift["drifts"]:
            print(f"    [{d['severity']}] {d['type']}: {d['message']}")
    else:
        print("  Scope Drift:       None detected")


def cmd_graph(args):
    """python3 trust-identity.py graph [--dir <dir>]"""
    target_dir = _resolve_target_dir(args)

    dag = DelegationAuditGraph(target_dir)
    graph = dag.export_audit_graph()

    print("Delegation Audit Graph")
    print(f"  Graph ID:          {graph['graph_id']}")
    print(f"  Built At:          {graph['built_at']}")
    print(f"  Summary:")
    summary = graph["summary"]
    print(f"    Identities:        {summary['total_identities']}")
    print(f"    Delegations:       {summary['total_delegations']}")
    print(f"    Narrowing Proofs:  {summary['total_narrowing_proofs']}")
    print(f"    Ephemeral Tokens:  {summary['total_ephemeral_tokens']}")
    print(f"    Authority Nodes:   {summary['authority_nodes']}")
    print(f"    Authority Edges:   {summary['authority_edges']}")

    # Check for unauthorized executions
    unauthorized = dag.detect_unauthorized_executions()
    if unauthorized:
        print(f"  ⚠️  Unauthorized:      {len(unauthorized)} execution(s)")
        for u in unauthorized:
            print(f"    {u['exec_id']}: {u['reason']}")
    else:
        print("  Unauthorized:        None detected")

    # Check for delegation loops
    tdv = TransitiveDelegationValidator(target_dir)
    loops = tdv.detect_delegation_loops()
    if loops:
        print(f"  ⚠️  Loops:             {len(loops)} detected")
    else:
        print("  Loops:               None detected")

    print(f"  Graph exported to: {dag.path}")


def cmd_ephemeral(args):
    """python3 trust-identity.py ephemeral create|revoke <token_id> [--dir <dir>]"""
    if len(args) < 2:
        print("Usage: trust-identity.py ephemeral create|revoke <token_id> [--dir <dir>]")
        sys.exit(1)

    action = args[0]
    target_dir = _resolve_target_dir(args)
    ephemeral = EphemeralAuthorityToken(target_dir)

    if action == "create":
        # Create: need parent token info from args
        # Parse remaining args for parent token details
        token_id_arg = args[1] if len(args) > 1 else None
        purpose = "ephemeral-delegation"
        ttl = 300

        for idx in range(len(args)):
            if args[idx] == "--purpose" and idx + 1 < len(args):
                purpose = args[idx + 1]
            elif args[idx] == "--ttl" and idx + 1 < len(args):
                ttl = int(args[idx + 1])

        # Create a minimal parent token (in practice this would come from context)
        parent = {
            "token_id": token_id_arg or "operator-root",
            "allowed_tools": ["view_file", "search_web", "write_to_file", "replace_file_content"],
            "allowed_scopes": ["security", "operations", "architecture", "product"],
            "trust_tier": "GOVERNANCE_WRITE",
        }

        token = ephemeral.create_ephemeral_token(parent, purpose, ttl)
        print(f"Ephemeral Token Created")
        print(f"  Token ID:          {token['token_id']}")
        print(f"  Parent:            {token['parent_token_id']}")
        print(f"  Purpose:           {token['purpose']}")
        print(f"  TTL:               {token['ttl_seconds']}s")
        print(f"  Expires At:        {token['expires_at']}")
        print(f"  Signature:         {token['signature'][:16]}...")

    elif action == "revoke":
        token_id = args[1]
        success, reason = ephemeral.revoke_ephemeral(token_id)
        if success:
            print(f"✅ {reason}")
        else:
            print(f"❌ {reason}")
            sys.exit(1)

    elif action == "check":
        token_id = args[1]
        valid, reason = ephemeral.is_ephemeral_valid(token_id)
        print(f"Ephemeral Token: {token_id}")
        print(f"  Valid: {valid} ({reason})")

    else:
        print(f"❌ Unknown ephemeral action: {action}")
        sys.exit(1)


def main():
    if len(sys.argv) < 2:
        print("Usage:")
        print("  trust-identity.py identity <exec_id> [--dir <dir>]")
        print("  trust-identity.py lineage <token_id> [--dir <dir>]")
        print("  trust-identity.py verify <token_id> [--dir <dir>]")
        print("  trust-identity.py deputy <exec_id> [--dir <dir>]")
        print("  trust-identity.py graph [--dir <dir>]")
        print("  trust-identity.py ephemeral create|revoke|check <token_id> [--dir <dir>]")
        sys.exit(1)

    subcmd = sys.argv[1]
    args = sys.argv[2:]

    if subcmd == "identity":
        cmd_identity(args)
    elif subcmd == "lineage":
        cmd_lineage(args)
    elif subcmd == "verify":
        cmd_verify(args)
    elif subcmd == "deputy":
        cmd_deputy(args)
    elif subcmd == "graph":
        cmd_graph(args)
    elif subcmd == "ephemeral":
        cmd_ephemeral(args)
    else:
        print(f"❌ Unknown command: {subcmd}")
        sys.exit(1)


if __name__ == "__main__":
    main()
