<?php
// Script to update admin.php by removing country field references
$file = './public/admin.php';
$content = file_get_contents($file);

// Replace the problematic update match section
$pattern = '/\$data = \[\s*\'team1\' => \$_POST\[\'team1\'\] \?\? \'\',\s*\'team2\' => \$_POST\[\'team2\'\] \?\? \'\',\s*\'team1_country\' => \$_POST\[\'team1_flag\'\] \?\? \'gb\',\s*\'team2_country\' => \$_POST\[\'team2_flag\'\] \?\? \'gb\',/s';
$replacement = '$data = [
            \'team1\' => $_POST[\'team1\'] ?? \'\',
            \'team2\' => $_POST[\'team2\'] ?? \'\',';

$updated_content = preg_replace($pattern, $replacement, $content);

// Save the updated content back to the file
file_put_contents($file, $updated_content);
echo "Admin.php updated successfully!";
?>
