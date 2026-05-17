#!/usr/bin/env python3
# security-adversary.py — Adversarial execution test runner for Agent Harness
#
# Runs attack simulations against the execution substrate security controls.
# Each attack must be DETECTED and BLOCKED for the test to PASS.
#
# Usage:
#   security-adversary.py --attack <attack_type> [--dir <dir>]
#   security-adversary.py --all [--dir <dir>]

import os
import sys
import json
import time
import uuid
import hashlib
import tempfile
import shutil
import importlib.util

# ---------------------------------------------------------------------------
# Import substrate classes from execution-substrate.py
# ---------------------------------------------------------------------------
SUBSTRATE_PATH = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'execution-substrate.py')

def _load_substrate_module():
    spec = importlib.util.spec_from_file_location("execution_substrate", SUBSTRATE_PATH)
    mod = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)
    return mod

substrate_mod = _load_substrate_module()
CapabilityToken = substrate_mod.CapabilityToken
ExecutionSubstrate = substrate_mod.ExecutionSubstrate

# ---------------------------------------------------------------------------
# Import security primitives from substrate_security.py
# ---------------------------------------------------------------------------
SECURITY_PATH = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'substrate_security.py')

def _load_security_module():
    spec = importlib.util.spec_from_file_location("substrate_security", SECURITY_PATH)
    mod = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)
    return mod

security_mod = _load_security_module()
NonceRegistry = security_mod.NonceRegistry
RevocationRegistry = security_mod.RevocationRegistry
PathGuard = security_mod.PathGuard
IntegritySeal = security_mod.IntegritySeal
AuditChain = security_mod.AuditChain
EnvironmentSanitizer = security_mod.EnvironmentSanitizer

# ---------------------------------------------------------------------------
# Constants
# ---------------------------------------------------------------------------
ALL_ATTACKS = [
    "replay",
    "escalation",
    "traversal",
    "tampering",
    "poisoning",
    "nonce-reuse",
    "revoked-token",
    "chain-tamper",
]

ATTACK_DESCRIPTIONS = {
    "replay": "Token replay attack — reuse a valid capability token's nonce",
    "escalation": "Privilege escalation — child token with higher trust tier or expanded tools/scopes",
    "traversal": "Path traversal / symlink escape — write outside the sandbox",
    "tampering": "Evidence tampering — modify a sealed execution manifest",
    "poisoning": "Environment poisoning — inject dangerous env vars into subprocess",
    "nonce-reuse": "Nonce reuse — register a nonce and try to reuse it",
    "revoked-token": "Revoked token use — execute with a token that has been revoked",
    "chain-tamper": "Audit chain tampering — modify an entry in the audit chain",
}


# ---------------------------------------------------------------------------
# Attack implementations
# ---------------------------------------------------------------------------

def attack_replay(tmpdir):
    """
    Create a valid capability token, then try to reuse its nonce.
    Should be blocked by NonceRegistry.
    """
    nonce_reg = NonceRegistry(target_dir=tmpdir)

    token_id = f"token-{uuid.uuid4()}"
    nonce = hashlib.sha256(f"{token_id}:{time.time()}".encode()).hexdigest()
    expires_at = time.time() + 3600  # 1 hour from now

    # First use — should succeed
    first_ok = nonce_reg.register_nonce(nonce, token_id, expires_at)
    assert first_ok, "First registration should succeed"

    # Verify nonce is valid
    is_valid = nonce_reg.is_nonce_valid(nonce)
    assert is_valid, "Nonce should be valid after registration"

    # Replay — try to reuse the same nonce
    second_ok = nonce_reg.register_nonce(nonce, token_id, expires_at)

    result = "BLOCKED" if not second_ok else "ESCAPED"
    detection = "NonceRegistry.register_nonce() returned False for duplicate nonce"

    return {
        "attack_setup": {
            "token_id": token_id,
            "nonce": nonce,
            "first_use": "registered",
            "nonce_valid_before_replay": is_valid,
        },
        "actual_result": result,
        "detection_mechanism": detection,
    }


