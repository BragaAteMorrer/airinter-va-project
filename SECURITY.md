# Sécurité

## Signaler une vulnérabilité

Ne publiez pas de secret ou de vulnérabilité exploitable dans une issue publique. Contactez l’équipe Air Inter VA par un canal privé et indiquez le composant, l’impact, les étapes de reproduction et, si possible, une proposition de correction.

## Données interdites dans le dépôt

Ce dépôt ne doit contenir aucun export de compte d’hébergement, historique shell, boîte mail, journal de production, fichier `.env`, sauvegarde SQL, jeton, clé privée ou artefact de compilation.

Les règles `.gitignore` et le contrôle « Repository hygiene » de la CI bloquent les réintroductions les plus courantes.

## Incident de secret

Si une information sensible a déjà été versionnée :

1. la révoquer ou la faire tourner immédiatement ;
2. supprimer le fichier de la branche active ;
3. examiner l’historique et les journaux d’accès ;
4. réécrire l’historique uniquement dans une intervention dédiée et coordonnée ;
5. informer les personnes concernées selon la nature de la donnée.

Supprimer un fichier dans un nouveau commit ne le retire pas de l’historique Git.
