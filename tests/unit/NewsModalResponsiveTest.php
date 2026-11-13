<?php

use CodeIgniter\Test\CIUnitTestCase;

final class NewsModalResponsiveTest extends CIUnitTestCase
{
    public function testNewsTitleFieldSupportsMultilineResponsiveInput(): void
    {
        $markup = view('components/ui/news-create-modal', [
            'formStatusOptions' => \App\Models\BeritaModel::formStatusOptions(),
        ]);

        $this->assertMatchesRegularExpression(
            '/<textarea\b(?=[^>]*id="news-create-title-input")(?=[^>]*name="title")(?=[^>]*data-news-title)[^>]*>/',
            $markup,
        );
        $this->assertStringContainsString('maxlength="255"', $markup);
        $this->assertStringContainsString('minlength="3"', $markup);
        $this->assertStringContainsString('required', $markup);
    }
}