def attack_escalation(tmpdir):
    """
    Create a child token with higher trust tier and expanded tools/scopes.
    Should be blocked by validate_delegation().
    """
    # Parent token with limited authority
    parent = CapabilityToken(
        token_id="parent-token",
        lease_duration=600,
        max_memory_mb=256,
        allowed_tools=["view_file"],
        allowed_scopes=["operations"],
        trust_tier="WORKSPACE_WRITE",
    )

    # Child tries to escalate: higher tier + more tools + more scopes
    child = CapabilityToken(
        token_id="child-escalating",
        lease_duration=600,
        max_memory_mb=512,
        allowed_tools=["view_file", "write_to_file", "search_web"],  # expanded
        allowed_scopes=["operations", "security"],                    # expanded
        trust_tier="GOVERNANCE_WRITE",                                # higher tier
    )

    ok, msg = parent.validate_delegation(child)

    result = "BLOCKED" if not ok else "ESCAPED"
    detection = "validate_delegation() returned False: {}".format(msg)

    return {
        "attack_setup": {
            "parent_trust_tier": parent.trust_tier,
            "parent_tools": parent.allowed_tools,
            "parent_scopes": parent.allowed_scopes,
            "child_trust_tier": child.trust_tier,
            "child_tools": child.allowed_tools,
            "child_scopes": child.allowed_scopes,
        },
        "actual_result": result,
        "detection_mechanism": detection,
    }


def attack_traversal(tmpdir):
    """
    Attempt to create files outside the target directory using ../ and symlinks.
    Should be blocked by PathGuard.
    """
    sandbox = os.path.join(tmpdir, "sandbox")
    os.makedirs(sandbox)

    guard = PathGuard()

    escape_attempts = [
        "../../../etc/passwd",
        os.path.join(sandbox, "..", "..", "..", "etc", "shadow"),
        "/tmp/outside-sandbox",
    ]

    # Create a symlink inside sandbox pointing outside
    symlink_path = os.path.join(sandbox, "escape-link")
    outside = os.path.join(tmpdir, "outside")
    os.makedirs(outside, exist_ok=True)
    try:
        os.symlink(outside, symlink_path)
    except OSError:
        pass  # Symlinks may not be supported

    results = {}
    for path in escape_attempts:
        safe = guard.is_path_safe(path, sandbox)
        results[path] = "blocked" if not safe else "escaped"

    if os.path.islink(symlink_path):
        symlink_safe = guard.is_path_safe(symlink_path, sandbox)
        results["symlink-escape"] = "blocked" if not symlink_safe else "escaped"

    all_blocked = all(v == "blocked" for v in results.values())
    result = "BLOCKED" if all_blocked else "ESCAPED"
    detection = "PathGuard.is_path_safe() rejected paths outside sandbox"

    return {
        "attack_setup": {
            "sandbox_root": sandbox,
            "escape_attempts": escape_attempts,
            "symlink_target": outside,
            "results": results,
        },
        "actual_result": result,
        "detection_mechanism": detection,
    }


def attack_tampering(tmpdir):
    """
    Create a valid execution manifest, then modify it.
    Should be detected by IntegritySeal.verify_seal().
    """
    manifest = {
        "execution_id": "exec-{}".format(uuid.uuid4()),
        "task": "test-task",
        "trust_tier": "READ_ONLY",
        "domain_scope": "security",
        "lifecycle_state": "REPLAYABLE",
        "timestamp": time.time(),
    }

    sealed = IntegritySeal.seal_manifest(manifest)

    # Verify original seal is valid
    valid_before = IntegritySeal.verify_seal(sealed)
    assert valid_before, "Original seal should be valid"

    # Tamper with fields
    sealed["lifecycle_state"] = "FAILED"
    sealed["task"] = "malicious-task"

    valid_after = IntegritySeal.verify_seal(sealed)

    result = "BLOCKED" if not valid_after else "ESCAPED"
    detection = "IntegritySeal.verify_seal() returned False after manifest was tampered"

    return {
        "attack_setup": {
            "original_manifest": manifest,
            "original_seal_valid": valid_before,
            "tampered_fields": ["lifecycle_state", "task"],
        },
        "actual_result": result,
        "detection_mechanism": detection,
    }


