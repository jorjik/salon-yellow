<?php
$file = 'c:/OSPanel/domains/land/salon/index.php';
$content = file_get_contents($file);

$replacements = [
    '#fff9f9' => '#fffdf4',
    '#d95f8a' => '#ffb300',
    '#b7476f' => '#e6a100',
    '#ffd9e6' => '#ffecb3',
    '#f1dde5' => '#ffe082',
    'rgba(177, 88, 126,' => 'rgba(230, 161, 0,',
    'rgba(217, 95, 138,' => 'rgba(255, 179, 0,',
    '#e06d95' => '#ffca28',
    '183, 71, 111' => '230, 161, 0',
    '#ad3f67' => '#e6a100',
    '#ab3f66' => '#e6a100',
    '#e5b7c8' => '#ffe082',
    '#fff8fb' => '#fff8e1',
    'rgba(239, 213, 224,' => 'rgba(255, 236, 179,',
    '#fff4f8' => '#fff8e1',
    '#9f4364' => '#cc8e00',
    '#ffe8f1' => '#fff8e1',
    'rgba(244, 217, 228,' => 'rgba(255, 243, 204,',
    'rgba(241, 221, 229,' => 'rgba(255, 243, 204,',
    '#e8c7d5' => '#ffca28',
    '#9f305a' => '#cc8e00',
    '#ffe6ef' => '#fff3e0',
    '#f5dce6' => '#ffecb3',
    '#eed3de' => '#ffecb3',
    '#e5c2d0' => '#ffe082',
    '#ffe2ed' => '#ffecb3',
    '#f4cfdd' => '#ffe082',
    '#deb8c9' => '#ffe082',
    '#efcedb' => '#ffecb3',
    'rgba(255, 210, 228,' => 'rgba(255, 235, 160,',
    'rgba(255, 236, 244,' => 'rgba(255, 245, 200,',
    'rgba(255, 248, 251,' => 'rgba(255, 250, 230,',
    '#fff0f5' => '#fffdf4',
];

$newContent = str_replace(array_keys($replacements), array_values($replacements), $content);
file_put_contents($file, $newContent);
echo "Replaced colors successfully.\n";
