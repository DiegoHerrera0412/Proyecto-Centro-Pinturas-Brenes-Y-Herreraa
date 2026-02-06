<?php
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function money_crc($n): string {
  $n = (float)$n;
  return '₡' . number_format($n, 2, '.', ',');
}
