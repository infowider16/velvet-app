<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SyncStaticMessagesCommand extends Command
{
    protected $signature = 'lang:sync-file {file : Relative file path from project root}';
    protected $description = 'Replace only static messages in a PHP file with translation keys and safely merge them into lang files';

    protected array $targetMethods = [
        'sendError',
        'sendResponse',
        'serviceSuccessResponse',
        'serviceErrorResponse',
        'responseFail',
        'responseSuccess',
    ];

    public function handle(): int
    {
        $relativeFilePath = $this->argument('file');
        $fullFilePath = base_path($relativeFilePath);

        if (!File::exists($fullFilePath)) {
            $this->error("File not found: {$relativeFilePath}");
            return self::FAILURE;
        }

        $content = File::get($fullFilePath);
        $originalContent = $content;

        $langEntries = [];

        foreach ($this->targetMethods as $method) {
            $content = $this->replaceStaticMessagesInMethod($content, $method, $langEntries);
        }
        
        $content = $this->replaceStaticMessagesInArrayMessageKeys($content, $langEntries);
        $content = $this->replaceStaticMessagesInExceptions($content, $langEntries);

        if ($content !== $originalContent) {
            File::put($fullFilePath, $content);
            $this->info("Updated file: {$relativeFilePath}");
        } else {
            $this->warn("No static messages replaced in: {$relativeFilePath}");
        }

        if (!empty($langEntries)) {
            $this->syncLangFiles($langEntries);

            $this->info('Language files merged successfully.');
            $this->line('- resources/lang/en/message.php');
            $this->line('- resources/lang/ge/message.php');
            $this->line('- resources/lang/ge/message_pending.php');

            $this->displayNewKeys($langEntries);
        } else {
            $this->warn('No new language keys needed.');
        }

        return self::SUCCESS;
    }
    
    protected function replaceStaticMessagesInArrayMessageKeys(string $content, array &$langEntries): string
    {
        $pattern = '/([\'"]message[\'"]\s*=>\s*)([\'"])((?:\\\\.|(?!\2).)*)\2/s';
    
        return preg_replace_callback($pattern, function ($matches) use (&$langEntries) {
            $prefix = $matches[1];
            $message = stripcslashes($matches[3]);
    
            if ($this->shouldSkipMessage($message)) {
                return $matches[0];
            }
    
            $existingKey = $this->findExistingLangKeyByMessage($message);
    
            if ($existingKey) {
                $key = $existingKey;
            } else {
                $key = $this->generateUniqueFlatKey($message, $langEntries);
                $langEntries[$key] = $message;
            }
    
            return $prefix . "__('message.{$key}')";
        }, $content);
    }

    protected function replaceStaticMessagesInMethod(string $content, string $method, array &$langEntries): string
    {
        $pattern = '/\$this->' . preg_quote($method, '/') . '\s*\((.*?)\);/s';

        return preg_replace_callback($pattern, function ($matches) use (&$langEntries) {
            $fullMatch = $matches[0];
            $arguments = $matches[1];

            $parsedArguments = $this->splitArguments($arguments);

            if (empty($parsedArguments)) {
                return $fullMatch;
            }

            foreach ($parsedArguments as $index => $argument) {
                $trimmedArgument = trim($argument);

                if (!$this->isQuotedString($trimmedArgument)) {
                    continue;
                }

                $message = $this->extractQuotedString($trimmedArgument);

                if ($this->shouldSkipMessage($message)) {
                    continue;
                }

                $existingKey = $this->findExistingLangKeyByMessage($message);

                if ($existingKey) {
                    $key = $existingKey;
                } else {
                    $key = $this->generateUniqueFlatKey($message, $langEntries);
                    $langEntries[$key] = $message;
                }

                $parsedArguments[$index] = "__('message.{$key}')";
            }

            return '$this->' . $this->extractMethodName($fullMatch) . '(' . implode(', ', $parsedArguments) . ');';
        }, $content);
    }

    protected function replaceStaticMessagesInExceptions(string $content, array &$langEntries): string
    {
        $pattern = '/throw\s+new\s+([\\\\a-zA-Z_][\\\\a-zA-Z0-9_]*)\s*\(([^;]*?)\);/m';

        return preg_replace_callback($pattern, function ($matches) use (&$langEntries) {
            $fullMatch = $matches[0];
            $exceptionClass = $matches[1];
            $arguments = trim($matches[2]);

            $parsedArguments = $this->splitArguments($arguments);

            if (empty($parsedArguments)) {
                return $fullMatch;
            }

            $firstArgument = trim($parsedArguments[0]);

            if (!$this->isPureStaticStringArgument($firstArgument)) {
                return $fullMatch;
            }

            $message = $this->extractQuotedString($firstArgument);

            if ($this->shouldSkipMessage($message)) {
                return $fullMatch;
            }

            $existingKey = $this->findExistingLangKeyByMessage($message);

            if ($existingKey) {
                $key = $existingKey;
            } else {
                $key = $this->generateUniqueFlatKey($message, $langEntries);
                $langEntries[$key] = $message;
            }

            $parsedArguments[0] = "__('message.{$key}')";

            return "throw new {$exceptionClass}(" . implode(', ', $parsedArguments) . ");";
        }, $content);
    }

    protected function extractMethodName(string $fullMatch): string
    {
        preg_match('/\$this->([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/', $fullMatch, $matches);
        return $matches[1] ?? '';
    }

    protected function splitArguments(string $arguments): array
    {
        $result = [];
        $current = '';
        $length = strlen($arguments);

        $depthParentheses = 0;
        $depthBrackets = 0;
        $depthBraces = 0;
        $inSingleQuote = false;
        $inDoubleQuote = false;
        $escaped = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $arguments[$i];

            if ($escaped) {
                $current .= $char;
                $escaped = false;
                continue;
            }

            if ($char === '\\') {
                $current .= $char;
                $escaped = true;
                continue;
            }

            if ($char === "'" && !$inDoubleQuote) {
                $inSingleQuote = !$inSingleQuote;
                $current .= $char;
                continue;
            }

            if ($char === '"' && !$inSingleQuote) {
                $inDoubleQuote = !$inDoubleQuote;
                $current .= $char;
                continue;
            }

            if (!$inSingleQuote && !$inDoubleQuote) {
                if ($char === '(') {
                    $depthParentheses++;
                } elseif ($char === ')') {
                    $depthParentheses--;
                } elseif ($char === '[') {
                    $depthBrackets++;
                } elseif ($char === ']') {
                    $depthBrackets--;
                } elseif ($char === '{') {
                    $depthBraces++;
                } elseif ($char === '}') {
                    $depthBraces--;
                } elseif (
                    $char === ',' &&
                    $depthParentheses === 0 &&
                    $depthBrackets === 0 &&
                    $depthBraces === 0
                ) {
                    $result[] = trim($current);
                    $current = '';
                    continue;
                }
            }

            $current .= $char;
        }

        if (trim($current) !== '') {
            $result[] = trim($current);
        }

        return $result;
    }

    protected function isQuotedString(string $value): bool
    {
        return preg_match('/^([\'"])(.*)\1$/s', $value) === 1;
    }

    protected function isPureStaticStringArgument(string $argument): bool
    {
        return $this->isQuotedString(trim($argument));
    }

    protected function extractQuotedString(string $value): string
    {
        return preg_replace('/^([\'"])(.*)\1$/s', '$2', $value);
    }

    protected function shouldSkipMessage(string $message): bool
    {
        if ($message === '') {
            return true;
        }

        if (Str::contains($message, '__(')) {
            return true;
        }

        if (preg_match('/^[a-z0-9_.-]+$/i', $message) && Str::contains($message, '.')) {
            return true;
        }

        return false;
    }

    protected function syncLangFiles(array $newEntries): void
    {
        $enPath = resource_path('lang/en/message.php');
        $gePath = resource_path('lang/ge/message.php');
        $gePendingPath = resource_path('lang/ge/message_pending.php');

        $enExisting = File::exists($enPath) ? include $enPath : [];
        $geExisting = File::exists($gePath) ? include $gePath : [];
        $gePendingExisting = File::exists($gePendingPath) ? include $gePendingPath : [];

        foreach ($newEntries as $key => $message) {
            if (!array_key_exists($key, $enExisting)) {
                $enExisting[$key] = $message;
            }

            if (!array_key_exists($key, $geExisting)) {
                $geExisting[$key] = $message;
            }

            if (!array_key_exists($key, $gePendingExisting)) {
                $gePendingExisting[$key] = $message;
            }
        }

        ksort($enExisting);
        ksort($geExisting);
        ksort($gePendingExisting);

        $this->writePhpArrayFile($enPath, $enExisting);
        $this->writePhpArrayFile($gePath, $geExisting);
        $this->writePhpArrayFile($gePendingPath, $gePendingExisting);
    }

    protected function findExistingLangKeyByMessage(string $message): ?string
    {
        $files = [
            resource_path('lang/en/message.php'),
            resource_path('lang/ge/message.php'),
        ];

        foreach ($files as $file) {
            if (!File::exists($file)) {
                continue;
            }

            $messages = include $file;

            if (!is_array($messages)) {
                continue;
            }

            $foundKey = array_search($message, $messages, true);

            if ($foundKey !== false) {
                return (string) $foundKey;
            }
        }

        return null;
    }

    protected function generateUniqueFlatKey(string $message, array $newEntries): string
    {
        $baseKey = Str::snake(
            Str::of($message)
                ->replaceMatches('/[^A-Za-z0-9\s]/', '')
                ->trim()
                ->value()
        );

        $baseKey = trim($baseKey, '_');

        if ($baseKey === '') {
            $baseKey = 'message';
        }

        $key = Str::limit($baseKey, 80, '');
        $counter = 1;

        $existingKeys = $this->getAllExistingKeys();

        while (
            in_array($key, $existingKeys, true) ||
            (isset($newEntries[$key]) && $newEntries[$key] !== $message)
        ) {
            $suffix = '_' . $counter;
            $allowedLength = 80 - strlen($suffix);
            $key = Str::limit($baseKey, $allowedLength, '') . $suffix;
            $counter++;
        }

        return $key;
    }

    protected function getAllExistingKeys(): array
    {
        $keys = [];

        $files = [
            resource_path('lang/en/message.php'),
            resource_path('lang/ge/message.php'),
            resource_path('lang/ge/message_pending.php'),
        ];

        foreach ($files as $file) {
            if (!File::exists($file)) {
                continue;
            }

            $messages = include $file;

            if (is_array($messages)) {
                $keys = array_merge($keys, array_keys($messages));
            }
        }

        return array_unique($keys);
    }

    protected function writePhpArrayFile(string $path, array $data): void
    {
        File::ensureDirectoryExists(dirname($path));

        $export = "<?php\n\nreturn " . $this->exportArray($data) . ";\n";
        File::put($path, $export);
    }

    protected function exportArray(array $array, int $level = 0): string
    {
        $indent = str_repeat('    ', $level);
        $nextIndent = str_repeat('    ', $level + 1);

        $lines = ['['];

        foreach ($array as $key => $value) {
            $formattedKey = var_export((string) $key, true);
            $formattedValue = is_array($value)
                ? $this->exportArray($value, $level + 1)
                : var_export($value, true);

            $lines[] = $nextIndent . $formattedKey . ' => ' . $formattedValue . ',';
        }

        $lines[] = $indent . ']';

        return implode("\n", $lines);
    }

    protected function displayNewKeys(array $langEntries): void
    {
        $this->newLine();
        $this->info('Newly added translation keys:');
        $this->line(str_repeat('-', 80));

        foreach ($langEntries as $key => $message) {
            $this->line("message.{$key} => {$message}");
        }

        $this->line(str_repeat('-', 80));
        $this->info('German pending translations are also saved in: resources/lang/ge/message_pending.php');
    }
}