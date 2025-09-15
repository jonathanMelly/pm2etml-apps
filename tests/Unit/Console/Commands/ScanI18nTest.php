<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\ScanI18n;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ScanI18nTest extends TestCase
{
    private ScanI18n $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->command = new ScanI18n();
    }

    public function test_finds_translation_strings_with_punctuation()
    {
        // Test that the scanner can find JSON translation strings that contain punctuation
        // This was a bug where strings ending with periods were incorrectly classified as PHP translations
        
        $testContent = '
            <button onclick="if(confirm(\'{{__("Are you sure you want to cancel? Any unsaved changes will be lost.")}}\')) { history.back(); }">
                {{__("Cancel")}}
            </button>
            <div>{{__("auth.failed")}}</div>
            <span>{{__("Save changes.")}}</span>
            <p>{{__("validation.required")}}</p>
        ';

        // Use reflection to access private methods
        $reflection = new \ReflectionClass($this->command);
        $patternsProperty = $reflection->getProperty('patterns');
        $patternsProperty->setAccessible(true);
        $patterns = $patternsProperty->getValue($this->command);

        $scanFilesMethod = $reflection->getMethod('scanFiles');
        $scanFilesMethod->setAccessible(true);

        $findUntranslatedMethod = $reflection->getMethod('findUntranslatedJsonStrings');
        $findUntranslatedMethod->setAccessible(true);

        // Mock file scanning by testing the patterns directly
        $foundStrings = [];
        foreach ($patterns['json'] as $pattern) {
            if (preg_match_all($pattern, $testContent, $matches)) {
                foreach ($matches[1] as $match) {
                    if (!str_starts_with($match, '$')) {
                        $foundStrings[$match] = ['test-file.blade.php'];
                    }
                }
            }
        }

        // Verify that strings with punctuation are found
        $this->assertArrayHasKey('Are you sure you want to cancel? Any unsaved changes will be lost.', $foundStrings);
        $this->assertArrayHasKey('Cancel', $foundStrings);
        $this->assertArrayHasKey('Save changes.', $foundStrings);
        $this->assertArrayHasKey('auth.failed', $foundStrings);
        $this->assertArrayHasKey('validation.required', $foundStrings);

        // Test the findUntranslatedJsonStrings method
        $existingTranslations = [
            'Cancel' => 'Annuler',
            'auth.failed' => 'Échec d\'authentification',
            'validation.required' => 'Ce champ est requis',
        ];

        $untranslated = $findUntranslatedMethod->invoke($this->command, $foundStrings, $existingTranslations);

        // Should find the strings with punctuation as untranslated JSON strings
        $this->assertArrayHasKey('Are you sure you want to cancel? Any unsaved changes will be lost.', $untranslated);
        $this->assertArrayHasKey('Save changes.', $untranslated);

        // Should NOT include PHP translation keys or already translated strings
        $this->assertArrayNotHasKey('auth.failed', $untranslated);
        $this->assertArrayNotHasKey('validation.required', $untranslated);
        $this->assertArrayNotHasKey('Cancel', $untranslated);
    }

    public function test_distinguishes_between_php_and_json_translations()
    {
        $reflection = new \ReflectionClass($this->command);
        $findUntranslatedMethod = $reflection->getMethod('findUntranslatedJsonStrings');
        $findUntranslatedMethod->setAccessible(true);

        $testStrings = [
            // These should be treated as JSON translations (sentences with punctuation)
            'Are you sure you want to delete this item?' => ['test.blade.php'],
            'Save changes.' => ['test.blade.php'], 
            'Hello, world!' => ['test.blade.php'],
            'Something went wrong...' => ['test.blade.php'],
            'File uploaded successfully.' => ['test.blade.php'],
            
            // These should be treated as PHP translation keys (namespace.key format)
            'auth.failed' => ['test.blade.php'],
            'validation.required' => ['test.blade.php'],
            'messages.success' => ['test.blade.php'],
            'errors.not_found' => ['test.blade.php'],
            'app.name' => ['test.blade.php'],
        ];

        $emptyTranslations = [];
        $untranslated = $findUntranslatedMethod->invoke($this->command, $testStrings, $emptyTranslations);

        // JSON translations should be included
        $this->assertArrayHasKey('Are you sure you want to delete this item?', $untranslated);
        $this->assertArrayHasKey('Save changes.', $untranslated);
        $this->assertArrayHasKey('Hello, world!', $untranslated);
        $this->assertArrayHasKey('Something went wrong...', $untranslated);
        $this->assertArrayHasKey('File uploaded successfully.', $untranslated);

        // PHP translation keys should be excluded from JSON untranslated list
        $this->assertArrayNotHasKey('auth.failed', $untranslated);
        $this->assertArrayNotHasKey('validation.required', $untranslated);
        $this->assertArrayNotHasKey('messages.success', $untranslated);
        $this->assertArrayNotHasKey('errors.not_found', $untranslated);
        $this->assertArrayNotHasKey('app.name', $untranslated);
    }

    public function test_html_attribute_patterns_work()
    {
        $reflection = new \ReflectionClass($this->command);
        $patternsProperty = $reflection->getProperty('patterns');
        $patternsProperty->setAccessible(true);
        $patterns = $patternsProperty->getValue($this->command);

        $testCases = [
            'onclick="if(confirm(\'{{__("Delete this item?")}}\')) { deleteItem(); }"',
            'onchange="showMessage(\'{{__("Value changed.")}}\');"',
            'data-confirm="{{__("Are you sure?")}}"',
            'data-message="{{__("Operation completed successfully.")}}"',
        ];

        foreach ($testCases as $testContent) {
            $found = false;
            foreach ($patterns['json'] as $pattern) {
                if (preg_match($pattern, $testContent, $matches)) {
                    $found = true;
                    $this->assertNotEmpty($matches[1], "Pattern should capture translation string in: $testContent");
                    break;
                }
            }
            $this->assertTrue($found, "Should find translation in HTML attribute: $testContent");
        }
    }

    public function test_finds_translation_strings_with_parameters()
    {
        // Test that the scanner can find translation strings that have parameters
        // This covers the scenario where __('string', [...]) format is used

        $testContent = '
            return response()->json([
                "error" => __("File format :extension not allowed. Only :formats files are accepted.", [
                    "extension" => $extension,
                    "formats" => implode(", ", FileFormat::CONTRACT_EVALUATION_PDF_FORMATS)
                ])
            ], 400);

            $message = __("User :name has :count unread messages.", [
                "name" => $user->name,
                "count" => $unreadCount
            ]);

            echo __("Simple message without parameters");
            echo __("Another message.", ["unused" => "parameter"]);
        ';

        // Use reflection to access private methods
        $reflection = new \ReflectionClass($this->command);
        $patternsProperty = $reflection->getProperty('patterns');
        $patternsProperty->setAccessible(true);
        $patterns = $patternsProperty->getValue($this->command);

        // Mock file scanning by testing the patterns directly
        $foundStrings = [];
        foreach ($patterns['json'] as $pattern) {
            if (preg_match_all($pattern, $testContent, $matches)) {
                foreach ($matches[1] as $match) {
                    if (!str_starts_with($match, '$')) {
                        $foundStrings[$match] = ['test-controller.php'];
                    }
                }
            }
        }

        // Verify that strings with parameters are found
        $this->assertArrayHasKey('File format :extension not allowed. Only :formats files are accepted.', $foundStrings);
        $this->assertArrayHasKey('User :name has :count unread messages.', $foundStrings);
        $this->assertArrayHasKey('Simple message without parameters', $foundStrings);
        $this->assertArrayHasKey('Another message.', $foundStrings);

        // Test that these are correctly identified as JSON translations (not PHP keys)
        $findUntranslatedMethod = $reflection->getMethod('findUntranslatedJsonStrings');
        $findUntranslatedMethod->setAccessible(true);

        $existingTranslations = [
            'Simple message without parameters' => 'Message simple sans paramètres',
        ];

        $untranslated = $findUntranslatedMethod->invoke($this->command, $foundStrings, $existingTranslations);

        // Should find the parameterized strings as untranslated JSON strings
        $this->assertArrayHasKey('File format :extension not allowed. Only :formats files are accepted.', $untranslated);
        $this->assertArrayHasKey('User :name has :count unread messages.', $untranslated);
        $this->assertArrayHasKey('Another message.', $untranslated);

        // Should NOT include already translated strings
        $this->assertArrayNotHasKey('Simple message without parameters', $untranslated);
    }
}