# Hermès v1.0.1 — cohérence visuelle Prométhée

## Audit constaté

Prométhée sépare déjà le style historique (`data-era`) de l'apparence (`data-appearance`). Son thème moderne utilise l'identité Air Inter bleu/cyan/rouge, des surfaces claires, une navigation calme et des rayons modestes. Le thème 1999–2005 est plus dense : Arial/Verdana, angles droits, bordures marquées, aplats bleus et tableaux métier. Les variantes nuit sont indépendantes de l'ère.

Le dépôt contient déjà une expérimentation Minitel côté Prométhée. Cette livraison ne la considère pas comme le contrat graphique définitif demandé pour le futur thème commun. Hermès réserve seulement l'identifiant `minitel` et n'expose aucune option incomplète.

Hermès possédait déjà deux présentations, mais son ancien `web2000` était plus proche d'un pastiche desktop (dégradés, reliefs/outset) que du langage 2000 réellement utilisé par Prométhée. Il ne proposait pas non plus de mode nuit indépendant.

## Architecture Hermès

- Le HTML reste fonctionnel et indépendant du thème.
- `styles.css` reste la base des composants ACARS.
- `hermes-themes.css` porte uniquement les tokens et variantes visuelles.
- `data-era="modern|2000"` sélectionne l'ère.
- `data-appearance="light|dark"` sélectionne l'apparence.
- `minitel` est réservé mais non sélectionnable.
- Les préférences sont locales (`hermesEra`, `hermesAppearance`) ; aucune synchronisation serveur fictive n'est introduite.
- Les anciennes clés locales sont lues en repli pour ne pas casser les installations existantes.

## Intentions

**Moderne** reprend le bleu Air Inter, le rouge d'accent, les surfaces et la retenue visuelle de Prométhée, tout en conservant la navigation latérale et la densité propres à un ACARS.

**Années 2000** reprend les panneaux métier, angles droits, aplats bleus, typographie Arial/Verdana et densité de Prométhée. Il évite volontairement une imitation Windows XP.

**Nuit** est orthogonal aux deux ères. Moderne nuit utilise les surfaces bleu-noir de Prométhée ; 2000 nuit conserve une identité bleu-gris plus physique.

## Minitel futur

Le futur thème commun Prométhée/Hermès pourra remplacer les tokens, typographies, rayons, surfaces et animations sans modifier la structure fonctionnelle des écrans. Aucun design Minitel final n'est figé dans cette livraison.

## Validation attendue

La CI doit continuer à construire Hermès et exécuter les tests .NET existants. Une validation visuelle Windows/WebView2 reste nécessaire pour les quatre combinaisons : Moderne clair, Moderne nuit, Années 2000 clair et Années 2000 nuit, notamment à 900 px, 1280×840, maximisé et HiDPI.
