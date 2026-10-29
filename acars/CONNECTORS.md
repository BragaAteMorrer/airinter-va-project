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
| FSUIPC | oui, fallback FSUIPC7 pour MSFS + télémétrie FS2004/FSX/P3D | non |
| X-Plane UDP DataRef localhost | expérimental | non |

## Matrice

| Simulateur | Connecteur Hermès | Runtime côté simulateur | État |
| --- | --- | --- | --- |
| MSFS 2020/2024 | SimConnectReader (prioritaire) + FsuipcConnector (fallback) | SimConnect ou FSUIPC7 | implémenté, non validé en vol réel |
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
la simple présence du processus MSFS/FS9/FSX/P3D n'est pas considérée comme une
connexion.

Sous MSFS 2020 comme sous MSFS 2024, Hermès essaie d'abord SimConnect. Si
SimConnect ne produit pas de télémétrie mais que FSUIPC7 est lancé et fournit
une trame valide, le hub bascule automatiquement sur le connecteur FSUIPC7 sans
redémarrer Hermès. Les variantes de processus Microsoft Store/Xbox et Steam
connues sont couvertes par la détection, mais la connexion n'est considérée
active qu'après réception de télémétrie réelle.

Une perte FSUIPC ferme la session locale et laisse le connecteur en état
réessayable. `SimulatorConnectorHub` conserve la sémantique de reconnexion :
un vol Hermès existant n'est pas recréé lors du retour de télémétrie.

## X-Plane

XPlaneUdpConnector utilise le protocole RREF en lecture seule sur
127.0.0.1:49000 (machine locale uniquement). Les requêtes ont une taille
fixe de 413 octets : en-tête RREF\0, fréquence et index little-endian, puis
nom DataRef sur 400 octets. Les réponses RREF, (XP11/12) et RREF\0 (ancien)
sont acceptées. L'IAS est déjà en nœuds ; la vitesse sol est en m/s et est
convertie en nœuds. Une donnée manquante, invalide ou périmée reste inconnue.

### Vérification de la liaison X-Plane

1. Démarrer X-Plane 11 ou 12 **sur la même machine** qu'Hermès.
2. Vérifier l'accès local UDP au port 49000 (y compris le pare-feu ou un
   éventuel port personnalisé dans X-Plane). Pas de plugin tiers requis
   pour le protocole RREF de base.
3. Ouvrir les diagnostics Hermès : dans \`connectors\`, l'entrée \`xplane-udp\`
   doit passer de Connecting à Connected, avec un \`lastSnapshotAt\` récent.
4. Vérifier qu'une latitude, une longitude et une altitude **valides** sont
   reçues. Si la connexion est établie mais que Prométhée affiche
   \`NO_SIGNAL\`, contrôler ensuite l'opération active, l'enregistrement
   ACARS, \`presenceLastHeartbeatAt\`, \`presenceError\`,
   \`syncState\` et \`syncError\` : UDP n'est qu'une étape du flux.
5. Après une coupure du simulateur, la liaison passe en reconnexion et
   doit reprendre sans redémarrer Hermès, sans injecter d'anciennes positions.

La liaison XP11/XP12 demeure expérimentale tant qu'aucun vol de validation
réel n'a confirmé les DataRefs, le cycle complet OFP/PIREP et la remontée
dans Prométhée.
