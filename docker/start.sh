PROJECT_NAME=alpacaphp

docker compose -p "$PROJECT_NAME" up --force-recreate -d && docker compose logs -f -p "$PROJECT_NAME" -t --tail=320