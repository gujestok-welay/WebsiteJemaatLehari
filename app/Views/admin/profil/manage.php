<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$pageDefaults = \App\Libraries\PublicProfileContent::defaults();
$pageContent = is_array($pageContent ?? null)
    ? \App\Libraries\PublicProfileContent::mergeForDisplay($pageDefaults, $pageContent)
    : $pageDefaults;
$legacyContent = $pageContent;
unset($legacyContent['territory'], $legacyContent['seasons'], $legacyContent['census'], $legacyContent['worship']);
$profile = is_array($profile ?? null) ? $profile : [];
$periods = is_array($periods ?? null) ? $periods : [];
$members = is_array($members ?? null) ? $members : [];
$readOnlyFields = [
    'content[stats][0][value]',
    'content[stats][1][value]',
];
?>
<section class="profile-manage-page" aria-label="Pengelolaan profil jemaat" data-profile-editor-page data-profile-editor-url="<?= esc(current_url(), 'attr') ?>">
    <header class="profile-manage-heading">
        <a class="profile-manage-back" href="<?= site_url('admin/profil') ?>" data-profile-leave>
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            <span>Kembali ke Profil</span>
        </a>
    </header>

    <?php if (! empty($success)): ?>
        <div class="news-alert news-alert-success" role="status"><?= esc($success) ?></div>
    <?php endif; ?>
    <?php if (! empty($error)): ?>
        <div class="news-alert news-alert-error" role="alert"><?= esc($error) ?></div>
    <?php endif; ?>
    <div class="profile-live-region" data-profile-live role="status" aria-live="polite" aria-atomic="true"></div>

    <?= view('admin/profil/_module-editors', [
        'editorModule' => $editorModule,
        'profile' => $profile,
        'pageContent' => $pageContent,
        'legacyContent' => $legacyContent,
        'pageDefaults' => $pageDefaults,
        'readOnlyFields' => $readOnlyFields,
        'periods' => $periods,
        'selectedPeriod' => $selectedPeriod,
        'members' => $members,
        'editPeriod' => $editPeriod ?? null,
        'editMember' => $editMember ?? null,
        'serviceMapPath' => $serviceMapPath,
        'serviceMapTitle' => $serviceMapTitle,
        'serviceMapAlt' => $serviceMapAlt,
        'totalJiwa' => $totalJiwa,
    ]) ?>
</section>
<?= $this->endSection() ?>
