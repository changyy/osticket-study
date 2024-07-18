#!/bin/bash

# Function to check if a command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Check if Docker is installed
if ! command_exists docker; then
    echo "Docker is not installed. Please install Docker and try again."
    exit 1
fi

# Check if the operating system is macOS
if [[ "$(uname)" != "Darwin" ]]; then
    echo "This script is intended to run on macOS only."
    exit 1
fi

# Check if /tmp directory exists
if [ ! -d "./tmp" ]; then
    echo "Creating /tmp directory..."
    mkdir ./tmp
fi

# Check if ./tmp/osTicket directory exists
if [ ! -d "./tmp/osTicket" ]; then
    echo "./tmp/osTicket directory does not exist. Cloning repository..."

    # Use the first argument as the repository URL, or use the default URL if not provided
    REPO_URL="${1:-https://github.com/changyy/osTicket.git}"

    # Clone the repository into /tmp/osTicket
    git clone "$REPO_URL" ./tmp/osTicket

    if [ $? -ne 0 ]; then
        echo "Failed to clone the repository. Please check the URL and try again."
        exit 1
    fi
else
    echo "./tmp/osTicket directory already exists."
fi

# Check if ./tmp/osTicket-plugins directory exists
if [ ! -d "./tmp/osTicket-plugins" ]; then
    echo "./tmp/osTicket-plugins directory does not exist. Cloning repository..."

    # Use the first argument as the repository URL, or use the default URL if not provided
    REPO_URL="${2:-https://github.com/osTicket/osTicket-plugins.git}"

    # Clone the repository into /tmp/osTicket
    git clone "$REPO_URL" ./tmp/osTicket-plugins

    if [ $? -ne 0 ]; then
        echo "Failed to clone the repository. Please check the URL and try again."
        exit 1
    fi
else
    echo "./tmp/osTicket-plugins directory already exists."
fi

cp ./tmp/osTicket/include/ost-sampleconfig.php ./tmp/osTicket/include/ost-config.php

echo "Using DB Info:"
echo "  host: 127.0.0.1"
echo "  user: developer"
echo "  pass: 12345678"
echo

docker run -it -p 20022:22 -p 80:80 -p 3306:3306 -v ./tmp/osTicket:/var/www/osticket-develop osticket-dev:develop
