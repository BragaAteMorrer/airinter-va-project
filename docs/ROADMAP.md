# Roadmap produit

La règle de priorité est simple : fiabilité opérationnelle avant enrichissement, patrimoine avant gamification générique.

## Prométhée 1.0 — Faire voler

Objectif : un pilote trouve un vol et le termine sans assistance.

- Dashboard OCC et tableau de départs réellement connectés aux opérations.
- Recherche et réservation simples.
- Affectation appareil et briefing unifié.
- Passage SimBrief → Hermès sans ressaisie.
- Télémétrie résiliente, reprise hors ligne et PIREP automatique.
- Live operations, téléchargements Hermès et responsive/PWA de base.
- Chaîne de locale cohérente : utilisateur → session → anglais → français historique signalé.

Critère de sortie : le parcours complet est couvert par des tests et peut être exécuté par un nouveau pilote sans Discord.

## Prométhée 1.1 — Faire vivre la compagnie

- Flotte persistante légère avec repositionnement autorisé.
- Activité compagnie et événements visibles sur le dashboard.
- Carnet Air Inter remplaçant le passeport générique.
- Missions qui modifient réellement l’état de la flotte.
- Grades séparés des qualifications.
- Analyse PIREP en trois axes : Safety, Operations et Flight.

## Principe produit — respecter le projet Air Inter VA

Prométhée valorise l'identité historique déjà portée par la compagnie, ses horaires et ses appareils, sans créer un système éditorial d'époques ou d'archives parallèle qui ne correspond pas au projet de ses créateurs. Les évolutions historiques restent pilotées par les besoins exprimés par Air Inter VA.

## Prométhée 2.0 — Écosystème Air Inter

- API métier stable et documentée.
- Air Inter ID via OAuth 2.1 / OpenID Connect quand plusieurs applications le justifient.
- PWA complète.
- Dispatch et opérations avancées.
- Connecteur X-Plane seulement après stabilisation de la chaîne MSFS.

## Hors objectif initial

- Monnaie virtuelle sans utilité opérationnelle.
- Niveaux XP et classements permanents.
- Rejet automatique d’un PIREP pour des écarts mineurs.
- Maintenance exigeant une présence staff permanente.
- Application mobile native Prométhée.
- Réimplémentation de SimBrief.
