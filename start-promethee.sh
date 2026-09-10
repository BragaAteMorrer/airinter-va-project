#!/usr/bin/env bash
set -euo pipefail
cd -- "$(dirname -- "$0")"
docker compose -f compose.promethee.yml up --build -d
printf '\nPromethee : http://localhost:8088/promethee\nPromethee ACARS : http://127.0.0.1:1974\nCompte local : admin@promethee.test / promethee-local\nCle API locale : docker compose -f compose.promethee.yml logs web\n'
