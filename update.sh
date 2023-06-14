#!/bin/bash

pull_from_github=false

# Check if the first argument is "y"
if [ "$1" = "y" ]; then
    pull_from_github=true
fi

# Prompt if no argument is supplied
if [ $# -eq 0 ]; then
    read -p "Do you want to pull from the GitHub repo before updating?: " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        pull_from_github=true
    fi
fi

if [ "$pull_from_github" = true ]; then
    cd ~/snipe-it/
    git pull
fi

# Perform the file copy operation
sudo cp -r ~/snipe-it/resources/* /var/www/html/snipeit/resources/

# Check the exit status of the cp command
if [ $? -eq 0 ]; then
    echo "File copy successful."
    sudo systemctl restart apache2.service
else
    echo "File copy failed."
fi
