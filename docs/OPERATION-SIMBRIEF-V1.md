# Contrat SimBrief par opération — API v1

Prométhée traite désormais l'opération comme point d'entrée du workflow SimBrief d'Hermès.

## Endpoints

```
POST /api/v1/operations/{operation_id}/simbrief/session
POST /api/v1/operations/{operation_id}/simbrief/redirect
POST /api/v1/operations/{operation_id}/simbrief/account/import
POST /api/v1/operations/{operation_id}/simbrief/import
```

L'identifiant public `operation_id` est résolu vers le Bid phpVMS historique. Aucun changement de schéma de base de données n'est nécessaire.

## Corrélation SimBrief

Pour les appels API v1, le `static_id` envoyé à SimBrief est déterministe :

```
AIRINTER_OP_{OPERATION_ID}
```

Le même identifiant est utilisé pour ouvrir, modifier et réimporter le plan. Le serveur reconstruit le vol et l'appareil depuis l'opération authentifiée : Hermès ne choisit pas librement un autre vol ou appareil dans ces routes.

Les réponses SimBrief v1 exposent également `operation_id`.

## Compatibilité

Les routes historiques `/api/acars/flights/{flight}/simbrief/*` restent disponibles pendant la transition Prometheus → Prométhée. Elles ne sont plus utilisées par le parcours SimBrief normal d'Hermès lorsqu'une opération Prométhée est sélectionnée.

La base phpVMS existante reste la source de vérité pour les Bid, SimBrief et futurs PIREP. Le lot B n'ajoute, ne supprime et ne migre aucune table.

## Suite

Le lot C introduira le prefile PIREP piloté par l'opération. Le dispatch pourra alors vérifier côté serveur l'existence du PIREP et ne laisser à Hermès que les contrôles locaux simulateur/appareil/aéroport.


## Airframes phpVMS / Hermès

Hermès consomme désormais le catalogue `simbrief_airframes` déjà administré par phpVMS via `/admin/airframes`.
Pour l'appareil affecté à l'opération, Prométhée fusionne :

- les variantes natives Hermès (Fenix, PMDG, iniBuilds, etc.) ;
- les airframes personnalisés créés dans phpVMS ;
- les airframes synchronisés depuis SimBrief.

Lorsqu'un airframe possède un `airframe_id`, celui-ci est envoyé comme paramètre SimBrief `type`.
Sinon, le code ICAO de l'airframe est utilisé. La sélection reste attachée à l'opération et ne modifie ni l'appareil physique phpVMS ni le schéma de la flotte.

Les métadonnées sûres présentes dans `details` et `options` sont renvoyées à Hermès. Une image HTTPS est affichée lorsque le catalogue en fournit une.

## Routes proposées

`GET /api/v1/operations/{operation_id}/briefing` renvoie désormais :

- `route` : route programmée ;
- `route_options` : routes compagnie distinctes trouvées sur la même liaison dans le programme phpVMS ;
- `route_auto_available=true` : Hermès peut envoyer une route vide et laisser SimBrief calculer sa propre proposition.

Aucune route SimBrief n'est inventée côté Prométhée. Les variantes proposées proviennent du programme compagnie existant.

## Options SimBrief avancées

Les options avancées d'Hermès sont des surcharges éphémères par génération d'OFP. Elles sont validées côté Prométhée puis transmises à SimBrief sans modifier les vols, appareils ou préférences persistantes phpVMS.

Le contrat couvre notamment `units`, `planformat`, `navlog`, `maps`, `tlr`, `notams`, `firnot`, `stepclimbs`, `etops`, `find_sidstar`, `cruise`, `civalue`, `contpct`, `resvrule`, `selcal`, `deprwy`, `arrrwy`, `taxiout`, `taxiin`, `pax`, `callsign` et `manualrmk`.
