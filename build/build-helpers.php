<?php

use MatthiasMullie\Minify;

/** recursive copy */
function rcopy(string $src, string $dst): void
{
    if (!is_dir($src)) return;
    if (!is_dir($dst)) mkdir($dst, 0775, true);
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($it as $item) {
        $rel = $it->getInnerIterator()->getSubPathname(); // e.g. "images/ui-icons_....png"
        $target = $dst . DIRECTORY_SEPARATOR . $rel;
        if ($item->isDir()) {
            if (!is_dir($target)) mkdir($target, 0775, true);
        } else {
            copy($item->getPathname(), $target);
        }
    }
}

/** post-process CSS file contents */
function css_replace(string $file, callable $replacer): void
{
    $css = file_get_contents($file);
    $css = $replacer($css);
    file_put_contents($file, $css);
}
