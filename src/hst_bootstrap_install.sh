#!/bin/bash

# Clean installation bootstrap for development purposes only
# Usage:    ./hst_bootstrap_install.sh [organization] [branch] [os]
# Example:  ./hst_bootstrap_install.sh OrbixtarTechnologies main ubuntu

# Define variables
organization=${1:-OrbixtarTechnologies}
branch=$2
os=$3

# Download specified installer and compiler
wget https://raw.githubusercontent.com/$organization/orbixtar-panel/$branch/install/hst-install-$os.sh
wget https://raw.githubusercontent.com/$organization/orbixtar-panel/$branch/src/hst_autocompile.sh

# Execute compiler and build hestia core package
chmod +x hst_autocompile.sh
./hst_autocompile.sh --hestia $branch no

# Execute the OrbixPanel installer with default dummy options for testing
bash hst-install-$os.sh -f -y no -e admin@test.local -p P@ssw0rd -s hestia-$branch-$os.test.local --with-debs /tmp/hestiacp-src/debs