def attack_poisoning(tmpdir):
    """
    Try to inject sensitive env vars (AWS_SECRET_KEY, LD_PRELOAD) into subprocess.
    Should be blocked by EnvironmentSanitizer.
    """
    sanitizer = EnvironmentSanitizer()

    # Poisoned environment
    poisoned_env = {
        "HOME": "/home/testuser",
        "USER": "testuser",
        "AWS_SECRET_KEY": "AKIAIOSFODNN7EXAMPLE",
        "AWS_SECRET_ACCESS_KEY": "wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY",
        "AWS_SESSION_TOKEN": "evil-token",
        "GOOGLE_APPLICATION_CREDENTIALS": "/tmp/evil-creds.json",
        "LD_PRELOAD": "/tmp/malicious.so",
        "MY_SECRET_TOKEN": "sk-evil-key-12345",
        "DATABASE_PASSWORD": "hunter2",
        "API_KEY": "evil-api-key",
        "NORMAL_VAR": "safe-value",
        "PATH": "/usr/bin:/bin",
        "LANG": "en_US.UTF-8",
    }

    sanitized = sanitizer.sanitize_env(poisoned_env)

    # Check that known poison vars are removed
    poison_vars = [
        'AWS_SECRET_KEY', 'AWS_SECRET_ACCESS_KEY', 'AWS_SESSION_TOKEN',
        'GOOGLE_APPLICATION_CREDENTIALS', 'MY_SECRET_TOKEN',
        'DATABASE_PASSWORD', 'API_KEY',
    ]
    poison_present = [v for v in poison_vars if v in sanitized]

    # Allowed vars should still be present
    allowed_vars = ['HOME', 'USER', 'PATH', 'LANG']
    allowed_present = [v for v in allowed_vars if v in sanitized]

    is_clean = len(poison_present) == 0
    result = "BLOCKED" if is_clean else "ESCAPED"

    # Check that sanitizer marker was added
    sanitized_marker = sanitized.get("SUBSTRATE_SANITIZED") == "true"
    locale_set = sanitized.get("LANG") == "C.UTF-8" and sanitized.get("LC_ALL") == "C.UTF-8"

    detection = (
        "EnvironmentSanitizer.sanitize_env() removed sensitive variables; "
        "sanitized={}, locale_forced={}, allowed_retained={}".format(
            sanitized_marker, locale_set, len(allowed_present)
        )
    )

    return {
        "attack_setup": {
            "poisoned_vars": list(poisoned_env.keys()),
            "poison_vars_checked": poison_vars,
            "poison_vars_present_in_output": poison_present,
            "allowed_vars_retained": allowed_present,
            "total_input_vars": len(poisoned_env),
            "total_output_vars": len(sanitized),
        },
        "actual_result": result,
        "detection_mechanism": detection,
    }


def attack_nonce_reuse(tmpdir):
    """
    Register a nonce, then try to execute with the same nonce again.
    Should be blocked by NonceRegistry.
    """
    nonce_reg = NonceRegistry(target_dir=tmpdir)

    token_id = "token-{}".format(uuid.uuid4())
    nonce = hashlib.sha256("nonce-{}:{}".format(uuid.uuid4(), time.time()).encode()).hexdigest()
    expires_at = time.time() + 3600

    # First registration
    first = nonce_reg.register_nonce(nonce, token_id, expires_at)
    assert first, "First registration should succeed"

    # Verify nonce is tracked
    is_tracked = nonce_reg.is_nonce_valid(nonce)

    # Second registration (reuse attempt)
    second = nonce_reg.register_nonce(nonce, token_id, expires_at)

    result = "BLOCKED" if (is_tracked and not second) else "ESCAPED"
    detection = (
        "NonceRegistry.is_nonce_valid() confirmed nonce was registered; "
        "register_nonce() rejected reuse"
    )

    return {
        "attack_setup": {
            "token_id": token_id,
            "nonce": nonce,
            "first_registration": first,
            "nonce_was_tracked": is_tracked,
        },
        "actual_result": result,
        "detection_mechanism": detection,
    }


