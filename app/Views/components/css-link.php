<?php
$linkClasses = trim('css-link ' . ($className ?? ''));
$iconMarkup = isset($icon) && $icon !== ''
    ? '<span aria-hidden="true">' . esc($icon) . '</span>'
    : '';
?>
<a class="<?= esc($linkClasses, 'attr') ?>" href="<?= esc($url, 'attr') ?>"><?= esc($label) ?><?= $iconMarkup ?></a>
