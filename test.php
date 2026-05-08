<?php $c = file_get_contents('resources/views/home.blade.php'); echo 'O:' . substr_count($c, '<div') . ' C:' . substr_count($c, '</div'); ?>
