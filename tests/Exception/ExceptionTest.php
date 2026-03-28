<?php

declare(strict_types = 1);

namespace aymanrb\UnstructuredTextParser\Tests\Exception;

use aymanrb\UnstructuredTextParser\Exception\InvalidParsedDataKeyException;
use aymanrb\UnstructuredTextParser\Exception\InvalidParseFileException;
use aymanrb\UnstructuredTextParser\Exception\InvalidTemplateSyntaxException;
use aymanrb\UnstructuredTextParser\Exception\InvalidTemplatesDirectoryException;
use aymanrb\UnstructuredTextParser\Exception\InvalidTemplateVariableNameException;
use aymanrb\UnstructuredTextParser\Exception\UnstructuredTextParserException;
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    public function testAllExceptionsExtendBaseException(): void
    {
        $this->assertInstanceOf(UnstructuredTextParserException::class, new InvalidParseFileException());
        $this->assertInstanceOf(UnstructuredTextParserException::class, new InvalidParsedDataKeyException());
        $this->assertInstanceOf(UnstructuredTextParserException::class, new InvalidTemplatesDirectoryException());
        $this->assertInstanceOf(UnstructuredTextParserException::class, new InvalidTemplateSyntaxException());
        $this->assertInstanceOf(UnstructuredTextParserException::class, new InvalidTemplateVariableNameException());
    }

    public function testBaseExceptionExtendsRuntimeException(): void
    {
        $this->assertInstanceOf(\Exception::class, new UnstructuredTextParserException());
    }

    public function testInvalidTemplatesDirectoryExceptionPreservesMessage(): void
    {
        $message = 'Invalid templates directory provided';
        $exception = new InvalidTemplatesDirectoryException($message);

        $this->assertSame($message, $exception->getMessage());
    }

    public function testInvalidParseFileExceptionPreservesMessage(): void
    {
        $path = '/some/missing/file.txt';
        $exception = new InvalidParseFileException($path);

        $this->assertSame($path, $exception->getMessage());
    }

    public function testInvalidParsedDataKeyExceptionPreservesMessage(): void
    {
        $message = 'Undefined results key: myKey';
        $exception = new InvalidParsedDataKeyException($message);

        $this->assertSame($message, $exception->getMessage());
    }

    public function testInvalidTemplateSyntaxExceptionPreservesMessage(): void
    {
        $message = 'Template produced an invalid regex pattern: No error';
        $exception = new InvalidTemplateSyntaxException($message);

        $this->assertSame($message, $exception->getMessage());
    }

    public function testInvalidTemplateVariableNameExceptionPreservesMessage(): void
    {
        $message = 'Invalid template variable name "my-var"';
        $exception = new InvalidTemplateVariableNameException($message);

        $this->assertSame($message, $exception->getMessage());
    }
}
