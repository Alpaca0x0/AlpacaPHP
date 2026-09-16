#!/bin/bash
# 掃描 app/configs/ 底下的範例檔（.foo.php），把正式檔裡範例已經沒有的多餘欄位刪掉。
# 正式檔不存在時略過（沒有東西可清）。
# Usage: scripts/config/clean.sh

set -euo pipefail

script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
configs_dir="$(cd "$script_dir/../../app/configs" && pwd)"

while IFS= read -r -d '' example; do
	dir="$(dirname "$example")"
	base="$(basename "$example")"
	real="$dir/${base#.}"

	if [ ! -f "$real" ]; then
		continue
	fi

	php "$script_dir/merge.php" clean "$example" "$real"
done < <(find "$configs_dir" -type f -name '.*.php' -print0 | sort -z)
