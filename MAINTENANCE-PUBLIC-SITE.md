# Air Inter VA — maintenance du site public

Ce mécanisme vise uniquement le site historique GuppY dans `public_html/`. Il ne déploie rien directement sur le serveur et n'affecte pas automatiquement Argos, Prométhée ou Hermès.

## Mise en maintenance

1. Fusionner la PR et déployer `public_html/.htaccess`, `public_html/maintenance.html` et `public_html/maintenance.on` vers la racine web effective du site (cPanel : vérifier que celle-ci est bien `public_html/`).
2. Sauvegarder le `.htaccess` du serveur avant de le remplacer et conserver ses éventuelles règles locales.
3. Le fichier vide ou non vide `maintenance.on`, lorsqu'il est présent **à la racine web**, active immédiatement les réponses HTTP **503** pour les routes publiques, y compris les chemins PHP directs. Les requêtes sur `/maintenance.html` restent possibles afin d'afficher le message.
4. Vérifier : `curl -I https://www.airinter-va.org/` → `503`, `curl -I 'https://www.airinter-va.org/index.php?lng=fr&tconfig=0'` → `503`, `curl -I https://www.airinter-va.org/maintenance.html` → `200`. Pour inspecter le rendu : `curl -i https://www.airinter-va.org/`.
5. Tester également une page profonde et le rendu mobile. Ne pas afficher de secrets, de stacktrace ou de détail d'incident sur la page publique.

## Réouverture

Sur le serveur cPanel, **supprimer ou renommer `public_html/maintenance.on` en dehors de la racine web** (par exemple `~/maintenance.off`). Le site GuppY répond alors normalement sans modifier son `index.php`. Vérifier la page d'accueil, la navigation, les images et la connexion habituelle.

### Attention au déploiement Git

Puisque le marqueur est suivi dans cette PR, un déploiement ultérieur qui restaure les fichiers suivis peut **réactiver la maintenance**. Après la reprise, faire également retirer `public_html/maintenance.on` du dépôt via un commit/une PR, ou conserver le marqueur hors du dépôt et l'activer manuellement lors des prochaines opérations.

## Points de vigilance

- La règle est volontairement **sans bypass IP ni cookie** : le site public ne reste pas accessible à l'administration pendant cette maintenance. Pour intervenir dans GuppY, passer par une instance isolée ou désactiver brièvement la maintenance dans un créneau contrôlé.
- La page HTML est autonome (CSS et SVG intégrés, aucune ressource externe), afin de rester disponible même en cas d'incident PHP ou de base de données.
- Les réponses 503 portent `Retry-After: 3600` (indication de nouvelle tentative dans une heure, **pas** une heure promise de réouverture).
- Tester la configuration Apache sur LWS, notamment le support des directives `Header ... expr=`. En cas de 500 après déploiement, restaurer immédiatement le `.htaccess` sauvegardé et vérifier le journal d'erreurs Apache.
- Les hôtes séparés (SSO Argos et OCC Prométhée) doivent être placés en maintenance chacun de leur côté si désiré.
