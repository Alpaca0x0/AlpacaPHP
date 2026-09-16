<?php
// 比對 config 範例檔（.foo.php）與正式檔（foo.php）的頂層/巢狀陣列 key。
// 只做 tokenize，不 include/evaluate 檔案，因此正式檔裡的常數（DOMAIN）、
// 函式呼叫（Uri::page(...)）等都能原樣保留，不會因為在 CLI 環境找不到而炸掉。
// 只支援 short array syntax `[...]`。

function tokenizeWithOffsets(string $code): array {
	$raw = token_get_all($code);
	$offset = 0;
	$tokens = [];
	foreach ($raw as $t) {
		if (is_array($t)) {
			[$id, $text] = $t;
		} else {
			$id = null;
			$text = $t;
		}
		$tokens[] = ['id' => $id, 'text' => $text, 'start' => $offset, 'end' => $offset + strlen($text)];
		$offset += strlen($text);
	}
	return $tokens;
}

function isSkippable(array $tok): bool {
	return $tok['id'] === T_WHITESPACE || $tok['id'] === T_COMMENT || $tok['id'] === T_DOC_COMMENT;
}

function findRootArrayStart(array $tokens): int {
	$n = count($tokens);
	for ($i = 0; $i < $n; $i++) {
		if ($tokens[$i]['id'] !== T_RETURN) continue;
		for ($j = $i + 1; $j < $n; $j++) {
			if (isSkippable($tokens[$j])) continue;
			if ($tokens[$j]['text'] === '[') return $j;
			break;
		}
	}
	throw new RuntimeException("Could not find 'return [ ... ];'");
}

function matchingBracket(array $tokens, int $openIdx): int {
	$depth = 0;
	for ($i = $openIdx, $n = count($tokens); $i < $n; $i++) {
		$text = $tokens[$i]['text'];
		if ($text === '[') $depth++;
		elseif ($text === ']' && --$depth === 0) return $i;
	}
	throw new RuntimeException('Array brackets do not match');
}

function lastSignificantIndexBefore(array $tokens, int $idx): ?int {
	for ($i = $idx - 1; $i >= 0; $i--) {
		if (!isSkippable($tokens[$i])) return $i;
	}
	return null;
}

// 解析從 $segStart 到 $segEnd（不含）的一段 entry：可能是 'key' => value，也可能是純值（list item）。
// $delimEndOffset 是這個 entry 結尾逗號的結束位置；如果是陣列最後一個、沒有逗號的 entry 則傳 null。
function parseEntry(array $tokens, string $code, int $segStart, int $segEnd, ?int $delimEndOffset): ?array {
	$sigIdx = [];
	for ($i = $segStart; $i < $segEnd; $i++) {
		if (!isSkippable($tokens[$i])) $sigIdx[] = $i;
	}
	if (empty($sigIdx)) return null;

	$key = null;
	$valueSig = $sigIdx;
	if (count($sigIdx) >= 2
		&& $tokens[$sigIdx[0]]['id'] === T_CONSTANT_ENCAPSED_STRING
		&& $tokens[$sigIdx[1]]['text'] === '=>'
	) {
		$key = substr($tokens[$sigIdx[0]]['text'], 1, -1);
		$valueSig = array_slice($sigIdx, 2);
	}

	$lineStart = 0;
	$nl = strrpos(substr($code, 0, $tokens[$sigIdx[0]]['start']), "\n");
	if ($nl !== false) $lineStart = $nl + 1;

	$valueStartOffset = $tokens[$valueSig[0]]['start'];
	$valueEndOffset = $tokens[end($valueSig)]['end'];

	$child = null;
	if ($tokens[$valueSig[0]]['text'] === '[' && matchingBracket($tokens, $valueSig[0]) === end($valueSig)) {
		$childNode = parseArray($tokens, $code, $valueSig[0]);
		if ($childNode['isAssoc']) $child = $childNode;
	}

	return [
		'key' => $key,
		'raw' => substr($code, $valueStartOffset, $valueEndOffset - $valueStartOffset),
		'child' => $child,
		'lineStart' => $lineStart,
		'valueEndOffset' => $valueEndOffset,
		'delimEndOffset' => $delimEndOffset,
	];
}

