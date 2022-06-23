<?php
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/tmp/')) {
    foreach (glob($_SERVER['DOCUMENT_ROOT'] . '/tmp/*') as $file) {
        unlink($file);
    }
}