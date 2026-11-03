<?php

use CodeIgniter\Test\CIUnitTestCase;

final class ResponsiveModalTest extends CIUnitTestCase
{
    public function testModalMarkupEscapesContentAndCarriesResponsiveProps(): void
    {
        helper('modal');

        ob_start();
        modal_open(
            'schedule-modal',
            true,
            ['aria-labelledby' => 'schedule-title'],
            ['aria-labelledby' => 'schedule-title'],
        );
        modal_content_open();
        modal_title('<Tambah Jadwal>', 'fw-semibold', ['id' => 'schedule-title'], ['id' => 'schedule-title']);
        modal_section_end();
        modal_end();
        $markup = (string) ob_get_clean();

        $this->assertStringContainsString('id="schedule-modal"', $markup);
        $this->assertStringContainsString('data-rm-default-open', $markup);
        $this->assertStringContainsString(
            'data-rm-desktop="&#x7B;&quot;aria-labelledby&quot;&#x3A;&quot;schedule-title&quot;&#x7D;"',
            $markup,
        );
        $this->assertStringContainsString('&lt;Tambah Jadwal&gt;', $markup);
        $this->assertStringNotContainsString('<Tambah Jadwal>', $markup);
    }

    public function testModalAttributesHandleBooleanAndEscapedValues(): void
    {
        helper('modal');

        $markup = modal_attrs([
            'disabled' => true,
            'title' => 'A "safe" value',
            'hidden' => false,
            'ignored' => null,
        ]);

        $this->assertSame(' disabled title="A&#x20;&quot;safe&quot;&#x20;value"', $markup);
    }
}