function parseArray(array $tokens, string $code, int $openIdx): array {
	$closeIdx = matchingBracket($tokens, $openIdx);
	$entries = [];
	$depth = 1;
	$segStart = $openIdx + 1;
	for ($i = $openIdx + 1; $i < $closeIdx; $i++) {
		$text = $tokens[$i]['text'];
		if ($text === '[') $depth++;
		elseif ($text === ']') $depth--;
		elseif ($text === ',' && $depth === 1) {
			$e = parseEntry($tokens, $code, $segStart, $i, $tokens[$i]['end']);
			if ($e !== null) $entries[] = $e;
			$segStart = $i + 1;
		}
	}
	if ($segStart < $closeIdx) {
		$e = parseEntry($tokens, $code, $segStart, $closeIdx, null);
		if ($e !== null) $entries[] = $e;
	}

	$isAssoc = count($entries) > 0;
	foreach ($entries as $e) {
		if ($e['key'] === null) { $isAssoc = false; break; }
	}

	$hasTrailingComma = false;
	$lastSig = lastSignificantIndexBefore($tokens, $closeIdx);
	if ($lastSig !== null && $tokens[$lastSig]['text'] === ',') $hasTrailingComma = true;

	return [
		'closeOffset' => $tokens[$closeIdx]['start'],
		'entries' => $entries,
		'isAssoc' => $isAssoc,
		'hasTrailingComma' => $hasTrailingComma,
	];
}

function parseConfigFile(string $path): array {
	$code = file_get_contents($path);
	$tokens = tokenizeWithOffsets($code);
	$root = parseArray($tokens, $code, findRootArrayStart($tokens));
	return ['code' => $code, 'root' => $root];
}

function detectIndentUnit(array $root, string $code): string {
	foreach ($root['entries'] as $e) {
		$lineEnd = strpos($code, "\n", $e['lineStart']);
		if ($lineEnd === false) $lineEnd = strlen($code);
		if (preg_match('/^([ \t]*)/', substr($code, $e['lineStart'], $lineEnd - $e['lineStart']), $m)) return $m[1];
	}
	return '    ';
}

function detectLineEnding(string $code): string {
	return strpos($code, "\r\n") !== false ? "\r\n" : "\n";
}

function keyedEntries(array $node): array {
	$map = [];
	foreach ($node['entries'] as $e) {
		if ($e['key'] !== null) $map[$e['key']] = $e;
	}
	return $map;
}

function renderEntry(array $node, int $level, string $unit, string $eol): string {
	$indent = str_repeat($unit, $level);
	if ($node['child'] !== null) {
		$lines = [$indent . "'" . $node['key'] . "' => ["];
		foreach ($node['child']['entries'] as $c) {
			$lines[] = renderEntry($c, $level + 1, $unit, $eol);
		}
		$lines[] = $indent . '],';
		return implode($eol, $lines);
	}
	return $indent . "'" . $node['key'] . "' => " . $node['raw'] . ',';
}

// 找出範例節點有、正式節點缺的 key，回傳要插入的操作列表（遞迴處理巢狀陣列）。
// 插入位置照範例順序：一段連續缺漏的欄位，插在「範例中下一個正式檔也有的 key」之前；
// 若缺漏一路到節點結尾，就沿用原本 append 在節點尾端的行為。
function collectAdditions(array $exNode, array $realNode, int $level, string $unit, string $eol): array {
	$ops = [];
	$realKeyed = keyedEntries($realNode);
	$pending = [];
	foreach ($exNode['entries'] as $exChild) {
		if ($exChild['key'] === null) continue;
		if (!array_key_exists($exChild['key'], $realKeyed)) {
			$pending[] = $exChild;
			continue;
		}
		$realChild = $realKeyed[$exChild['key']];
		if (!empty($pending)) {
			$ops[] = [
				'insertOffset' => $realChild['lineStart'],
				'commaFixOffset' => null,
				'text' => implode($eol, array_map(fn($e) => renderEntry($e, $level + 1, $unit, $eol), $pending)),
				'eol' => $eol,
			];
			$pending = [];
		}
		if ($exChild['child'] !== null && $realChild['child'] !== null) {
			$ops = array_merge($ops, collectAdditions($exChild['child'], $realChild['child'], $level + 1, $unit, $eol));
		}
	}
	if (!empty($pending)) {
		$lastEntry = empty($realNode['entries']) ? null : end($realNode['entries']);
		$ops[] = [
			'closeOffset' => $realNode['closeOffset'],
			'commaFixOffset' => ($lastEntry !== null && !$realNode['hasTrailingComma']) ? $lastEntry['valueEndOffset'] : null,
			'text' => implode($eol, array_map(fn($e) => renderEntry($e, $level + 1, $unit, $eol), $pending)),
			'eol' => $eol,
		];
	}
	return $ops;
}

