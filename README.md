# Spryker Symfony AI Wrapper

[![Latest Version](https://img.shields.io/packagist/v/spryker-community/product-3d-viewer.svg?style=flat-square)](https://packagist.org/packages/spryker-community/product-3d-viewer)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/travis/spryker-community/product-3d-viewer/master.svg?style=flat-square)](https://travis-ci.org/spryker-community/product-3d-viewer)

A Spryker Yves module that provides an integration with Symfony AI 

---

## Overview

## Installation

Follow these steps to install and configure the module in your Spryker project.

### 1. Install via Composer

```bash
composer require spryker-community/symfony-ai-wrapper
```

### 2. Add Core Namespace

Register the `SprykerCommunity` namespace in your project's configuration to allow the Spryker kernel to locate the module's classes.

**File:** `config/Shared/config_default.php`
```php
<?php

use Spryker\Shared\Kernel\KernelConstants;

// ...
$config[KernelConstants::CORE_NAMESPACES] = [
    'Spryker',
    'SprykerCommunity', // Add this line
];
```

### 4. Generate Transfer Objects

The module comes with a transfer definition. You must regenerate the transfer classes to make `Product3DViewTransfer` available throughout the application.

```bash
vendor/bin/console transfer:generate
```

### 5. Register the router

Register the plugin in: src/Pyz/Yves/Router/RouterDependencyProvider::getRouteProvider()

### 6. Register MCP in claude

http://yves.us.spryker.local/mcp and use method "listTools" to see what the mcp server can do.


## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
