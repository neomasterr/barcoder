<?php

namespace Neomasterr\Barcoder;

use InvalidArgumentException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Reader
{
    private string $cliPath;
    private array $defaultTypes = [
        'i25', 'code39', 'code128', 'codabar', 'upca',
        'ean8', 'code93', 'upce', 'ean13', 'ucc128',
        'patch', 'datamatrix', 'pdf417', '4state',
        'imb', 'bpo', 'aust', 'qr', 'sing', 'drvlic',
    ];

    public function __construct(string $cliPath)
    {
        if (!file_exists($cliPath)) {
            throw new InvalidArgumentException("Исполняемый файл Barcode Reader CLI не найден: {$cliPath}");
        }

        $this->cliPath = $cliPath;
    }

    public function readRaw(string $imagePath, array $types = [], array $additionalOptions = []): string
    {
        if (!file_exists($imagePath)) {
            throw new InvalidArgumentException("Image not found: {$imagePath}");
        }

        $command = [$this->cliPath];

        if (empty($types)) {
            $types = $this->defaultTypes;
        }

        $typesString = implode(',', $types);
        $command[] = "--type={$typesString}";
        $command[] = "--format=json";

        foreach ($additionalOptions as $option) {
            $command[] = $option;
        }

        $command[] = $imagePath;

        $process = $this->createProcess($command);
        $process->setTimeout(60);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return trim($process->getOutput());
    }

    public function readAsArray(string $imagePath, array $types = []): array
    {
        $rawJson = $this->readRaw($imagePath, $types);
        $data = json_decode($rawJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Ошибка парсинга JSON: " . json_last_error_msg());
        }

        $extractedBarcodes = [];

        foreach ($data['sessions'] ?? [] as $session) {
            foreach ($session['barcodes'] ?? [] as $barcode) {
                $extractedBarcodes[] = [
                    'text'   => $barcode['text'] ?? '',
                    'type'   => $barcode['type'] ?? '',
                    'length' => $barcode['length'] ?? 0,
                    'data'   => $barcode['data'] ?? '', // Base64
                    'page'   => $barcode['page']['number'] ?? 1,
                ];
            }
        }

        return $extractedBarcodes;
    }

    protected function createProcess(array $command): Process
    {
        return new Process($command);
    }
}