function applyAdditions(string $code, array $ops): string {
	usort($ops, fn($a, $b) => ($b['closeOffset'] ?? $b['insertOffset']) <=> ($a['closeOffset'] ?? $a['insertOffset']));
	foreach ($ops as $op) {
		if (isset($op['closeOffset'])) {
			$lineStart = 0;
			$nl = strrpos(substr($code, 0, $op['closeOffset']), "\n");
			if ($nl !== false) $lineStart = $nl + 1;
		} else {
			$lineStart = $op['insertOffset'];
		}
		$code = substr($code, 0, $lineStart) . $op['text'] . $op['eol'] . substr($code, $lineStart);
		if ($op['commaFixOffset'] !== null) {
			$code = substr($code, 0, $op['commaFixOffset']) . ',' . substr($code, $op['commaFixOffset']);
		}
	}
	return $code;
}

// 找出正式節點有、範例節點沒有的 key，回傳要刪除的 [start, end) 區間（遞迴處理巢狀陣列）。
function collectRemovals(array $exNode, array $realNode, string $code): array {
	$spans = [];
	$exKeyed = keyedEntries($exNode);
	foreach ($realNode['entries'] as $realChild) {
		if ($realChild['key'] === null) continue;
		if (!array_key_exists($realChild['key'], $exKeyed)) {
			$end = $realChild['delimEndOffset'] ?? $realChild['valueEndOffset'];
			if (($code[$end] ?? '') === "\r") $end++;
			if (($code[$end] ?? '') === "\n") $end++;
			$spans[] = [$realChild['lineStart'], $end];
			continue;
		}
		$exChild = $exKeyed[$realChild['key']];
		if ($exChild['child'] !== null && $realChild['child'] !== null) {
			$spans = array_merge($spans, collectRemovals($exChild['child'], $realChild['child'], $code));
		}
	}
	return $spans;
}

function applyRemovals(string $code, array $spans): string {
	usort($spans, fn($a, $b) => $b[0] <=> $a[0]);
	foreach ($spans as [$start, $end]) {
		$code = substr($code, 0, $start) . substr($code, $end);
	}
	return $code;
}

// 寫檔前先用 php -l 驗證語法，避免解析邏輯有誤把正式 config 寫壞。
function writeIfValid(string $path, string $code): void {
	$tmp = $path . '.tmp';
	file_put_contents($tmp, $code);
	exec('php -l ' . escapeshellarg($tmp) . ' 2>&1', $out, $exit);
	if ($exit !== 0) {
		unlink($tmp);
		fwrite(STDERR, "Syntax check failed, not written to $path:\n" . implode("\n", $out) . "\n");
		exit(1);
	}
	rename($tmp, $path);
}

function cmdBuild(string $examplePath, string $realPath): void {
	$ex = parseConfigFile($examplePath);
	$real = parseConfigFile($realPath);
	$unit = detectIndentUnit($real['root'], $real['code']);
	$eol = detectLineEnding($real['code']);
	$ops = collectAdditions($ex['root'], $real['root'], 0, $unit, $eol);
	if (empty($ops)) {
		echo "PASS: $realPath\n";
		return;
	}
	writeIfValid($realPath, applyAdditions($real['code'], $ops));
	$added = 0;
	foreach ($ops as $op) $added += substr_count($op['text'], $eol) + 1;
	echo "Added $added field(s): $realPath\n";
}

function cmdClean(string $examplePath, string $realPath): void {
	$ex = parseConfigFile($examplePath);
	$real = parseConfigFile($realPath);
	$spans = collectRemovals($ex['root'], $real['root'], $real['code']);
	if (empty($spans)) {
		echo "OK (no extra fields): $realPath\n";
		return;
	}
	writeIfValid($realPath, applyRemovals($real['code'], $spans));
	echo "Removed " . count($spans) . " field(s): $realPath\n";
}

$mode = $argv[1] ?? null;
$examplePath = $argv[2] ?? null;
$realPath = $argv[3] ?? null;
if (!in_array($mode, ['build', 'clean'], true) || !$examplePath || !$realPath) {
	fwrite(STDERR, "Usage: php merge.php <build|clean> <example.php> <real.php>\n");
	exit(1);
}
if ($mode === 'build') cmdBuild($examplePath, $realPath);
else cmdClean($examplePath, $realPath);
