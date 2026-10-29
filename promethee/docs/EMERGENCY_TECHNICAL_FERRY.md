# Prométhée — dépannage d'urgence et convoyage technique

## Pourquoi ce dispositif ?

Un hard landing ou une alerte FDM peut immobiliser un appareil sur une escale sans
capacité de maintenance structurelle. La levée immédiate du hold suivie d'un vol
commercial banal n'est pas acceptable. Le convoyage est une **exception
opérationnelle contrôlée**, non une levée d'immobilisation.

## Processus de simulation

1. Après le PIREP incident, l'appareil est **MAINTENANCE / PARKED**.
   L'incident doit être un safety hold FDM ou un `Hard Landing Check` historique.
2. Sur l'écran *Admin > Maintenance cellule*, la direction technique valide
   une inspection provisoire, renseigne un compte-rendu (minimum 12 caractères),
   la durée de préparation (1 à 48 h) et une base technique.
3. La destination doit être **active**, capable de réaliser les checks **B et C**,
   et différente de l'aéroport actuel. Les bases déjà équipées réparent sur place.
4. Prométhée crée de façon atomique une **mission de rapatriement technique**
   (multiplicateur 1) et un **vol privé invisible au programme public**
   de type **positionnement** (`P`), associé à une seule immatriculation.
5. Après expiration de la durée de préparation, le pilote qualifié réserve la
   mission dans *Missions*. La réservation produit un vrai bid phpVMS reconnu
   par Hermès. Le vol technique doit être préparé avec **zéro passager**.
   Les tarifs passagers/fret commerciaux ne sont pas ajoutés au PIREP.
6. L'appareil **reste MAINTENANCE pendant toute l'opération**. La seule
   exception de disponibilité concerne le vol privé et l'immatriculation
   autorisée. Le PIREP doit être **ACCEPTÉ**, correspondre à la mission réellement réservée par ce pilote et mentionner exactement l'origine et la destination.
7. À l'acceptation du PIREP, la mission et le vol privé sont clôturés, l'avion
   se trouve sur la base technique prévue, reste **MAINTENANCE** et requiert
   une nouvelle intervention. Aucune prime de rapatriement commercial.
8. L'administration termine les checks A/B/C/historiques nécessaires,
   consigne la réparation définitive, puis clique **Clôturer le convoyage**.
   La levée FDM est effectuée à ce moment-là, si toutes les conditions sont
   réunies. Les actions restent journalisées.

## Déroutement en vol

Lorsque le PIREP du convoyage est accepté mais porte un **aéroport réel
d'arrivée différent** de la base technique prévue, Prométhée ne considère pas
la mission initiale comme réussie. Il :

- Enregistre l'aéroport déclaré dans le PIREP comme nouvelle position physique ;
- Désactive le vol privé et la mission initiale, sans prime de rapatriement ;
- Place le convoyage en état **DÉROUTÉ** et conserve l'appareil en MAINTENANCE ;
- Journalise la route prévue et la position du déroutement.

À partir de cette position, **deux scénarios** sont possibles :

- **Escale non équipée** : nouvelle inspection provisoire, puis un **nouveau
  convoyage privé unique** vers une base technique agréée. L'ancienne mission
  reste désactivée et son événement de déroutement demeure dans l'historique.
- **Base technique active B+C** : effectuer sur place les visites techniques
  nécessaires et clôturer le dossier avec un compte-rendu définitif. La
  validation exige toujours un PIREP accepté correspondant au vol effectivement
  réalisé et à l'aéroport d'arrivée réel.

Un PIREP avec origine incohérente ou code ICAO d'arrivée malformé reste
bloqué pour intervention humaine. L'aéroport peut ne pas encore disposer d'une
fiche dans la base locale phpVMS. Le système ne devine pas la position réelle à partir du
plan de vol SimBrief : **le PIREP accepté doit porter l'aéroport réel**, pas
automatiquement la destination programmée.

## Annulation avant départ et changement de base

L'administration peut désormais cliquer sur **Annuler le convoyage** dans
*Maintenance cellule* avec un motif écrit (12 caractères minimum). Cette action
est possible **avant réservation et avant tout PIREP** : elle désactive le vol
privé et sa mission, conserve un événement d'audit et **ne lève jamais
l'immobilisation**. Une nouvelle inspection peut ensuite créer une nouvelle
mission vers une autre base.

Si le pilote a déjà réservé la mission, il doit d'abord annuler sa réservation
depuis *Missions*. Un PIREP déjà associé au vol bloque l'annulation du permis,
car son état doit être traité explicitement. Un convoyage déjà arrivé ne peut
pas être annulé : il nécessite la réparation finale et la clôture technique.

Les missions de ferry encore autorisées ou arrivées sont verrouillées contre
la modification et la suppression depuis l'administration générale des missions.
Pour les incidents provenant uniquement de `Hard Landing Check` historique,
Prométhée crée aussi une immobilisation de sécurité persistante, afin que
l'expiration du check historique ne déverrouille pas l'appareil.

## Cas bloquants

- Absence de safety hold FDM et de hard landing historique ;
- Appareil non immobilisé ou non au parking ;
- Absence de base technique B+C, destination identique à l'origine ;
- Check A/B/C actif ou maintenance historique non liée au hard landing ;
- Réservations existantes sur l'avion ;
- Deuxième autorisation sur le même incident ;
- Départ avant la fin de l'inspection provisoire ;
- PIREP vers un autre aéroport ;
- Tentative de levée du hold FDM avant la réparation finale.

En présence d'une réparation lourde impossible à sécuriser pour le convoyage,
l'administrateur **ne doit pas autoriser le ferry**. Il conserve l'appareil en
maintenance et organise une réparation sur place.

## Protection contre les rapatriements automatiques

Le synchroniseur régional ne crée pas de retour automatique pour un appareil
en `MAINTENANCE` ou disposant d'un hold / ferry ouvert. Le module historique
`DisposableSpecial` ne peut pas réactiver l'avion au terme d'un check ou lors
du rejet d'un PIREP si Prométhée maintient une immobilisation.

## Déploiement et validation

- Appliquer la migration
  `2026_10_29_000001_add_emergency_technical_ferry.php` avec la procédure
  normale de migration du module Prométhée ; vider le cache des routes Laravel.
- Exécuter le test dédié `tests/PrometheeEmergencyFerryTest.php`.
- Valider la réservation depuis *Missions*, l'OFP à zéro passager, le pré-dépôt
  PIREP, l'enregistrement Hermès et la clôture après acceptation.
- Vérifier la rejéction d'un PIREP déjà accepté : l'appareil demeure bloqué, sans remise en service possible.
- Vérifier un appareil standard : le statut MAINTENANCE continue d'empêcher
  la réservation des vols habituels.
- Ne pas fusionner ou déployer sans ces tests sur une instance de préproduction.

**Limite actuelle :** la durée d'immobilisation est modélisée, mais ce
développement ne génère pas encore de débit comptable distinct pour
l'intervention de dépannage. Le devis jumpseat éventuel reste facturé
selon les règles existantes. La mission de convoyage n'est pas une
autorisation aéronautique réelle.
