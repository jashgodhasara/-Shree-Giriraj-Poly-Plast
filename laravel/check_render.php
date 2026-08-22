<?php
$html = file_get_contents('https://shreegiriraj-erp.onrender.com');
echo "HTML Length: " . strlen($html) . "\n";
echo "Has topbar-desktop-only: " . (strpos($html, 'topbar-desktop-only') !== false ? 'YES' : 'NO') . "\n";
echo "Has viewport-fit=cover: " . (strpos($html, 'viewport-fit=cover') !== false ? 'YES' : 'NO') . "\n";
if (preg_match('/<title>(.*?)<\/title>/', $html, $m)) {
    echo "Title: " . $m[1] . "\n";
}
