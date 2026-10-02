# Contrat PIREP par opération — API v1

Hermès traite le PIREP comme un artefact de l'opération Prométhée tout en conservant les tables et services phpVMS existants.

## Invariants absolus

Ces trois règles font partie du contrat public entre Hermès, Prométhée et phpVMS :

- **PREFILE != FILE**
- **PREFILED != COMPLETED**
- **PIREP EXISTENCE != FLIGHT COMPLETION**

Créer ou retrouver un PIREP Hermès ne constitue jamais une preuve que le vol a été effectué.

Un PIREP de préparation reste un rapport actif. Le pilote doit encore démarrer ACARS, produire de la télémétrie réelle, arriver au parking puis déposer le rapport final.

## API

```
GET  /api/v1/operations/{operation_id}/pirep
POST /api/v1/operations/{operation_id}/pirep
```

Le POST est idempotent : si l'opération possède déjà un PIREP Hermès **actif**, Prométhée retourne ce même PIREP au lieu d'en créer un second.

Un PIREP réellement déposé n'est jamais rouvert. Une ancienne donnée Hermès terminale et incohérente n'est pas réparée automatiquement par cette route.

## Chaîne de corrélation

```
operation_id
  -> Bid phpVMS
  -> SimBrief exact de l'opération
  -> PirepService::prefile()
  -> PIREP phpVMS
```

Le marqueur :

```
source_name = Hermes ACARS [operation_id]
```

est la clé de corrélation inverse PIREP -> opération.

L'identité métier est donc **operation_id / Bid**, jamais le seul `flight_id`.

Deux occurrences du même vol programme doivent produire deux opérations différentes et, le moment venu, deux PIREP différents.

## Cycle de vie

| État métier Hermès | État phpVMS / preuve | Sens |
| --- | --- | --- |
| RESERVED | Bid présent, pas de PIREP | réservation active |
| PLANNING | appareil/OFP en préparation | vol non commencé |
| PREFILED / PREPARED | `state=IN_PROGRESS`, `submitted_at=NULL`, PIREP actif | brouillon prêt, vol non commencé |
| IN_PROGRESS | PIREP actif + télémétrie ACARS réelle | vol en cours |
| ARRIVED / AWAITING_FILING | preuve d'arrivée `IN`, rapport pas encore déposé | vol arrivé, dépôt final à faire |
| COMPLETED | `submitted_at != NULL` **et** `state in PENDING/ACCEPTED/REJECTED` | PIREP final réellement déposé |
| CANCELLED | état/statut phpVMS annulé | opération annulée |

### Important : `ARRIVED` n'est pas `COMPLETED`

`PirepStatus::ARRIVED` peut représenter une arrivée opérationnelle avant le dépôt final.

Il ne suffit donc jamais, à lui seul, à rendre une opération Hermès terminale.

La preuve serveur utilisée par Prométhée pour `COMPLETED` est le dépôt final phpVMS :

```
submitted_at != NULL
AND
state IN (PENDING, ACCEPTED, REJECTED)
```

Cette règle est centralisée dans `HermesPirepLifecycleService::isFiled()`.

## PREFILE

`PirepService::prefile()` doit produire un rapport de travail :

```
state  = IN_PROGRESS
status = INITIATED
submitted_at = NULL
```

Le prefile :

- ne dépose pas le rapport final ;
- ne supprime pas le Bid ;
- ne déplace pas l'appareil à destination ;
- ne déclenche pas les finances d'un vol terminé ;
- ne crédite pas le pilote ;
- ne rend pas l'opération `COMPLETED`.

Le PIREP actif est précisément ce qui permet à Hermès de démarrer l'enregistrement.

## START / télémétrie

Le premier lot de télémétrie réelle fait passer le Dispatch Hermès à `IN_PROGRESS`.

Les données SimBrief de type ROUTE enregistrées avant le départ sont des données de planification : elles ne constituent pas une preuve que le simulateur a réellement commencé le vol.

La reprise après interruption conserve obligatoirement :

- le même `operation_id` ;
- le même `pirep_id` ;
- le même appareil affecté.

## FILE — protection serveur

Le client Hermès impose déjà l'arrivée au parking avant de proposer le dépôt final, mais le serveur ne fait jamais confiance au seul client.

Pour un PIREP dont le `source_name` est Hermès, `POST /api/pireps/{pirep_id}/file` exige désormais :

1. une preuve de télémétrie ACARS réelle — les seuls points ROUTE SimBrief ne comptent pas ;
2. une preuve d'arrivée au parking, événement/phase `IN`.

Sans ces preuves, le dépôt final est refusé avec HTTP 409.

Cette barrière empêche une ancienne version d'Hermès, un JavaScript obsolète en cache ou un appel client incorrect de transformer un prefile en vol terminé.

Après un FILE valide, phpVMS émet `PirepFiled`. Le `BidEventHandler` peut alors supprimer le Bid et l'opération quitte normalement « Mes réservations ».

## Appareil

Un appareil déjà affecté au Bid courant reste utilisable par **cette même opération** même lorsque `bids.block_aircraft` est actif.

En revanche, une autre opération reste soumise aux protections normales contre la double réservation.

Le prefile ne déplace jamais l'appareil. Les conséquences de flotte restent liées au vrai workflow de dépôt/acceptation phpVMS.

## Ancien PIREP et nouveau vol identique

Un PIREP terminé est immuable.

Après un vrai vol terminé, reprendre la même ligne programme signifie :

```
même flight_id possible
nouveau Bid
nouvel operation_id
nouveau PIREP
```

La détection de doublon Hermès est corrélée par le `source_name` exact de l'opération. Un ancien PIREP du même `flight_id` ne doit donc jamais être récupéré pour la nouvelle opération.

## Données historiques incohérentes

La requête HTTP de prefile ne supprime, ne rouvre et ne rétrograde plus automatiquement un PIREP terminal existant.

Une commande d'administration permet de diagnostiquer un ancien « zero-flight ghost » Hermès :

```bash
php artisan promethee:hermes-pirep-repair op_123
```

Le mode est **dry-run par défaut**.

Pour cibler explicitement un PIREP :

```bash
php artisan promethee:hermes-pirep-repair op_123 --pirep=PIREP_ID
```

Une réparation destructive n'est effectuée qu'avec une action explicite :

```bash
php artisan promethee:hermes-pirep-repair op_123 --pirep=PIREP_ID --apply
```

La commande refuse la réparation si le rapport possède une preuve de vol réelle.

## Logs de transition

Les transitions Hermès importantes utilisent des logs structurés avec, selon disponibilité :

- `operation_id`
- `bid_id`
- `pirep_id`
- `pirep_state`
- `pirep_status`
- `dispatch_status`
- `aircraft_id`

Transitions suivies :

```
PREFILE
PREFILE_IDEMPOTENT
START
RESUME
ARRIVAL
FILE
```

Aucun token Argos, mot de passe ou secret ne doit être journalisé.

## Réponse de création

La réponse du prefile contient `operation_id`, `pirep_id`, `simbrief_id` et un bloc `correlation` permettant à Hermès de vérifier explicitement toute la chaîne.

## Compatibilité

Aucune migration destructive n'est nécessaire.

Aucun PIREP historique n'est automatiquement réécrit.

Le workflow natif phpVMS reste responsable du prefile, du dépôt final, de l'acceptation et des conséquences de flotte/finance. Prométhée expose une machine d'état cohérente à Hermès sans confondre l'existence du PIREP avec la fin du vol.