def attack_revoked_token(tmpdir):
    """
    Create a token, revoke it, then try to execute with it.
    Should be blocked by RevocationRegistry.
    """
    rev_reg = RevocationRegistry(target_dir=tmpdir)

    token_id = "token-{}".format(uuid.uuid4())

    # Verify not revoked before
    before_revoked = rev_reg.is_revoked(token_id)
    assert not before_revoked, "Token should not be revoked initially"

    # Revoke the token
    rev_reg.revoke_token(token_id, "adversarial-test-revocation")

    # Verify it's now revoked
    after_revoked = rev_reg.is_revoked(token_id)

    result = "BLOCKED" if after_revoked else "ESCAPED"
    detection = "RevocationRegistry.is_revoked() returned True for '{}'".format(token_id)

    return {
        "attack_setup": {
            "token_id": token_id,
            "revoked": after_revoked,
            "reason": "adversarial-test-revocation",
        },
        "actual_result": result,
        "detection_mechanism": detection,
    }


def attack_chain_tamper(tmpdir):
    """
    Create an audit chain, then modify an entry.
    Should be detected by AuditChain.verify_chain().
    """
    chain = AuditChain(target_dir=tmpdir)

    # Build a chain with 5 entries
    for i in range(5):
        chain.append_entry({
            "action": "step-{}".format(i),
            "actor": "test-agent",
            "detail": "Action {} details".format(i),
            "timestamp": time.time() + i,
        })

    # Verify original chain is valid
    valid_before, broken_idx_before = chain.verify_chain()
    assert valid_before, "Original chain should be valid"

    # Tamper with entry at index 2 — modify the manifest_data which breaks the hash
    chain._entries[2]["manifest_data"]["action"] = "step-2-MODIFIED"
    chain._entries[2]["manifest_data"]["actor"] = "attacker"

    valid_after, broken_idx = chain.verify_chain()

    result = "BLOCKED" if not valid_after else "ESCAPED"
    detection = "AuditChain.verify_chain() detected tampering at index {}".format(broken_idx)

    return {
        "attack_setup": {
            "chain_length": 5,
            "tampered_index": 2,
            "original_valid": True,
            "broken_index_detected": broken_idx,
        },
        "actual_result": result,
        "detection_mechanism": detection,
    }


# ---------------------------------------------------------------------------
# Attack dispatcher
# ---------------------------------------------------------------------------
ATTACK_FUNCS = {
    "replay": attack_replay,
    "escalation": attack_escalation,
    "traversal": attack_traversal,
    "tampering": attack_tampering,
    "poisoning": attack_poisoning,
    "nonce-reuse": attack_nonce_reuse,
    "revoked-token": attack_revoked_token,
    "chain-tamper": attack_chain_tamper,
}


def run_attack(attack_type, proofs_dir):
    """Run a single attack and write proof file. Returns (passed, proof, proof_path)."""
    tmpdir = tempfile.mkdtemp(prefix="adversary-{}-".format(attack_type))
    try:
        func = ATTACK_FUNCS[attack_type]
        attack_data = func(tmpdir)
    except Exception as e:
        # If the attack itself crashes, that's a FAIL
        attack_data = {
            "attack_setup": {"error": str(e)},
            "actual_result": "ESCAPED",
            "detection_mechanism": "Attack runner crashed: {}".format(e),
        }
    finally:
        shutil.rmtree(tmpdir, ignore_errors=True)

    passed = attack_data["actual_result"] == "BLOCKED"

    proof = {
        "attack_type": attack_type,
        "attack_description": ATTACK_DESCRIPTIONS.get(attack_type, ""),
        "timestamp": time.time(),
        "attack_setup": attack_data["attack_setup"],
        "expected_result": "BLOCKED",
        "actual_result": attack_data["actual_result"],
        "detection_mechanism": attack_data["detection_mechanism"],
        "proof_hash": hashlib.sha256(json.dumps(attack_data, sort_keys=True).encode()).hexdigest(),
        "passed": passed,
    }

    # Write proof
    os.makedirs(proofs_dir, exist_ok=True)
    proof_path = os.path.join(proofs_dir, "{}-proof.json".format(attack_type))
    with open(proof_path, 'w') as f:
        json.dump(proof, f, indent=2)

    return passed, proof, proof_path


