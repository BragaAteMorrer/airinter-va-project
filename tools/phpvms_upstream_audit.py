#!/usr/bin/env python3
"""Audit an Air Inter phpVMS tree against a future upstream release.

This script deliberately does not update files. It downloads the pinned release
and the requested target release, detects which upstream files changed, then
checks whether Air Inter has locally modified those same files.

Typical use:
  python tools/phpvms_upstream_audit.py --latest
  python tools/phpvms_upstream_audit.py --target 7.0.11
  python tools/phpvms_upstream_audit.py --check-manifest

Set GITHUB_TOKEN to avoid anonymous GitHub API rate limits.
"""

from __future__ import annotations

import argparse
import hashlib
import json
import os
from pathlib import Path
import re
import shutil
import sys
import tempfile
import urllib.request
import zipfile

REPO_ROOT = Path(__file__).resolve().parents[1]
MANIFEST_PATH = REPO_ROOT / "prometheus" / ".phpvms-upstream.json"
TEXT_SUFFIXES = {
    ".php", ".json", ".yml", ".yaml", ".xml", ".txt", ".md", ".blade.php",
    ".js", ".css", ".scss", ".html", ".htm", ".toml", ".lock", ".env",
}


def load_manifest() -> dict:
    data = json.loads(MANIFEST_PATH.read_text(encoding="utf-8"))
    required = {"repository", "version", "commit", "release_asset", "release_sha256", "local_root"}
    missing = sorted(required - data.keys())
    if missing:
        raise RuntimeError("Missing manifest keys: " + ", ".join(missing))
    return data


def request_json(url: str) -> dict:
    headers = {
        "Accept": "application/vnd.github+json",
        "User-Agent": "airinter-phpvms-upstream-audit",
    }
    token = os.getenv("GITHUB_TOKEN")
    if token:
        headers["Authorization"] = f"Bearer {token}"
    req = urllib.request.Request(url, headers=headers)
    with urllib.request.urlopen(req, timeout=45) as response:
        return json.load(response)


def release_metadata(repository: str, version: str | None = None) -> dict:
    if version:
        url = f"https://api.github.com/repos/{repository}/releases/tags/{version}"
    else:
        url = f"https://api.github.com/repos/{repository}/releases/latest"
    return request_json(url)


def release_asset(release: dict, version: str) -> tuple[str, str | None]:
    expected = f"phpvms-{version}.zip"
    for asset in release.get("assets", []):
        if asset.get("name") == expected:
            digest = asset.get("digest")
            sha256 = digest.split(":", 1)[1] if isinstance(digest, str) and digest.startswith("sha256:") else None
            return asset["browser_download_url"], sha256
    raise RuntimeError(f"Release asset {expected} was not found")


def download(url: str, destination: Path) -> None:
    req = urllib.request.Request(url, headers={"User-Agent": "airinter-phpvms-upstream-audit"})
    with urllib.request.urlopen(req, timeout=120) as response, destination.open("wb") as output:
        shutil.copyfileobj(response, output)


