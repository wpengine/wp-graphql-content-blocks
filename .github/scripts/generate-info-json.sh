#!/bin/bash
#
# Refreshes the release-time fields in the plugin's info.json manifest.
#
# Downloads the currently published manifest from the plugin update service,
# refreshes the release-time fields from readme.txt (the source of truth),
# and writes the result to ./info.json in the current working directory
# for upload as a GitHub Release asset.
#
# If no manifest exists at UPDATE_URL yet (first release / new slug), a minimal
# skeleton is bootstrapped automatically.
#
# Environment overrides (with defaults for this repo):
#   SLUG             plugin slug              (default: wp-graphql-content-blocks)
#   README           path to readme.txt       (default: readme.txt)
#   UPDATE_URL       base URL to fetch the seed manifest from
#                    (default: https://wpe-plugin-updates.wpengine.com/${SLUG})
#   DOWNLOAD_BASE_URL base URL written into download_link / versions entries
#                    (default: same as UPDATE_URL)
#
# Usage: bash generate-info-json.sh <version>

set -euo pipefail

VERSION="${1:?usage: $0 <version>}"
SLUG="${SLUG:-wp-graphql-content-blocks}"
README="${README:-readme.txt}"
UPDATE_URL="${UPDATE_URL:-https://wpe-plugin-updates.wpengine.com/${SLUG}}"
DOWNLOAD_BASE_URL="${DOWNLOAD_BASE_URL:-${UPDATE_URL}}"

if [ ! -f "$README" ]; then
	echo "::error::readme.txt not found at $README" >&2
	exit 1
fi

# Fetch the current manifest; bootstrap a skeleton on first release (404).
http_status=$(curl --silent --show-error --location --write-out "%{http_code}" \
	"${UPDATE_URL}/info.json" \
	--output info.json)

if [ "$http_status" = "404" ] || [ "$http_status" = "000" ]; then
	echo "No existing manifest found (HTTP ${http_status}); bootstrapping skeleton."
	printf '{"name":"%s","slug":"%s","version":"","download_link":"","sections":{},"versions":{}}' \
		"$SLUG" "$SLUG" > info.json
elif [ "$http_status" != "200" ]; then
	echo "::error::Failed to fetch manifest (HTTP ${http_status})" >&2
	exit 1
fi

# date %p emits uppercase AM/PM on Linux; the manifest convention is lowercase.
current_time=$(LC_ALL=C date -u +"%Y-%m-%d %-I:%M%p GMT" | sed -e 's/AM/am/' -e 's/PM/pm/')
download_link="${DOWNLOAD_BASE_URL}/${SLUG}.${VERSION}.zip"

# Sync compatibility headers from readme.txt — empty value skips the update.
tested=$(sed -nE 's/^Tested up to:[[:space:]]+([^[:space:]]+).*/\1/p' "$README" | head -1 | tr -d '\r')
requires=$(sed -nE 's/^Requires at least:[[:space:]]+([^[:space:]]+).*/\1/p' "$README" | head -1 | tr -d '\r')
requires_php=$(sed -nE 's/^Requires PHP:[[:space:]]+([^[:space:]]+).*/\1/p' "$README" | head -1 | tr -d '\r')

# Sync the changelog from readme.txt so the manifest reflects the current
# release notes. readme.txt is the source of truth and is already trimmed to
# the recent versions with a "View the full changelog" link. Capture the body
# of the "== Changelog ==" section, then trim surrounding whitespace. Empty
# value skips the update.
changelog=$(awk '
	/^== Changelog ==/ { capture = 1; next }
	capture && /^== / { capture = 0 }
	capture { body = body $0 "\n" }
	END {
		gsub(/^[ \t\r\n]+/, "", body)
		gsub(/[ \t\r\n]+$/, "", body)
		printf "%s", body
	}
' "$README")

jq \
	--arg version "$VERSION" \
	--arg last_updated "$current_time" \
	--arg download_link "$download_link" \
	--arg new_version "$VERSION" \
	--arg new_download_link "$download_link" \
	--arg tested "$tested" \
	--arg requires "$requires" \
	--arg requires_php "$requires_php" \
	--arg changelog "$changelog" \
	'
		.versions[$new_version] = $new_download_link
		| .version = $version
		| .last_updated = $last_updated
		| .download_link = $download_link
		| if $tested != "" then .tested = $tested else . end
		| if $requires != "" then .requires = $requires else . end
		| if $requires_php != "" then .requires_php = $requires_php else . end
		| if $changelog != "" then .sections.changelog = $changelog else . end
	' \
	info.json > info.json.tmp && mv info.json.tmp info.json
