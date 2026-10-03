# Audit de navigation Prométhée

Audit statique effectué à partir des déclarations de routes, contrôleurs et vues du dépôt. `php artisan route:list` ne peut pas être exécuté dans cet espace de travail : les dépendances Composer ne sont pas présentes pour le runtime PHP disponible. Les routes ci-dessous ont donc été recoupées avec leurs déclarations `Route::*`, contrôleurs et vues.

| Fonction historique | Route actuelle | Contrôleur / vue | Module / accès | État | Action |
|---|---|---|---|---|---|
| Actualités | `DBasic.news` (`/dnews`) | `DB_NewsController` / `DBasic::news.index` | Disposable Basic inactif | Module optionnel | Non restauré |
| NOTAMs | `DSpecial.notams` (`/dnotams`) | `DS_NotamController` | Disposable Special inactif | Module optionnel | Non restauré |
| Accueil | `promethee.occ` (`/occ`) | `PortalController@occ` / `promethee::occ` | Public | Présent | Conservé dans Bienvenue |
| Pilotes | `promethee.public.pilots` (`/public/pilots`) | `PortalController@publicPilots` | Public | Présent | Conservé dans Bienvenue et Compagnie (contexte annuaire) |
| Vols | `promethee.flights` (`/flights`) | `PortalController@flights` | Authentifié | Présent mais absent du menu historique groupé | Restauré dans Opérations |
| Carte des vols | `promethee.flights` (`/flights`) | `PortalController@flights` / `promethee::flights` | Authentifié | Renommé / déplacé | Restauré dans Bienvenue; la carte réseau est native à Prométhée |
| OCC en détail | `promethee.occ` (`/occ`) | `PortalController@occ` | Public | Présent | Conservé |
| Profil | `promethee.profile` (`/profile`) | `PortalController@profile` | Authentifié | Présent | Conservé |
| Paramètres | — | Le profil Prométhée est actuellement en lecture seule | — | Supprimé / introuvable | Non restauré; aucun lien Legacy n'est exposé |
| Articles achetés | `promethee.shop` (`/shop`) | `PortalController@shop` / `promethee::shop` | Authentifié | Renommé / fusionné | Restauré sous Boutique; les achats y sont listés |
| Mon passeport | `promethee.passport` (`/passport`) | `PortalController@passport` / `promethee::passport` | Authentifié | Présent | Conservé |
| Mon HUB | — | — | L’ancien écran est dans Disposable Basic inactif | Module optionnel | Non restauré; le HUB courant reste visible dans le profil |
| Ma compagnie | — | — | Pas de page personnelle distincte dans les routes actives | Supprimé / introuvable | Non restauré |
| Mes scènes | `DBasic.scenery` (`/dscenery`) | `DB_SceneryController` | Disposable Basic inactif | Module optionnel | Non restauré |
| Mes affectations | `promethee.assignments` (`/assignments`) | `PortalController@assignments` | Authentifié | Présent | Conservé |
| Mes rapports | `promethee.public.pireps` (`/public/pireps`) | `PortalController@publicPireps` | Authentifié | Présent mais absent | Restauré |
| Mes réservations | `promethee.bookings` (`/bookings`) | `PortalController@bookings` / `promethee::bookings` | Authentifié | Présent | Restauré dans l'interface native Prométhée |
| Compagnie | — | — | Pas de page générale distincte dans les routes actives | Supprimé / introuvable | Non restauré |
| Flotte | `DBasic.fleet` (`/dfleet`) | `DB_FleetController` | Disposable Basic inactif | Module optionnel | Non restauré |
| Maintenance | `DSpecial.maintenance` (`/dmaintenance`) | `DS_MaintenanceController` | Disposable Special inactif | Module optionnel | Non restauré |
| Boutique du pilote | `promethee.shop` (`/shop`) | `PortalController@shop` | Authentifié | Présent | Conservé |
| HUB Transfer | `promethee.transfers` (`/transfers`) | `PortalController@transfers` | Authentifié | Renommé / fusionné | Conservé sous Transferts |
| Airline Transfer | `promethee.transfers` (`/transfers`) | `PortalController@transfers` | Authentifié | Renommé / fusionné | Conservé sous Transferts |
| Grades | `DBasic.ranks` (`/dranks`) | `DB_RankController` | Disposable Basic inactif | Module optionnel | Non restauré |
| Récompenses | — | module Awards sans route frontend | Awards actif mais logique seule | Module sans écran | Non restauré |
| Statistiques | `DBasic.statistics` (`/dstatistics`) | `DB_StatisticController` | Disposable Basic inactif | Module optionnel | Non restauré |
| Téléchargements | `frontend.downloads.index` (`/legacy/downloads`) | `DownloadController@index` | Authentifié | Présent uniquement en Legacy | Non restauré afin de ne pas exposer de parcours Legacy |
| Réservations (opérations) | `frontend.flights.bids` | `FlightController@bids` | Authentifié | Même vue personnelle | Accessible dans Espace pilote, pas dupliqué |
| Vol libre | `DSpecial.freeflight` (`/dfreeflight`) | `DS_FreeFlightController` | Disposable Special inactif | Module optionnel | Non restauré |
| Missions | `promethee.missions` (`/missions`) | `PortalController@missions` / `promethee::missions` | Authentifié | Présent | Conservé |
| Circuits | `promethee.missions` (`/missions`) | `PortalController@missions` | Authentifié | Renommé / fusionné | Conservé sous Missions et circuits |
| Nos HUBs | `DBasic.hubs` (`/dhubs`) | `DB_HubController` | Disposable Basic inactif | Module optionnel | Non restauré |
| Rapports des pilotes | `promethee.public.pireps` (`/public/pireps`) | `PortalController@publicPireps` | Authentifié | Présent mais absent | Restauré sous Mes rapports |
| Vols en temps réel | `promethee.live` (`/live`) | `PortalController@live` / `promethee::live` | Authentifié | Présent | Conservé |
| Météo en temps réel | `DBasic.livewx` (`/dlivewx`) | `DB_PageController@livewx` | Disposable Basic inactif | Module optionnel | Non restauré |
| Tableau de bord | `promethee.dashboard` (`/`) | `PortalController@dashboard` | Authentifié | Présent | Conservé dans Privé |
| Déconnexion | `auth.logout` (`/logout`) | `LoginController@logout` | Authentifié | Présent | Conservé dans Privé |

## Garde-fous appliqués

- Les routes Prométhée et phpVMS qui représentent des données personnelles restent derrière leur middleware `auth` existant.
- L’administration conserve `@ability('admin','admin-access')`; aucun lien administratif n’est montré aux pilotes standard.
- Les modules `DisposableBasic`, `DisposableSpecial` et `SPTransfer` sont présents dans `modules/`, mais leurs manifests ont `active: 0`. Aucun lien vers leurs routes n’est rendu.
- Les sections sont rendues côté serveur avec des routes nommées; la section parente est ouverte lorsque l’une de ses routes enfants est active.
