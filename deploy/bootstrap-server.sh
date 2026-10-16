#!/usr/bin/env bash
#
# Prepares a fresh Oracle Cloud Always Free VM to run TechScope.
# Run once, on the server, as the default user (ubuntu / opc).
#
#   curl -fsSL https://raw.githubusercontent.com/m7md-Y-3mra/techscope/main/deploy/bootstrap-server.sh | bash
#
# Idempotent — safe to re-run.

set -euo pipefail

log() { printf '\n\033[1m==> %s\033[0m\n' "$*"; }

if [[ $EUID -eq 0 ]]; then
	echo "Run as the normal user, not root. The script uses sudo where it needs to." >&2
	exit 1
fi

. /etc/os-release
log "Detected ${PRETTY_NAME}"

log "Installing Docker Engine and the Compose plugin"
if ! command -v docker >/dev/null; then
	case "$ID" in
		ubuntu|debian)
			sudo apt-get update -qq
			sudo apt-get install -y -qq ca-certificates curl gnupg git
			sudo install -m 0755 -d /etc/apt/keyrings
			curl -fsSL "https://download.docker.com/linux/${ID}/gpg" \
				| sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
			sudo chmod a+r /etc/apt/keyrings/docker.gpg
			echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/${ID} ${VERSION_CODENAME} stable" \
				| sudo tee /etc/apt/sources.list.d/docker.list >/dev/null
			sudo apt-get update -qq
			sudo apt-get install -y -qq docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
			;;
		ol|oracle|rhel|almalinux|rocky)
			sudo dnf install -y -q dnf-utils git
			sudo dnf config-manager --add-repo https://download.docker.com/linux/centos/docker-ce.repo
			sudo dnf install -y -q docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
			;;
		*)
			echo "Unsupported distribution: $ID. Install Docker manually, then re-run." >&2
			exit 1
			;;
	esac
else
	echo "Docker already present: $(docker --version)"
fi

sudo systemctl enable --now docker
sudo usermod -aG docker "$USER"

# Oracle's stock images ship a REJECT rule in the INPUT chain that silently
# drops 80/443 even after the VCN security list allows them. This is the single
# most common reason a new Oracle VM "cannot be reached" — fix both layers.
log "Opening ports 80 and 443 on the host firewall"
if command -v firewall-cmd >/dev/null && sudo systemctl is-active --quiet firewalld; then
	sudo firewall-cmd --permanent --add-service=http
	sudo firewall-cmd --permanent --add-service=https
	sudo firewall-cmd --reload
	echo "firewalld updated"
elif command -v iptables >/dev/null; then
	for port in 80 443; do
		if ! sudo iptables -C INPUT -p tcp --dport "$port" -m state --state NEW -j ACCEPT 2>/dev/null; then
			sudo iptables -I INPUT 6 -p tcp --dport "$port" -m state --state NEW -j ACCEPT
		fi
	done
	sudo apt-get install -y -qq iptables-persistent >/dev/null 2>&1 || true
	if command -v netfilter-persistent >/dev/null; then
		sudo netfilter-persistent save
		echo "iptables rules saved"
	else
		echo "WARNING: could not persist iptables rules; they will not survive a reboot." >&2
	fi
fi

# Always Free VMs ship with no swap. WordPress plus MariaDB survives without it,
# but an image-heavy upload or a plugin update is where a 1GB shape falls over.
if ! sudo swapon --show | grep -q .; then
	log "Adding 2G of swap"
	sudo fallocate -l 2G /swapfile
	sudo chmod 600 /swapfile
	sudo mkswap /swapfile >/dev/null
	sudo swapon /swapfile
	echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab >/dev/null
fi

log "Unattended security updates"
case "$ID" in
	ubuntu|debian)
		sudo apt-get install -y -qq unattended-upgrades
		sudo dpkg-reconfigure -f noninteractive unattended-upgrades
		;;
	ol|oracle|rhel|almalinux|rocky)
		sudo dnf install -y -q dnf-automatic
		sudo systemctl enable --now dnf-automatic.timer
		;;
esac

log "Done"
cat <<'NEXT'

Still to do, in this order:

  1. Log out and back in, so your shell picks up the `docker` group.
  2. In the Oracle Console, add ingress rules to the subnet's security list:
         0.0.0.0/0  TCP  80
         0.0.0.0/0  TCP  443
     The host firewall is now open, but the VCN blocks these by default.
  3. Point your domain's A record at this VM's public IP and wait for it
     to resolve, before starting Caddy. Let's Encrypt validates over HTTP,
     so a wrong DNS record means a failed certificate and a rate-limit hit.
  4. git clone https://github.com/m7md-Y-3mra/techscope.git
     cd techscope
     cp .env.prod.example .env    # then fill it in
     ./deploy/deploy.sh

NEXT
