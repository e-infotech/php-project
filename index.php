<?php
declare(strict_types=1);

// Enable error reporting for development
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Namespace declaration
namespace ModernPhpExample;

// Class definition with constructor property promotion
class PageGenerator
{
    public function __construct(
        private string $title = "Modern PHP Page",
        private array $content = []
    ) {}

    public function addContent(string $content): void
    {
        $this->content[] = $content;
    }

    public function render(): string
    {
        $contentHtml = implode("\n", array_map(fn($item) => "<p>$item</p>", $this->content));
        
        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{$this->title}</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
                h1 { color: #333; }
            </style>
        </head>
        <body>
            <h1>{$this->title}</h1>
            $contentHtml
            <footer>
                <p>PHP Version: {$this->getPhpVersion()}</p>
            </footer>
        </body>
        </html>
        HTML;
    }

    private function getPhpVersion(): string
    {
        return PHP_VERSION;
    }
}

// Usage
$page = new PageGenerator("Welcome to Modern PHP");
$page->addContent("This page demonstrates modern PHP features.");
$page->addContent("It uses PHP 8.3 and follows current best practices.");

// Output the page
echo $page->render();
