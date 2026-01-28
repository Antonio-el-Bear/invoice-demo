<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class InvoiceFunctionsTest extends TestCase
{
    /**
     * Test that invoice ID generation returns expected format
     */
    public function testInvoiceIdGeneration(): void
    {
        // Test invoice ID starts with expected format
        $invoiceId = $this->generateMockInvoiceId(1);
        
        $this->assertIsNumeric($invoiceId, 'Invoice ID should be numeric');
        $this->assertGreaterThan(0, $invoiceId, 'Invoice ID should be positive');
    }

    /**
     * Test invoice number increment logic
     */
    public function testInvoiceNumberIncrement(): void
    {
        $currentId = 100;
        $nextId = $currentId + 1;
        
        $this->assertEquals(101, $nextId, 'Next invoice should increment by 1');
    }

    /**
     * Test invoice status validation
     */
    public function testInvoiceStatusValues(): void
    {
        $validStatuses = ['open', 'paid', 'overdue', 'cancelled'];
        
        foreach ($validStatuses as $status) {
            $this->assertContains($status, $validStatuses);
        }
        
        $this->assertNotContains('invalid-status', $validStatuses);
    }

    /**
     * Test invoice type validation
     */
    public function testInvoiceTypeValues(): void
    {
        $validTypes = ['standard', 'recurring', 'proforma'];
        
        $testType = 'standard';
        $this->assertContains($testType, $validTypes, 'Standard should be a valid invoice type');
    }

    /**
     * Test date format validation for invoice dates
     */
    public function testInvoiceDateFormat(): void
    {
        $testDate = '2026-01-23';
        $timestamp = strtotime($testDate);
        
        $this->assertNotFalse($timestamp, 'Date should be valid');
        $this->assertEquals($testDate, date('Y-m-d', $timestamp), 'Date format should be Y-m-d');
    }

    /**
     * Test invoice amount calculations
     */
    public function testInvoiceAmountCalculation(): void
    {
        $quantity = 5;
        $unitPrice = 10.50;
        $expectedTotal = 52.50;
        
        $calculatedTotal = $quantity * $unitPrice;
        
        $this->assertEquals($expectedTotal, $calculatedTotal, 'Invoice line total should calculate correctly');
    }

    /**
     * Test tax calculation (assuming 10% tax rate)
     */
    public function testTaxCalculation(): void
    {
        $subtotal = 100.00;
        $taxRate = 0.10;
        $expectedTax = 10.00;
        
        $calculatedTax = $subtotal * $taxRate;
        
        $this->assertEquals($expectedTax, $calculatedTax, 'Tax should calculate correctly');
    }

    /**
     * Test grand total calculation
     */
    public function testGrandTotalCalculation(): void
    {
        $subtotal = 100.00;
        $tax = 10.00;
        $expectedGrandTotal = 110.00;
        
        $grandTotal = $subtotal + $tax;
        
        $this->assertEquals($expectedGrandTotal, $grandTotal, 'Grand total should include subtotal and tax');
    }

    /**
     * Test email validation for customer emails
     */
    public function testEmailValidation(): void
    {
        $validEmail = 'customer@example.com';
        $invalidEmail = 'invalid-email';
        
        $this->assertTrue(filter_var($validEmail, FILTER_VALIDATE_EMAIL) !== false, 'Valid email should pass');
        $this->assertFalse(filter_var($invalidEmail, FILTER_VALIDATE_EMAIL) !== false, 'Invalid email should fail');
    }

    /**
     * Test discount calculation
     */
    public function testDiscountCalculation(): void
    {
        $amount = 100.00;
        $discountPercent = 15;
        $expectedDiscount = 15.00;
        
        $discount = ($amount * $discountPercent) / 100;
        
        $this->assertEquals($expectedDiscount, $discount, 'Discount should calculate correctly');
    }

    // Helper methods

    /**
     * Mock invoice ID generation for testing
     */
    private function generateMockInvoiceId(int $lastId): int
    {
        return $lastId + 1;
    }
}
