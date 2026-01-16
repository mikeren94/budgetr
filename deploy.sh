#!/bin/bash

echo "running deployment script"
rm -rf deploy_temp
mkdir deploy_temp

echo "copying files into deploy_temp"
# Copy everything into temp
cp -R core/. deploy_temp/budgeter

# Remove existing .env and replace with .env.live
rm -f deploy_temp/budgeter/.env
mv deploy_temp/budgeter/.env.live deploy_temp/budgeter/.env

echo "remoing public folder from deploy_temp"
# Remove public folder
rm -rf deploy_temp/budgeter/public
# Remove node modules from zip
rm -rf deploy_temp/budgeter/node_modules

echo "recreating public folder"
# Recreate public folder
mkdir deploy_temp/budgeter/public

# Copy manifest.json into Laravel's public/build
mkdir -p deploy_temp/budgeter/public/build
cp core/public/build/manifest.json deploy_temp/budgeter/public/build/manifest.json

# Copy assets into Hostinger's public_html/build
mkdir -p deploy_temp/budgeter/build
cp -R public_html/ deploy_temp/public_html
cp -R core/budgeter/public/build/assets deploy_temp/public_html/build/assets

echo "creating zip folder"
# Create zip
cd deploy_temp
zip -r ../deploy.zip .
cd ..

echo "removing deployment_temp"
# Cleanup
rm -rf deploy_temp

echo "Deployment zip created successfully."