<?php
$scheduleName = (string) ($schedule['name'] ?? '');
$scheduleDate = (string) ($schedule['date_label'] ?? $schedule['day_label'] ?? '');
$scheduleTime = (string) ($schedule['time'] ?? '');
$scheduleLocation = (string) ($schedule['location'] ?? '');
?>
<div class="schedule-share" data-schedule-share-wrap>
    <button
        class="schedule-share-button"
        type="button"
        aria-label="<?= esc('Bagikan jadwal: ' . $scheduleName, 'attr') ?>"
        data-schedule-share
        data-schedule-name="<?= esc($scheduleName, 'attr') ?>"
        data-schedule-date="<?= esc($scheduleDate, 'attr') ?>"
        data-schedule-time="<?= esc($scheduleTime, 'attr') ?>"
        data-schedule-location="<?= esc($scheduleLocation, 'attr') ?>"
        data-schedule-url="<?= esc(site_url('jadwal-ibadah'), 'attr') ?>"
    >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
            <circle cx="18" cy="5" r="3" />
            <circle cx="6" cy="12" r="3" />
            <circle cx="18" cy="19" r="3" />
            <path d="m8.7 10.7 6.6-4.4m-6.6 7 6.6 4.4" />
        </svg>
        <span>Bagikan jadwal</span>
    </button>
    <span class="schedule-share-status" role="status" aria-live="polite" aria-atomic="true" data-schedule-share-status></span>
</div>
