# Lot N — Hygiène du dépôt et audit final

## Périmètre

Le Lot N ferme le chantier A → N côté dépôt. Il ne supprime pas arbitrairement des fichiers historiques phpVMS : un nom comme `bin/`, `mail/` ou `storage/logs/.gitignore` peut être légitime dans l'arbre applicatif. Le nettoyage cible les artifacts générés, exports d'hébergement, bases/backups et secrets.

## Fichiers interdits

La CI refuse les artifacts .NET `bin/obj`, les exports d'hébergement connus, `Zone.Identifier`, bases locales, backups et désormais les formats de certificats/clés :

- `.pfx`, `.p12`, `.pem`, `.key`, `.snk` ;
- `.sql`, `.sqlite*`, `.bak`, `.backup`.

Le `.gitignore` couvre également TestResults/TRX et les sorties de coverage.

## Secrets

La barrière CI cherche les signatures évidentes de clés privées et les familles de tokens GitHub dans les fichiers suivis.

Un audit de recherche du dépôt a été effectué pour les motifs suivants : mot de passe assigné, API key, private key, Bearer et client secret. Aucun résultat évident n'a été trouvé au moment du Lot N.

Cette vérification n'est pas une preuve cryptographique d'absence de secret et ne remplace pas GitHub Secret Scanning lorsqu'il est disponible.

## Fichiers conservés volontairement

- `prometheus/.env.example` : exemple sans secret ;
- `prometheus/bin/*` : scripts applicatifs phpVMS ;
- vues Laravel `vendor/mail` : templates de mail versionnés ;
- `prometheus/storage/logs/.gitignore` : garde le dossier sans versionner les logs.

Ils ne doivent pas être supprimés uniquement parce que leur chemin ressemble à un artifact.

## Politique de branches

Les branches de lots peuvent être supprimées après merge de leurs PR. La suppression distante n'est pas réalisée dans ce lot tant qu'une PR correspondante est encore ouverte ou utile à la revue.

## Validation finale après merges

Après fusion A → N :

1. repartir d'un clone propre de `master` ;
2. exécuter la workflow Quality ;
3. exécuter `dotnet test acars.tests/Promethee.Acars.Tests.csproj -c Release -f net8.0` ;
4. builder Hermès Windows Release ;
5. valider Composer et les tests Laravel/phpVMS dans l'environnement DB de test ;
6. réaliser le vol E2E réel : connexion → opération → OFP → PIREP → READY → Hermès → OUT/OFF/ON/IN → final PIREP → post-flight → continuité flotte ;
7. tester coupure réseau/reprise et fermeture forcée Hermès ;
8. tester les simulateurs réellement annoncés supportés.

La validation finale doit se faire sur le `master` consolidé, pas séparément sur les branches de lots.
