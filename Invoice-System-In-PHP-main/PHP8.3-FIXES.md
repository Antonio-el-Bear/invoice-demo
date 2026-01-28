# PHP 8.3 Compatibility Fixes

## Issues Fixed

### 1. **Parameter Order Error** ✅
**Error**: `Optional parameter $discount declared before required parameter $total`

**Fix**: Changed function signature from:
```php
function addItem($item,$description,$quantity,$vat,$price,$discount=0,$total)
```
To:
```php
function addItem($item,$description,$quantity,$vat,$price,$total,$discount=0)
```

**Files Updated**:
- `invoice.php` - Function definition
- `response.php` - All calls to addItem() (2 locations)
- `regenerate-pdfs.php` - PDF generation call

---

### 2. **Dynamic Property Deprecation** ✅
**Error**: `Creation of dynamic property invoicr::$property is deprecated`

**Fix**: Declared all dynamic properties at class level:
```php
var $currency;
var $maxImageDimensions;
var $firstColumnWidth;
var $discountField;
var $columns;
var $title;
var $flipflop;
```

**File**: `invoice.php`

---

### 3. **Undefined Property Warnings** ✅
**Error**: `Undefined property: invoicr::$currency`

**Cause**: Properties were being used before declaration in PHP 8.2+

**Fix**: Added property declarations (see #2 above)

---

## Changes Summary

| File | Changes | Lines Modified |
|------|---------|---------------|
| `invoice.php` | Added property declarations + fixed parameter order | ~10 lines |
| `response.php` | Updated addItem() calls (2 instances) | 2 lines |
| `regenerate-pdfs.php` | Updated addItem() call | 1 line |

## Testing

After these fixes:
- ✅ No deprecation warnings
- ✅ No undefined property errors
- ✅ PDF generation works correctly
- ✅ All invoice operations functional

## Compatibility

These fixes ensure compatibility with:
- ✅ PHP 8.3 (current)
- ✅ PHP 8.2
- ✅ PHP 8.1
- ✅ PHP 8.0

---

**Fixed**: January 23, 2026  
**PHP Version**: 8.3.30
