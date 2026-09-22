# Lot J — Mes réservations devient Mes opérations

## Objectif

La page pilote ne présente plus une réservation phpVMS comme un simple objet binaire. Elle projette la progression réelle du vol à partir des données existantes : Bid, appareil, SimBrief, PIREP Hermès et télémétrie.

## États

- `AIRCRAFT_REQUIRED` : aucun appareil sélectionné ;
- `OFP_REQUIRED` : appareil sélectionné, OFP absent ;
- `PIREP_REQUIRED` : OFP disponible, PIREP Hermès absent ;
- `READY` : PIREP préparé, aucune télémétrie reçue ;
- `IN_PROGRESS` : télémétrie Hermès reçue ;
- `COMPLETED` : PIREP soumis/arrivé ou passé dans un état terminal phpVMS ;
- `CANCELLED` : PIREP annulé.

Ces états sont calculés depuis les sources existantes et ne nécessitent aucune nouvelle table.

## Suppression conditionnelle

Une réservation reste supprimable tant qu'aucun PIREP Hermès n'a été créé.

Dès qu'un PIREP existe, le vol est devenu un enregistrement opérationnel. Le bouton de suppression disparaît et le contrôleur refuse également la suppression côté serveur.

La règle ne dépend donc pas de l'interface et ne permet pas de supprimer par requête directe une opération déjà engagée.

## SimBrief

Le lookup de l'OFP suit le cycle réel phpVMS :

1. si un PIREP existe, rechercher d'abord le SimBrief attaché à ce PIREP ;
2. sinon/repli, rechercher l'OFP actif non attaché créé depuis la réservation.

Cela évite qu'une opération paraisse perdre son OFP au moment du prefile.

## Interface

La page affiche désormais :

- l'`operation_id` ;
- le vol et la route ;
- l'appareil ;
- la progression ;
- l'état opérationnel ;
- la prochaine action ;
- l'action de suppression uniquement lorsqu'elle est autorisée.
