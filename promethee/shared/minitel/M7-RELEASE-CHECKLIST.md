# M7 — Checklist de release 3615 AIRINTER / HERMÈS

Cette checklist complète la gate automatisée `tools/check_minitel_release.cjs`.
Une release Minitel n'est considérée prête pour la production qu'après validation
des contrôles automatiques ET des scénarios manuels ci-dessous.

## Gate automatisée

La workflow `Quality` doit afficher les quatre jobs suivants en vert :

- Repository hygiene
- Hermes tests
- Promethee manifest
- Minitel M7 release gate

Le job M7 produit deux artefacts :

- `minitel-m7-release.json`
- `minitel-m7-release.md`

Ils listent les invariants vérifiés automatiquement : runtime partagé, clavier,
routes/actions, Datalink local-first, recovery, dépôt PIREP, fidélité M6,
fallback mobile et non-régression Moderne/Années 2000.

## Scénario manuel A — Prométhée clavier-only

1. Activer le mode Minitel sur desktop.
2. Ouvrir GUIDE puis RÉGLAGES sans souris.
3. Passer en AUTHENTIQUE + MONOCHROME puis revenir en RAPIDE + COULEUR.
4. Rechercher un vol qualifié.
5. Réserver le vol.
6. Ouvrir MES OPERATIONS.
7. Sélectionner un appareil réellement éligible.
8. Consulter le briefing.
9. Tester le chemin SimBrief compte.
10. Tester le chemin SimBrief compagnie si la clé est configurée.
11. Préparer le PIREP.
12. Vérifier le dispatch.
13. Revenir au SOMMAIRE puis quitter avec CONNEXION/FIN.

Critère : aucune action métier ne doit nécessiter la souris à l'intérieur du terminal.

## Scénario manuel B — Hermès pré-vol et vol complet

1. Démarrer Hermès avec un simulateur supporté.
2. Se connecter au compte Air Inter depuis le terminal.
3. Sélectionner/réserver une opération.
4. Affecter l'appareil.
5. Préparer/importer l'OFP.
6. Pré-déposer le PIREP.
7. Vérifier que START reste bloqué si l'avion n'est pas dans un état pré-vol sûr.
8. Revenir à un état sûr puis démarrer l'enregistrement.
9. Vérifier les changements de phase et la télémétrie.
10. Mettre en pause puis reprendre.
11. Forcer une synchronisation.
12. Ouvrir Datalink, lire, ACK et répondre à un message.
13. Consulter Journal et Air Inter Network.
14. Terminer le vol jusqu'à IN.
15. Consulter Flight Review, observations et anomalies.
16. Déposer le PIREP depuis le terminal.

Critère : le vol déposé doit être le même PIREP/opération/appareil que celui préparé.

## Scénario manuel C — Coupure réseau

1. Pendant un vol enregistré, couper l'accès réseau.
2. Envoyer un message Datalink.
3. Vérifier que l'interface signale la file locale.
4. Continuer le vol et laisser de la télémétrie en attente.
5. Rétablir le réseau.
6. Utiliser SYNCHRONISER.
7. Vérifier que les files locales reviennent à zéro et que les messages ne sont pas dupliqués.

## Scénario manuel D — Recovery

1. Pendant un vol enregistré, fermer brutalement Hermès.
2. Relancer l'application.
3. Vérifier que le mode Minitel ouvre Recovery Center.
4. Consulter le Flight Review partiel.
5. Reprendre le vol.
6. Vérifier que l'opération, le PIREP, la phase et le journal sont conservés.
7. Terminer normalement le vol.

L'abandon destructif doit rester dans l'interface graphique moderne.

## Scénario manuel E — Fallback et non-régression

1. Enregistrer Minitel comme préférence desktop.
2. Ouvrir Prométhée sur téléphone.
3. Vérifier le fallback moderne tactile sans suppression de la préférence desktop.
4. Revenir sur desktop et vérifier que Minitel est toujours sélectionné.
5. Quitter volontairement Minitel et vérifier le retour Moderne.
6. Tester le thème Moderne.
7. Tester le thème Années 2000.
8. Réactiver Minitel et vérifier la persistance des réglages Videotex.

## Go / No-Go

GO si :
- les quatre jobs Quality sont verts ;
- le rapport M7 automatisé indique `release_ready: true` ;
- les scénarios A à E ont été exécutés sur une build de release ;
- aucune régression bloquante n'est observée en Moderne ou Années 2000.

NO-GO dans tous les autres cas.
