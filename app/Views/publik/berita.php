<?= $this->extend('layouts/publik') ?>
<?= $this->section('content') ?>

<div class="berita-page">
    <section class="berita-hero text-white">
        <div class="container py-5">
            <h1 class="display-6 fw-bold mb-2">Berita &amp; Peristiwa</h1>
            <p class="lead mb-0 col-lg-7">Kabar terbaru kegiatan dan pelayanan Jemaat GPM Leahari.</p>
        </div>
    </section>

    <section class="container py-5 berita-shell">
        <?php
        $categories = [];
        foreach ($news as $item) {
            $category = trim((string) ($item['category'] ?? ''));
            if ($category !== '') {
                $categories[$category] = true;
            }
        }
        ?>

        <?php if (empty($news)): ?>
            <p class="text-center text-body-secondary py-5">Belum ada berita yang diterbitkan.</p>
        <?php else: ?>
            <?php if (! empty($categories)): ?>
                <div class="d-flex flex-wrap gap-2 mb-4" id="filter-kategori">
                    <button type="button" class="btn btn-sm rounded-pill btn-primary" data-kategori="all">Semua</button>
                    <?php foreach (array_keys($categories) as $nama): ?>
                        <button type="button" class="btn btn-sm rounded-pill btn-outline-secondary" data-kategori="<?= esc($nama, 'attr') ?>"><?= esc($nama) ?></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="row g-4" id="daftar-berita">
                <?php foreach ($news as $index => $item):
                    $categoryName = (string) ($item['category'] ?? 'Berita');
                ?>
                    <div class="col-md-6 col-lg-4 item-berita" data-kategori="<?= esc($categoryName, 'attr') ?>">
                        <?= view('components/berita-card', [
                            'item' => $item,
                            'index' => $index,
                            'isSample' => ! empty($isSample),
                        ]) ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (! empty($categories)): ?>
                <p id="berita-kosong" class="text-center text-body-secondary py-5 d-none">Belum ada berita pada kategori ini.</p>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</div>

<script>
(function () {
    var buttons = document.querySelectorAll('#filter-kategori button');
    var items = document.querySelectorAll('.item-berita');
    var empty = document.getElementById('berita-kosong');

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            var selected = button.dataset.kategori;
            var visible = 0;

            buttons.forEach(function (btn) {
                var active = btn === button;
                btn.classList.toggle('btn-primary', active);
                btn.classList.toggle('btn-outline-secondary', !active);
            });

            items.forEach(function (item) {
                var match = selected === 'all' || item.dataset.kategori === selected;
                item.classList.toggle('d-none', !match);
                if (match) visible++;
            });

            if (empty) {
                empty.classList.toggle('d-none', visible > 0);
            }
        });
    });
})();
</script>

<?= $this->endSection() ?>
