# IVAO FPL — intégration Hermès / Prométhée

## Parcours disponible

Après import d'un OFP SimBrief (Legacy ou API compagnie), Prométhée fournit le plan ICAO via `network_prefiles.icao_flightplan`, et l'URL `https://fpl.ivao.aero/flight-plans/create`.
Hermès valide la présence d'un FPL ICAO, le copie si possible et ouvre le formulaire officiel dans le navigateur système. Le pilote importe le texte ICAO dans le portail IVAO puis confirme le dépôt. **L'ouverture d'IVAO ne signifie pas qu'un FPL a été déposé.**

Les anciens OFP renvoyant `/home` sont normalisés vers le formulaire de création côté client. Aucune donnée d'authentification IVAO n'est demandée par Hermès.

## À ne pas confondre

- Documentation IVAO API v2 « Whazzup » : données réseau/vols, ne vaut pas autorisation d'écriture dans le système FPL.
- Documentation Flight Plan System : confirme l'import ICAO et la soumission par le pilote.
- Le portail `https://api.ivao.aero/docs` référence aussi une documentation `fpl`, mais le contrat de création, les permissions OAuth et les schémas n'ont pas été vérifiés en accès public.
- SimBrief permet un pré-dépôt IVAO, mais le schéma de paramètres URL utilisé par son bouton n'est pas documenté publiquement à ce stade. Ne pas inventer de query-string ni rejouer une session navigateur.

## Pour une intégration OAuth2 de création directe (non implémentée)

1. Créer une application via `https://developers.ivao.aero`, selon les conditions IVAO applicables aux compagnies virtuelles.
2. Vérifier auprès de `web@ivao.aero` si les scopes et endpoints FPL **en écriture** sont accessibles aux applications VA ; obtenir le schéma exact, les règles anti-doublon et la politique d'utilisation.
3. Faire authentifier chaque pilote sur le domaine IVAO par un flux OAuth2 autorisé, sans demander son mot de passe à Hermès.
4. Garder les secrets/tokens serveur dans un stockage chiffré, isolé par pilote ; ne jamais journaliser des credentials.
5. Soumettre uniquement après action explicite du pilote, avec identifiant opérationnel/idempotence, puis confirmer le dépôt via l'API authentifiée.
6. Sur erreur/timeout, afficher « non confirmé » ; ne pas annoncer un dépôt réussi.
7. Ajouter tests de refus 401/403, expiration OAuth, doublons, changement de pilote, données OFP incohérentes, API indisponible.

Aucune de ces étapes OAuth n'est activée sans contrat/autorisation IVAO vérifiés.

## Recette

- OFP Legacy et API compagnie : bouton IVAO mène à `/flight-plans/create`.
- Ancien OFP avec `network_prefiles.ivao.url=/home` : même résultat.
- FPL absent/incomplet : blocage local explicite ; pas de dépôt revendiqué.
- FPL ICAO complet : copie, ouverture, import et validation sur le portail IVAO.
- Vérifier callsign, appareil, départ/arrivée, route, altitude, endurance et mentions de compagnie.
- VATSIM et Solo : comportement inchangé.

Références officielles :
- https://wiki.ivao.aero/en/home/devops/websites/flightplansystem
- https://wiki.ivao.aero/en/home/devops/api/documentation-v2
- https://api.ivao.aero/docs
- https://developers.ivao.aero
