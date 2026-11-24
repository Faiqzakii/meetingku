<?php if ($pager->getPageCount($group) > 1): ?>
    <nav class="card-section page-navigation" aria-label="<?= esc($label, 'attr') ?>">
        <?php if ($previous = $pager->getPreviousPageURI($group)): ?>
            <a class="btn btn-secondary" href="<?= esc($previous, 'attr') ?>" rel="prev">Sebelumnya</a>
        <?php endif; ?>
        <span>Halaman <?= $pager->getCurrentPage($group) ?> dari <?= $pager->getPageCount($group) ?></span>
        <?php if ($next = $pager->getNextPageURI($group)): ?>
            <a class="btn btn-secondary" href="<?= esc($next, 'attr') ?>" rel="next">Berikutnya</a>
        <?php endif; ?>
    </nav>
<?php endif; ?>
