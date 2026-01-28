# PHPUnit Testing Setup

## Overview
This directory contains PHPUnit tests for the Invoice Management System.

## Structure
```
tests/
├── Unit/                  # Unit tests for individual functions
│   └── InvoiceFunctionsTest.php
└── Integration/          # Integration tests (database, full workflows)
```

## Running Tests

### Run all tests
```bash
php composer.phar test
```

### Or directly with PHPUnit
```bash
vendor/bin/phpunit
```

### Run specific test suite
```bash
vendor/bin/phpunit --testsuite "Unit Tests"
```

### Run with coverage (requires Xdebug)
```bash
vendor/bin/phpunit --coverage-html coverage/
```

## Writing Tests

### Unit Tests
- Test individual functions in isolation
- Mock database connections when possible
- Focus on business logic validation

### Integration Tests
- Test full workflows (create invoice → save → retrieve)
- Use test database or fixtures
- Clean up test data after each test

## Test Naming Convention
- Test methods: `test{FunctionName}{Scenario}`
- Example: `testInvoiceCalculationWithDiscount()`

## Database Testing
For tests that require database access:
1. Create a separate test database
2. Use database transactions in setUp/tearDown
3. Or use SQLite in-memory database for speed
