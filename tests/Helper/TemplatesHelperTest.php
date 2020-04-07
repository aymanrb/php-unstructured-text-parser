<?php

namespace aymanrb\UnstructuredTextParser\Tests\Helper;

use aymanrb\UnstructuredTextParser\Exception\InvalidTemplatesDirectoryException;
use aymanrb\UnstructuredTextParser\Helper\TemplatesHelper;
use PHPUnit\Framework\TestCase;

class TemplatesHelperTest extends TestCase
{
    public function testExceptionIsRaisedForInvalidTemplatesDirectory()
    {
        $this->expectException(InvalidTemplatesDirectoryException::class);
        new TemplatesHelper(__DIR__ . '/DirectoryThatDoesNotExist');
    }

    private function getTemplatesHelperInstance()
    {
        return new TemplatesHelper(__DIR__ . '/helper_templates');
    }

    public function testGetAllTemplates()
    {
        $templatesHelper = $this->getTemplatesHelperInstance();

        $returnedTemplates = $templatesHelper->getTemplates('regardless of what comes here');
        $this->assertCount(2, $returnedTemplates);
    }

    public function testGetAllTemplatesRegexIsPrepared()
    {
        $templatesHelper = $this->getTemplatesHelperInstance();

        $returnedTemplates = $templatesHelper->getTemplates('regardless of what comes here');
        $this->assertTrue($this->checkPreparedTemplates($returnedTemplates));
    }

    public function testGetMostMatchingTemplateToText()
    {
        $templatesHelper = $this->getTemplatesHelperInstance();

        $returnedTemplates = $templatesHelper->getTemplates('Sent to customer service from Someone', true);
        $this->assertCount(1, $returnedTemplates);
    }

    public function testGetMostMatchingTemplateToTextRegexIsPrepared()
    {
        $templatesHelper = $this->getTemplatesHelperInstance();

        $returnedTemplates = $templatesHelper->getTemplates('Sent to customer service from Someone', true);
        $this->assertTrue($this->checkPreparedTemplates($returnedTemplates));
    }

    private function checkPreparedTemplates(array $templatesArray): bool
    {
        foreach ($templatesArray as $templatePath => $template) {
            $this->assertStringContainsString('(?<variable>.*)', $template);
            $this->assertTrue($this->isValidRegex($template));
        }

        return true;
    }

    private function isValidRegex(string $pattern): bool
    {
        try {
            preg_match('/' . $pattern . '/s', '');
        } catch (\Throwable $exception) {
            return false;
        }

        return true;
    }
}
