#!/bin/bash
# 掃描 app/configs/ 底下的範例檔（.foo.php），把範例有、正式檔沒有的欄位補進正式檔。
# 正式檔本來就有的欄位一律不動；正式檔不存在時直接複製範例檔。
# Usage: scripts/config/builder.sh

set -euo pipefail

script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
configs_dir="$(cd "$script_dir/../../app/configs" && pwd)"

while IFS= read -r -d '' example; do
	dir="$(dirname "$example")"
	base="$(basename "$example")"
	real="$dir/${base#.}"

	if [ ! -f "$real" ]; then
		cp "$example" "$real"
		echo "Created: ${real#"$configs_dir"/} (copied from example file)"
		continue
	fi

	php "$script_dir/merge.php" build "$example" "$real"
done < <(find "$configs_dir" -type f -name '.*.php' -print0 | sort -z)
