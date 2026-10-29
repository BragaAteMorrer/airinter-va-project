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
   autorisée. Le PIREP doit mentionner exactement l'origine et la destination.
7. À l'acceptation du PIREP, la mission et le vol privé sont clôturés, l'avion
   se trouve sur la base technique prévue, reste **MAINTENANCE** et requiert
   une nouvelle intervention. Aucune prime de rapatriement commercial.
8. L'administration termine les checks A/B/C/historiques nécessaires,
   consigne la réparation définitive, puis clique **Clôturer le convoyage**.
   La levée FDM est effectuée à ce moment-là, si toutes les conditions sont
   réunies. Les actions restent journalisées.

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
- Vérifier un appareil standard : le statut MAINTENANCE continue d'empêcher
  la réservation des vols habituels.
- Ne pas fusionner ou déployer sans ces tests sur une instance de préproduction.

**Limite actuelle :** la durée d'immobilisation est modélisée, mais ce
développement ne génère pas encore de débit comptable distinct pour
l'intervention de dépannage. Le devis jumpseat éventuel reste facturé
selon les règles existantes. La mission de convoyage n'est pas une
autorisation aéronautique réelle.
