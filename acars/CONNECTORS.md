# Connecteurs simulateur

Le cœur ACARS dépend uniquement de l'interface ISimulatorConnector et du
modèle AircraftSnapshot, définis dans SimulatorContracts.cs.

Un nouveau connecteur doit :

1. déclarer un SimulatorDescriptor honnête, avec uniquement les capacités
   réellement exposées ;
2. ne publier que des AircraftSnapshot UTC et immuables ;
3. laisser les données indisponibles à null ;
4. ne jamais appeler l'API Prométhée ni attribuer de pénalité ;
5. rendre les déconnexions non fatales et libérer ses ressources dans Dispose.

Le moteur FlightTrackingEngine reçoit ces snapshots et produit des faits
opérationnels. La politique compagnie et le score restent exclusivement côté
Prométhée.

Statut actuel :

| Connecteur | Implémenté | Validé |
| --- | --- | --- |
| SimConnectReader | oui, compatibilité MSFS existante | non, vol réel à effectuer |
| FSUIPC | non | non |
| X-Plane plugin localhost | non | non |

Matrice cible actualisée :

| Simulateur | Connecteur | État |
| --- | --- | --- |
| MSFS 2020/2024 | SimConnectReader | implémenté, non validé en vol réel |
| FS2004 | FSUIPC3 | non implémenté : runtime et kit client FSUIPC 32 bits requis |
| FSX / FSX Steam | FSUIPC4 | non implémenté : runtime et kit client FSUIPC requis |
| Prepar3D | FSUIPC4/5/6 selon version | non implémenté : runtime et kit client FSUIPC requis |
| X-Plane 11/12 | XPlaneUdpConnector | expérimental : DataRefs UDP locaux, non validé en vol réel |

XPlaneUdpConnector envoie uniquement des requêtes RREF à 127.0.0.1:49000
et n'écoute que les réponses loopback. Il ne contrôle jamais le simulateur.
Le pilote doit autoriser les DataRefs UDP dans X-Plane ; l'absence de réponse
laisse simplement le connecteur déconnecté.

FSUIPC ne doit pas être copié dans la publication ACARS sans examiner sa
licence. Le futur adaptateur devra utiliser le kit client correspondant au
runtime installé (FSUIPC3/4/5/6), puis être validé sur chaque famille de
simulateur avant de changer l'état de cette matrice.
