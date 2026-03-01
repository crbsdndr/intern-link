#!/usr/bin/env bash
set -Eeuo pipefail
PROJECT_ROOT="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
ENV_FILE="${PROJECT_ROOT}/.env"
ENV_EXAMPLE_FILE="${PROJECT_ROOT}/.env.example"
NON_INTERACTIVE=0
INSTALL_DEPS=0
USE_DOCKER_DB="auto"
SKIP_NPM=0
SKIP_MIGRATE=0
SKIP_SEED=0
START_SERVERS=0
DB_HOST="${DB_HOST:-}"
DB_PORT="${DB_PORT:-}"
DB_NAME="${DB_DATABASE:-${DB_NAME:-}}"
DB_USER="${DB_USERNAME:-${DB_USER:-}}"
DB_PASS="${DB_PASSWORD:-${DB_PASS:-}}"
DB_CONTAINER_NAME="${DB_CONTAINER_NAME:-internlink-db}"
DB_IMAGE="${DB_IMAGE:-postgres:16}"
CREATED_DB_CONTAINER=""
DEPS_INSTALL_ATTEMPTED=0
COMPOSER_CMD=()
log() { printf '[setup] %s\n' "$*"; }
warn() { printf '[setup][warn] %s\n' "$*" >&2; }
die() { printf '[setup][error] %s\n' "$*" >&2; exit 1; }
on_error() { warn "Script gagal di baris $1."; }
trap 'on_error ${LINENO}' ERR
command_exists() { command -v "$1" >/dev/null 2>&1; }
usage() {
  cat <<'EOF'
Usage: ./setup.sh [options]
Main: -y --install-deps --docker-db --no-docker-db --skip-npm --skip-migrate --skip-seed --serve
DB overrides: --db-host --db-port --db-name --db-user --db-password
Help: -h | --help
EOF
}
confirm() {
  local message="$1"
  if [[ "$NON_INTERACTIVE" -eq 1 ]]; then return 0; fi
  read -r -p "${message} [y/N]: " answer
  [[ "${answer,,}" == "y" || "${answer,,}" == "yes" ]]
}
detect_package_manager() {
  if command_exists brew; then echo "brew"; return; fi
  if command_exists apt-get; then echo "apt"; return; fi
  if command_exists dnf; then echo "dnf"; return; fi
  echo "unknown"
}
install_dependencies_if_requested() {
  if [[ "$INSTALL_DEPS" -ne 1 || "$DEPS_INSTALL_ATTEMPTED" -eq 1 ]]; then return; fi
  DEPS_INSTALL_ATTEMPTED=1
  local manager
  manager="$(detect_package_manager)"
  log "Mencoba install dependency via ${manager}..."
  case "$manager" in
    brew)
      brew update
      brew install php composer node postgresql docker
      ;;
    apt)
      sudo apt-get update
      sudo apt-get install -y php-cli php-mbstring php-xml php-curl php-zip php-pgsql composer nodejs npm postgresql-client docker.io
      ;;
    dnf)
      sudo dnf install -y php-cli php-common php-pdo php-pgsql php-mbstring php-xml php-curl php-zip composer nodejs npm postgresql docker
      ;;
    *)
      warn "Auto-install tidak didukung di OS ini."
      warn "Install manual: PHP 8.2+, Composer, Node.js, npm, PostgreSQL client, Docker (opsional)."
      ;;
  esac
}
require_command() {
  local cmd="$1" label="$2" hint="$3"
  if command_exists "$cmd"; then return; fi
  install_dependencies_if_requested
  command_exists "$cmd" || die "${label} tidak ditemukan. ${hint}"
}
read_env_value() {
  local key="$1" value
  value="$(grep -E "^${key}=" "$ENV_FILE" | tail -n1 | cut -d'=' -f2- || true)"
  value="${value%\"}"; value="${value#\"}"
  printf '%s' "$value"
}
set_env_value() {
  local key="$1" value="$2" tmp
  tmp="$(mktemp)"
  awk -v k="$key" -v v="$value" '
    BEGIN { done=0 }
    $0 ~ "^"k"=" { print k"="v; done=1; next }
    { print }
    END { if (!done) print k"="v }
  ' "$ENV_FILE" > "$tmp"
  mv "$tmp" "$ENV_FILE"
}
is_port_in_use() {
  local port="$1"
  if command_exists ss; then
    ss -ltn 2>/dev/null | awk '{print $4}' | grep -Eq "(^|:)${port}$"
    return $?
  fi
  if command_exists lsof; then
    lsof -nP -iTCP:"${port}" -sTCP:LISTEN >/dev/null 2>&1
    return $?
  fi
  return 1
}
find_free_port() {
  local start="$1" end="$2" port
  for ((port = start; port <= end; port++)); do
    if ! is_port_in_use "$port"; then echo "$port"; return 0; fi
  done
  return 1
}
resolve_composer_command() {
  if command_exists composer; then COMPOSER_CMD=("composer"); return; fi
  if [[ -f "${PROJECT_ROOT}/composer.phar" ]]; then COMPOSER_CMD=("php" "${PROJECT_ROOT}/composer.phar"); return; fi
  die "Composer tidak ditemukan (composer atau composer.phar)."
}
validate_config() {
  [[ -f "${PROJECT_ROOT}/artisan" ]] || die "Jalankan script dari root proyek Laravel."
  [[ -f "$ENV_EXAMPLE_FILE" ]] || die ".env.example tidak ditemukan."
  [[ "$DB_NAME" =~ ^[A-Za-z_][A-Za-z0-9_]*$ ]] || die "DB name tidak valid: ${DB_NAME}"
  [[ "$DB_USER" =~ ^[A-Za-z_][A-Za-z0-9_]*$ ]] || die "DB user tidak valid: ${DB_USER}"
  [[ "$DB_PORT" =~ ^[0-9]+$ ]] || die "DB port tidak valid: ${DB_PORT}"
}
ensure_php_extensions() {
  php -m | grep -Eq '^PDO$' || die "Extension PHP PDO belum aktif."
  php -m | grep -Eq '^pdo_pgsql$' || die "Extension PHP pdo_pgsql belum aktif."
}
docker_ready() { command_exists docker && docker info >/dev/null 2>&1; }
local_postgres_accessible() {
  command_exists psql || return 1
  PGPASSWORD="$DB_PASS" psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d postgres -tAc "SELECT 1" >/dev/null 2>&1
}
ensure_database_exists_with_psql() {
  local exists
  exists="$(PGPASSWORD="$DB_PASS" psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d postgres -tAc "SELECT 1 FROM pg_database WHERE datname='${DB_NAME}'" || true)"
  if [[ "$exists" != "1" ]]; then
    log "Database ${DB_NAME} belum ada. Membuat database..."
    PGPASSWORD="$DB_PASS" psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d postgres -v ON_ERROR_STOP=1 -c "CREATE DATABASE \"${DB_NAME}\" OWNER \"${DB_USER}\";"
  fi
}
start_docker_postgres() {
  docker_ready || die "Docker tidak siap. Pastikan Docker daemon berjalan."
  local selected_port="$DB_PORT"
  if is_port_in_use "$selected_port"; then
    selected_port="$(find_free_port 5433 5500)" || die "Tidak ada port kosong untuk PostgreSQL Docker."
    warn "Port ${DB_PORT} dipakai. Pakai port ${selected_port}."
  fi
  local name="$DB_CONTAINER_NAME"
  if docker ps -a --format '{{.Names}}' | grep -Fxq "$name"; then
    name="${DB_CONTAINER_NAME}-$(date +%s)"
    warn "Nama container bentrok. Pakai ${name}."
  fi
  docker run --name "$name" -e POSTGRES_DB="$DB_NAME" -e POSTGRES_USER="$DB_USER" -e POSTGRES_PASSWORD="$DB_PASS" -p "${selected_port}:5432" -d "$DB_IMAGE" >/dev/null
  local ready=0
  for _ in {1..30}; do
    if docker exec "$name" pg_isready -U "$DB_USER" -d "$DB_NAME" >/dev/null 2>&1; then ready=1; break; fi
    sleep 1
  done
  [[ "$ready" -eq 1 ]] || die "Container PostgreSQL gagal ready."
  CREATED_DB_CONTAINER="$name"; DB_HOST="127.0.0.1"; DB_PORT="$selected_port"
}
parse_args() {
  while (($#)); do
    case "$1" in
      -y|--yes) NON_INTERACTIVE=1 ;;
      --install-deps) INSTALL_DEPS=1 ;;
      --docker-db) USE_DOCKER_DB="always" ;;
      --no-docker-db) USE_DOCKER_DB="never" ;;
      --skip-npm) SKIP_NPM=1 ;;
      --skip-migrate) SKIP_MIGRATE=1 ;;
      --skip-seed) SKIP_SEED=1 ;;
      --serve) START_SERVERS=1 ;;
      --db-host|--db-port|--db-name|--db-user|--db-password)
        [[ $# -ge 2 ]] || die "Nilai untuk $1 belum diisi."
        case "$1" in
          --db-host) DB_HOST="$2" ;;
          --db-port) DB_PORT="$2" ;;
          --db-name) DB_NAME="$2" ;;
          --db-user) DB_USER="$2" ;;
          --db-password) DB_PASS="$2" ;;
        esac
        shift
        ;;
      -h|--help) usage; exit 0 ;;
      *) die "Opsi tidak dikenali: $1" ;;
    esac
    shift
  done
}
main() {
  parse_args "$@"
  cd "$PROJECT_ROOT"
  [[ -f "$ENV_FILE" ]] || cp "$ENV_EXAMPLE_FILE" "$ENV_FILE"
  [[ -n "$DB_HOST" ]] || DB_HOST="$(read_env_value DB_HOST)"
  [[ -n "$DB_PORT" ]] || DB_PORT="$(read_env_value DB_PORT)"
  [[ -n "$DB_NAME" ]] || DB_NAME="$(read_env_value DB_DATABASE)"
  [[ -n "$DB_USER" ]] || DB_USER="$(read_env_value DB_USERNAME)"
  [[ -n "$DB_PASS" ]] || DB_PASS="$(read_env_value DB_PASSWORD)"
  : "${DB_HOST:=127.0.0.1}"; : "${DB_PORT:=5432}"; : "${DB_NAME:=internlink}"
  : "${DB_USER:=internlink}"; : "${DB_PASS:=internlink123}"
  validate_config
  if [[ "$INSTALL_DEPS" -eq 1 ]] && ! confirm "Script akan install paket sistem yang hilang. Lanjut?"; then die "Dibatalkan."; fi
  require_command php "PHP" "Install PHP 8.2+."
  ensure_php_extensions
  resolve_composer_command
  if [[ "$SKIP_NPM" -eq 0 ]]; then
    require_command node "Node.js" "Install Node.js LTS."
    require_command npm "npm" "Install npm."
  fi
  if local_postgres_accessible; then
    log "PostgreSQL lokal terdeteksi."
    ensure_database_exists_with_psql
  else
    [[ "$USE_DOCKER_DB" != "never" ]] || die "Tidak bisa konek PostgreSQL lokal dengan kredensial saat ini."
    log "PostgreSQL lokal tidak siap. Menyiapkan PostgreSQL via Docker..."
    start_docker_postgres
  fi
  set_env_value DB_CONNECTION "pgsql"
  set_env_value DB_HOST "$DB_HOST"
  set_env_value DB_PORT "$DB_PORT"
  set_env_value DB_DATABASE "$DB_NAME"
  set_env_value DB_USERNAME "$DB_USER"
  set_env_value DB_PASSWORD "$DB_PASS"
  "${COMPOSER_CMD[@]}" install --no-interaction --prefer-dist
  if [[ "$SKIP_NPM" -eq 0 ]]; then
    if [[ -f "${PROJECT_ROOT}/package-lock.json" ]]; then npm ci; else npm install; fi
    npm run build
  fi
  php artisan key:generate --force
  php artisan config:clear
  [[ "$SKIP_MIGRATE" -eq 1 ]] || php artisan migrate --force
  [[ "$SKIP_SEED" -eq 1 ]] || php artisan db:seed --force
  if [[ "$START_SERVERS" -eq 1 ]]; then "${COMPOSER_CMD[@]}" run dev; exit 0; fi
  log "Setup selesai."
  log "Jalankan: php artisan serve"
  if [[ "$SKIP_NPM" -eq 0 ]]; then log "Jalankan juga: npm run dev"; fi
  if [[ -n "$CREATED_DB_CONTAINER" ]]; then log "Database Docker aktif: ${CREATED_DB_CONTAINER} (port ${DB_PORT})"; fi
}
main "$@"
