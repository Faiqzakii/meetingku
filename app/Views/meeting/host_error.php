<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div style="max-width:520px;margin:60px auto;text-align:center;">
    <div style="font-size:48px;margin-bottom:16px;color:var(--error);">&#9888;</div>
    <h2 style="margin-bottom:12px;color:var(--error);">Link Tidak Tersedia</h2>
    <p style="color:var(--mute);font-size:.95rem;line-height:1.6;">
        <?= $message ?>
    </p>
</div>
<?= $this->endSection() ?>