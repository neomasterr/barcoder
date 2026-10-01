<?php

namespace YourNamespace\InliteBarcode;

use InvalidArgumentException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Reader
{
    private string $cliPath;

    public function __construct(string $cliPath)
    {
        if (!file_exists($cliPath)) {
            throw new InvalidArgumentException("Исполняемый файл Barcode Reader CLI не найден по пути: {$cliPath}");
        }
        
        $this->cliPath = $cliPath;
    }

    public function read(string $imagePath, array $additionalOptions = []): string
    {
        if (!file_exists($imagePath)) {
            throw new InvalidArgumentException("Файл изображения не найден: {$imagePath}");
        }

        $command = array_merge(
            [$this->cliPath, $imagePath],
            $additionalOptions
        );

        $process = new Process($command);
        $process->setTimeout(60);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return trim($process->getOutput());
    }
}
