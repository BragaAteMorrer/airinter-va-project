# Code signing policy

Hermès ACARS releases are built from this public GitHub repository using GitHub Actions.

For releases using the free open-source code-signing service, code signing is provided by SignPath.io and the SignPath Foundation.

## Build and signing process

- Release binaries must be produced from the public source in this repository.
- Build jobs that precede signing must run on GitHub-hosted runners.
- The unsigned build artifact is submitted to SignPath for signing.
- Signed artifacts are returned to the release workflow and published with their checksums.
- The signing process must not use a private build that differs from the public source associated with the release.

## Roles and approval

For SignPath Foundation signing:

- SignPath.io provides the signing platform.
- The SignPath Foundation provides the free open-source signing service.
- SignPath Foundation maintainers/reviewers/approvers may review project eligibility, release provenance and signing requests before approval.
- Air Inter Virtual Airlines project maintainers remain responsible for the source code, release contents and versioning of Hermès.

## Privacy

The code-signing process only uses repository, build, release and signing metadata needed to verify provenance and sign release artifacts.

The project's privacy policy is available in [PRIVACY.md](./PRIVACY.md).

## Security

Private signing keys are not stored in this repository or in GitHub Actions secrets. Signing keys are managed by the signing provider.

Security issues should be reported according to [SECURITY.md](./SECURITY.md).