def sha256(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as stream:
        for chunk in iter(lambda: stream.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def extract_release(archive: Path, destination: Path) -> Path:
    with zipfile.ZipFile(archive) as zf:
        zf.extractall(destination)

    if (destination / "composer.json").exists() and (destination / "app").is_dir():
        return destination

    candidates = [p for p in destination.iterdir() if p.is_dir()]
    for candidate in candidates:
        if (candidate / "composer.json").exists() and (candidate / "app").is_dir():
            return candidate

    raise RuntimeError(f"Cannot locate phpVMS root inside {archive.name}")


def ignored(path: str, manifest: dict) -> bool:
    prefixes = tuple(manifest.get("ignored_prefixes", [])) + tuple(manifest.get("local_owned_prefixes", []))
    return path.startswith(prefixes)


def inventory(root: Path, manifest: dict) -> dict[str, Path]:
    files: dict[str, Path] = {}
    for item in root.rglob("*"):
        if not item.is_file():
            continue
        rel = item.relative_to(root).as_posix()
        if ignored(rel, manifest):
            continue
        files[rel] = item
    return files


def normalized_bytes(path: Path) -> bytes:
    raw = path.read_bytes()
    name = path.name.lower()
    suffix = path.suffix.lower()
    is_text = suffix in TEXT_SUFFIXES or name in {"composer.json", "composer.lock", "artisan"}
    if not is_text:
        return raw
    try:
        text = raw.decode("utf-8")
    except UnicodeDecodeError:
        return raw
    return text.replace("\r\n", "\n").encode("utf-8")


def same_file(a: Path, b: Path) -> bool:
    return normalized_bytes(a) == normalized_bytes(b)


def version_tuple(value: str) -> tuple[int, ...]:
    match = re.fullmatch(r"v?(\d+)\.(\d+)\.(\d+)", value.strip())
    if not match:
        raise ValueError(f"Unsupported version: {value}")
    return tuple(int(part) for part in match.groups())


def verify_manifest(manifest: dict) -> list[str]:
    problems: list[str] = []
    version = manifest["version"]
    major, minor, patch = version_tuple(version)
    version_file = REPO_ROOT / manifest["local_root"] / "config" / "version.yml"
    text = version_file.read_text(encoding="utf-8")
    for label, expected in (("major", major), ("minor", minor), ("patch", patch)):
        if not re.search(rf"^\s*{label}:\s*{expected}\s*$", text, re.MULTILINE):
            problems.append(f"config/version.yml does not declare {label}={expected}")
    if not re.fullmatch(r"[0-9a-f]{40}", manifest["commit"]):
        problems.append("manifest commit is not a full 40-character SHA")
    if not re.fullmatch(r"[0-9a-f]{64}", manifest["release_sha256"]):
        problems.append("manifest release_sha256 is not a SHA-256 digest")
    return problems


def materialize_release(repository: str, version: str, workdir: Path, expected_sha: str | None = None) -> Path:
    release = release_metadata(repository, version)
    url, api_sha = release_asset(release, version)
    archive = workdir / f"phpvms-{version}.zip"
    download(url, archive)
    actual = sha256(archive)
    expected = expected_sha or api_sha
    if expected and actual.lower() != expected.lower():
        raise RuntimeError(f"SHA256 mismatch for {archive.name}: expected {expected}, got {actual}")
    target = workdir / f"phpvms-{version}"
    target.mkdir()
    return extract_release(archive, target)


def audit(manifest: dict, target_version: str) -> tuple[list[str], list[str], list[str]]:
    pinned_version = manifest["version"]
    repository = manifest["repository"]
    local_root = REPO_ROOT / manifest["local_root"]

    with tempfile.TemporaryDirectory(prefix="phpvms-audit-") as tmp:
        workdir = Path(tmp)
        pinned_root = materialize_release(
            repository,
            pinned_version,
            workdir,
            expected_sha=manifest["release_sha256"],
        )
        target_root = pinned_root if target_version == pinned_version else materialize_release(
            repository,
            target_version,
            workdir,
        )

        pinned = inventory(pinned_root, manifest)
        target = inventory(target_root, manifest)
        changed = sorted(
            path for path in set(pinned) | set(target)
            if path not in pinned or path not in target or not same_file(pinned[path], target[path])
        )

        safe: list[str] = []
        collisions: list[str] = []
        for path in changed:
            local = local_root / path
            baseline = pinned.get(path)
            if baseline is None:
                # Upstream is adding a path. A local file at that path is a collision.
                (collisions if local.exists() else safe).append(path)
                continue
            if not local.exists() or not same_file(local, baseline):
                collisions.append(path)
            else:
                safe.append(path)

        # A core extension can collide with a newly introduced upstream path even
        # when its prefix is otherwise excluded from ordinary source comparisons.
        for path in manifest.get("core_extensions", []):
            if path in target and path not in pinned and path not in collisions:
                collisions.append(path)

        return changed, sorted(safe), sorted(collisions)


def print_report(pinned: str, target: str, changed: list[str], safe: list[str], collisions: list[str]) -> None:
    print(f"phpVMS upstream audit: {pinned} -> {target}")
    print(f"Upstream changed source files: {len(changed)}")
    print(f"Safe to take upstream directly: {len(safe)}")
    print(f"Needs manual merge: {len(collisions)}")

    if collisions:
        print("\nMANUAL MERGE REQUIRED")
        for path in collisions:
            print(f"  ! {path}")

    if safe:
        print("\nSAFE UPSTREAM CHANGES")
        for path in safe:
            print(f"  + {path}")


def main() -> int:
    parser = argparse.ArgumentParser()
    target = parser.add_mutually_exclusive_group()
    target.add_argument("--target", help="Target stable phpVMS version, for example 7.0.11")
    target.add_argument("--latest", action="store_true", help="Audit against the latest stable GitHub release")
    parser.add_argument("--check-manifest", action="store_true", help="Validate the pinned manifest and local version only")
    parser.add_argument("--fail-if-newer", action="store_true", help="Exit non-zero when target differs from the pinned version")
    args = parser.parse_args()

    manifest = load_manifest()
    problems = verify_manifest(manifest)
    if problems:
        for problem in problems:
            print(f"ERROR: {problem}", file=sys.stderr)
        return 1

    if args.check_manifest:
        print(f"phpVMS manifest OK: {manifest['version']} @ {manifest['commit'][:7]}")
        return 0

    if args.latest:
        release = release_metadata(manifest["repository"])
        target_version = release["tag_name"].lstrip("v")
    else:
        target_version = (args.target or manifest["version"]).lstrip("v")

    if version_tuple(target_version) < version_tuple(manifest["version"]):
        print(f"Target {target_version} is older than pinned {manifest['version']}", file=sys.stderr)
        return 1

    changed, safe, collisions = audit(manifest, target_version)
    print_report(manifest["version"], target_version, changed, safe, collisions)

    if collisions:
        return 3
    if args.fail_if_newer and target_version != manifest["version"]:
        return 2
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
