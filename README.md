# Inlite Barcode Reader CLI PHP Wrapper

[![Latest Stable Version](https://shields.io)](https://packagist.org)
[![License](https://shields.io)](LICENSE)

A lightweight, secure, and developer-friendly PHP wrapper for the **Inlite Research Barcode Reader CLI** tool. It utilizes the `symfony/process` component to safely run barcode scanning processes without risking shell injections.

---

## ⚖️ Legal Disclaimer

This repository **DOES NOT** contain the `BarcodeReaderCLI` binary files. 

This package is an **unofficial independent wrapper** and is not affiliated, associated, authorized, endorsed by, or in any way officially connected with Inlite Research. 

To use this wrapper, you **must independently obtain a valid license and download** the official `BarcodeReaderCLI` executable from the official [Inlite Research Website](https://inliteresearch.com). You must comply with the Inlite Barcode Reader License Agreement when using their software.

---

## 🚀 Features

* **Secure Executions:** Built on top of `symfony/process` preventing any basic command line injections.
* **Auto JSON Parsing:** Extracts dense nested CLI responses directly into convenient flat PHP arrays.
* **Flexible Filtering:** Easily pass specific barcode types (`qr`, `code128`, etc.) directly from PHP.

## 📦 Installation

Install the package via [Composer](https://getcomposer.org):

```bash
composer require your-username/inlite-barcode-reader
```

## 🛠️ Usage

### Basic Example (Array Output)

```php
<?php

require 'vendor/autoload.php';

use Neomasterr\Barcoder\Reader;

try {
    // 1. Initialize the reader with the path to your local Inlite CLI binary
    $reader = new Reader('C:\Inlite\BarcodeReaderCLI\bin\BarcodeReaderCLI.exe');
    // For Linux/Docker: new Reader('/usr/bin/BarcodeReaderCLI');

    // 2. Scan an image for specific barcode types
    $barcodes = $reader->readAsArray(__DIR__ . '/images/sample.jpg', ['code128', 'qr', 'ean13']);

    // 3. Iterate through results
    foreach ($barcodes as $barcode) {
        echo "Type: " . $barcode['type'] . "\n";
        echo "Value: " . $barcode['text'] . "\n";
        echo "Page: " . $barcode['page'] . "\n";
        echo "-----------------------\n";
    }

} catch (\InvalidArgumentException $e) {
    echo "Validation Error: " . $e->getMessage();
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### Get Raw JSON Output

If you need full details including image dimensions, bit depth, or metadata, use the raw method:

```php
$rawJson = $reader->readRaw(__DIR__ . '/images/sample.jpg', ['all']);
echo $rawJson;
```

## ⚙️ Supported Barcode Types

You can pass an array of supported types to the reader method. Valid types include:
`i25`, `code39`, `code128`, `codabar`, `upca`, `ean8`, `code93`, `upce`, `ean13`, `ucc128`, `patch`, `datamatrix`, `pdf417`, `qr`, and others supported by your Inlite license.

## 🔒 Security

If you discover any security-related issues, please open a Github issue or submit a pull request.

## 📄 License

This wrapper is open-sourced software licensed under the [MIT license](LICENSE).
