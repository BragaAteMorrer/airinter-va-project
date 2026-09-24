# Connecteurs simulateur

Le cœur ACARS dépend uniquement de l'interface `ISimulatorConnector` et du
modèle `AircraftSnapshot`, définis dans `SimulatorContracts.cs`.

Un nouveau connecteur doit :

1. déclarer un `SimulatorDescriptor` honnête, avec uniquement les capacités
   réellement exposées ;
2. ne publier que des `AircraftSnapshot` UTC et immuables ;
3. laisser les données indisponibles à `null` ;
4. ne jamais appeler l'API Prométhée ni attribuer de pénalité ;
5. rendre les déconnexions non fatales et libérer ses ressources dans `Dispose`.

Le moteur `FlightTrackingEngine` reçoit ces snapshots et produit des faits
opérationnels. La politique compagnie et le score restent exclusivement côté
Prométhée.

## État actuel

| Connecteur | Implémenté | Validé en vol réel |
| --- | --- | --- |
| SimConnectReader | oui, MSFS 2020/2024 | non |
| FSUIPC | oui, télémétrie FS2004/FSX/P3D | non |
| X-Plane UDP DataRef localhost | expérimental | non |

## Matrice

| Simulateur | Connecteur Hermès | Runtime côté simulateur | État |
| --- | --- | --- | --- |
| MSFS 2020/2024 | SimConnectReader | SimConnect | implémenté, non validé en vol réel |
| FS2004 | FsuipcConnector | FSUIPC3 3.999z9 | implémenté, non validé en vol réel |
| FSX / FSX Steam | FsuipcConnector | FSUIPC4 4.977 | implémenté, non validé en vol réel |
| Prepar3D 1–3 | FsuipcConnector | FSUIPC4 4.977 | implémenté, non validé en vol réel |
| Prepar3D 4–6 | FsuipcConnector | FSUIPC6 6.2.2 | implémenté, non validé en vol réel |
| X-Plane 11/12 | XPlaneUdpConnector | DataRefs UDP | expérimental, non validé en vol réel |

## FSUIPC

Hermès utilise `FSUIPCClientDLL 3.3.16` comme dépendance NuGet **uniquement
pour sa cible Windows**. Le package est un client IPC ; **FSUIPC lui-même n'est
pas embarqué ni redistribué dans Hermès**. Le pilote installe la version FSUIPC
correspondant à son simulateur.

Le connecteur lit notamment :

- position, altitude MSL et AGL ;
- IAS, vitesse sol, vitesse verticale, cap, pitch et bank ;
- quantité totale de carburant ;
- état sol, frein de parc, train et volets ;
- combustion des moteurs ;
- feux navigation/beacon/strobe/landing/taxi ;
- slew et spoilers armés ;
- titre de l'appareil.

Les offsets et leurs encodages sont confinés à
`FsuipcNativeSession.Windows.cs`. Ils ne doivent jamais remonter dans le cœur
ACARS. Une trame FSUIPC n'active le connecteur qu'après lecture et validation :
la simple présence du processus FS9/FSX/P3D n'est pas considérée comme une
connexion.

Une perte FSUIPC ferme la session locale et laisse le connecteur en état
réessayable. `SimulatorConnectorHub` conserve la sémantique de reconnexion :
un vol Hermès existant n'est pas recréé lors du retour de télémétrie.

## X-Plane

`XPlaneUdpConnector` envoie uniquement des requêtes RREF à
`127.0.0.1:49000` et n'écoute que les réponses loopback. Il ne contrôle jamais
le simulateur. Le pilote doit autoriser les DataRefs UDP dans X-Plane ;
l'absence de réponse laisse simplement le connecteur déconnecté.
