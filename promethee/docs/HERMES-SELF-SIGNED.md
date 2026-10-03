# Hermès temporary self-signed releases

Hermès releases are temporarily Authenticode-signed with a self-signed development certificate while the project waits for SignPath Foundation approval.

The release workflow publishes the public certificate as `Hermes-Development-Signing.cer`.

## Important

A self-signed certificate does **not** make Windows or Smart App Control trust Hermès automatically.

For internal testing only, a tester may explicitly install the published `.cer` certificate into the trusted certificate stores on a machine they control. Do not ask users to disable Smart App Control or Microsoft Defender.

Each CI release currently creates a fresh temporary development certificate. Trust therefore applies only to artifacts signed by that specific release certificate.

Once SignPath Foundation signing is available, this temporary mechanism should be removed.