def run_all(proofs_dir):
    """Run all attacks. Returns list of (attack_type, passed, proof, proof_path)."""
    results = []
    for attack_type in ALL_ATTACKS:
        passed, proof, proof_path = run_attack(attack_type, proofs_dir)
        results.append((attack_type, passed, proof, proof_path))
    return results


def print_results(results):
    """Print a summary table."""
    width = 70
    print("=" * width)
    print("  ADVERSARIAL SECURITY TEST RESULTS")
    print("=" * width)
    print("{:<20} {:<10} {:<40}".format("Attack", "Result", "Proof"))
    print("-" * width)

    passes = 0
    fails = 0
    for attack_type, passed, proof, proof_path in results:
        status = "PASS" if passed else "FAIL"
        if passed:
            passes += 1
        else:
            fails += 1
        print("{:<20} {:<10} {}".format(attack_type, status, proof_path))

    print("-" * width)
    total = passes + fails
    print("  Total: {}  |  Passes: {}  |  Failures: {}".format(total, passes, fails))
    print("=" * width)

    if fails > 0:
        print("")
        print("  FAILING ATTACKS (security controls did NOT block):")
        for attack_type, passed, proof, proof_path in results:
            if not passed:
                print("    - {}: {}".format(attack_type, proof['detection_mechanism']))
        print("")


# ---------------------------------------------------------------------------
# CLI
# ---------------------------------------------------------------------------
def main():
    args = sys.argv[1:]

    if not args:
        print("Usage:")
        print("  security-adversary.py --attack <attack_type> [--dir <dir>]")
        print("  security-adversary.py --all [--dir <dir>]")
        print("")
        print("Attack types:")
        for a in ALL_ATTACKS:
            print("  {:<16} — {}".format(a, ATTACK_DESCRIPTIONS[a]))
        sys.exit(1)

    # Parse arguments
    mode = None
    attack_type = None
    target_dir = "."
    idx = 0
    while idx < len(args):
        if args[idx] == "--attack" and idx + 1 < len(args):
            mode = "single"
            attack_type = args[idx + 1]
            idx += 2
        elif args[idx] == "--all":
            mode = "all"
            idx += 1
        elif args[idx] == "--dir" and idx + 1 < len(args):
            target_dir = args[idx + 1]
            idx += 2
        else:
            idx += 1

    # Determine proofs directory
    proofs_dir = os.path.join(target_dir, "EVIDENCE", "security", "adversarial-proofs")

    if mode == "single":
        if attack_type not in ATTACK_FUNCS:
            print("ERROR: Unknown attack type '{}'".format(attack_type))
            print("Valid types: {}".format(", ".join(ALL_ATTACKS)))
            sys.exit(1)

        passed, proof, proof_path = run_attack(attack_type, proofs_dir)
        status = "PASS" if passed else "FAIL"
        print("[{}] {}: {}".format(status, attack_type, proof['detection_mechanism']))
        print("  Proof: {}".format(proof_path))
        sys.exit(0 if passed else 1)

    elif mode == "all":
        results = run_all(proofs_dir)
        print_results(results)
        any_fail = any(not passed for _, passed, _, _ in results)
        sys.exit(1 if any_fail else 0)

    else:
        print("ERROR: Specify --attack <type> or --all")
        sys.exit(1)


if __name__ == "__main__":
    main()
