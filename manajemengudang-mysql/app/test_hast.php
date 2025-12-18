<?php
$plain = "admin123";
$hash = '$2y$10$y0PwCPJf4uXjW6dkqJdIieDz.XgChB4G5HgM0WsmhOrJ3eEFvS5bC';

if (password_verify($plain, $hash)) {
    echo "✅ Cocok!";
} else {
    echo "❌ Tidak cocok!";
}
