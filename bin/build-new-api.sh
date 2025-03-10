#!/usr/bin/env bash
set -e

if [ $# -lt 1 ]; then
    echo -e "${COLOR_RED}Provide a comma separated list of the APIs you want to rebuild ${NC}"
    exit 1;
fi

APIS_TO_REBUILD=$1

COLOR_RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if there's a new version
NEW_VERSION=$(curl -s -L https://www.worldcubeassociation.org/api/v0/export/public)
CURRENT_VERSION="`cat api/version.json 2>/dev/null`"

if [ "$NEW_VERSION" == "$CURRENT_VERSION" ]; then
    echo "No new version detected, exiting, bye."
    exit 0
fi

# Download and unzip WCA export.
rm -Rf wca-export
mkdir wca-export

echo "Downloading WCA export..."
curl https://www.worldcubeassociation.org/export/results/WCA_export.sql.zip -L --output "wca-export/export.zip"

echo "Unzipping WCA export..."
unzip wca-export/export.zip -d wca-export

# Import SQL file into db.
echo "Importing WCA export to database..."
# We need to remove the first line from the import file, because it causes MySQL to crash during import
tail -n +2 wca-export/WCA_export.sql > wca-export/tmp.sql && mv wca-export/tmp.sql wca-export/WCA_export.sql
# Now import
mysql --host="host.docker.internal" --user=root --password=root --port=3307 wca < wca-export/WCA_export.sql

# Add indexes for faster processing
mysql --host="host.docker.internal" --user=root --password=root --port=3307 wca -e "CREATE INDEX personId_index ON Persons (id)"
mysql --host="host.docker.internal" --user=root --password=root --port=3307 wca -e "CREATE INDEX personId_index ON Results (personId)"
mysql --host="host.docker.internal" --user=root --password=root --port=3307 wca -e "CREATE INDEX competitionId_index ON Results (competitionId)"
mysql --host="host.docker.internal" --user=root --password=root --port=3307 wca -e "CREATE INDEX eventId_index ON Results (eventId)"
mysql --host="host.docker.internal" --user=root --password=root --port=3307 wca -e "CREATE INDEX competitionId_index ON championships (competition_id)"
mysql --host="host.docker.internal" --user=root --password=root --port=3307 wca -e "CREATE INDEX personId_index ON RanksSingle (personId)"
mysql --host="host.docker.internal" --user=root --password=root --port=3307 wca -e "CREATE INDEX eventId_index ON RanksSingle (eventId)"
mysql --host="host.docker.internal" --user=root --password=root --port=3307 wca -e "CREATE INDEX personId_index ON RanksAverage (personId)"
mysql --host="host.docker.internal" --user=root --password=root --port=3307 wca -e "CREATE INDEX eventId_index ON RanksAverage (eventId)"

# Build API.
bin/console app:api:build $APIS_TO_REBUILD