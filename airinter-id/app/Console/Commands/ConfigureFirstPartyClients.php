<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;

class ConfigureFirstPartyClients extends Command
{
    protected $signature = 'airinter-id:configure-clients
        {--show-secrets : Display newly-created confidential client secrets}';

    protected $description = 'Create or validate Air Inter first-party OAuth clients.';

    public function handle(ClientRepository $clients): int
    {
        $configured = (array) config('airinter-id.clients', []);

        if (!$configured) {
            $this->error('No Air Inter OAuth clients are configured.');
            return self::FAILURE;
        }

        $rows = [];
        $failed = false;

        foreach ($configured as $key => $definition) {
            $name = (string) ($definition['name'] ?? $key);
            $redirectUri = trim((string) ($definition['redirect_uri'] ?? ''));
            $confidential = (bool) ($definition['confidential'] ?? true);

            if ($redirectUri === '') {
                $this->error("Missing redirect URI for {$name}.");
                $failed = true;
                continue;
            }

            /** @var Client|null $client */
            $client = Passport::client()
                ->newQuery()
                ->where('name', $name)
                ->where('revoked', false)
                ->first();

            $created = false;
            if (!$client) {
                $client = $clients->createAuthorizationCodeGrantClient(
                    $name,
                    [$redirectUri],
                    $confidential,
                );
                $created = true;
            } else {
                if ($client->confidential() !== $confidential) {
                    $this->error(
                        "{$name}: existing client confidentiality does not match configuration. ".
                        'Revoke/recreate it explicitly instead of mutating the security model in place.'
                    );
                    $failed = true;
                    continue;
                }

                if ($client->redirect_uris !== [$redirectUri]) {
                    $clients->update($client, $name, [$redirectUri]);
                    $client->refresh();
                }
            }

            $secret = null;
            if ($created && $confidential && $this->option('show-secrets')) {
                $secret = $client->plainSecret;
            }

            $rows[] = [
                $name,
                $client->getKey(),
                $confidential ? 'confidential' : 'public/PKCE',
                $redirectUri,
                $created ? 'created' : 'ready',
                $secret ?: ($confidential ? 'hidden' : 'n/a'),
            ];
        }

        $this->table(
            ['Client', 'Client ID', 'Type', 'Redirect URI', 'State', 'Secret'],
            $rows,
        );

        if ($this->option('show-secrets')) {
            $this->warn('Store any displayed secret now. Passport will not show it again.');
        } else {
            $this->line('Use --show-secrets only during first production provisioning if you need confidential client secrets.');
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
