# Invoice Updates - Bank Account & PDF Export

## Changes Made

### 1. Migration: Add Bank Account Fields to Events
**File:** `database/migrations/2026_07_26_000001_add_bank_account_to_events_table.php`

Added fields to `events` table:
- `bank_name` (nullable) - Nama bank (BCA, Mandiri, dll)
- `account_number` (nullable) - Nomor rekening
- `account_holder` (nullable) - Atas nama rekening

### 2. Event Model Update
**File:** `app/Models/Event.php`

Added fillable fields:
- `bank_name`
- `account_number`
- `account_holder`

### 3. Invoice View Update
**File:** `resources/views/livewire/event-registration-invoice.blade.php`

Added:
- Bank account info display box in the summary card
- "Download PDF" button

### 4. PDF Invoice View
**File:** `resources/views/pdf/event-invoice.blade.php`

Created comprehensive PDF invoice template with:
- Event and contingent information
- Athlete and coach listings
- Cost breakdown
- Bank account information
- Print/Save PDF button

### 5. Invoice Controller
**File:** `app/Http/Controllers/InvoiceController.php`

Created controller to handle PDF invoice generation with proper data retrieval.

### 6. Route Update
**File:** `routes/web.php`

Added route:
```php
Route::get('registration/invoice/{event}/pdf', [InvoiceController::class, 'pdf'])
    ->name('invoice.pdf');
```

### 7. Livewire Component Update
**File:** `app/Livewire/EventRegistrationInvoice.php`

Added `downloadPDF()` method that redirects to the PDF route.

### 8. Seeder Update
**File:** `database/seeders/NewEventSeeder.php`

Updated event data to include bank account information for all 5 events.

## How to Use

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Run Seeder (for sample bank account data)
```bash
php artisan db:seed --class=NewEventSeeder
```

### 3. Access Invoice
- Navigate to registration invoice page
- Click "Download PDF" button
- Use browser's print dialog to save as PDF

## Bank Account per Event

Each event now has its own bank account:

| Event | Bank | No. Rekening | Atas Nama |
|-------|------|--------------|-----------|
| Piala Presiden 2026 | BCA | 1234567890 | Pengurus Pusat Karate Indonesia |
| Jakarta Open 2026 | Mandiri | 0987654321 | Jakarta Karate Association |
| Nasional Yunior 2026 | BNI | 555566667777 | Pengurus Nasional Karate |
| Indonesia Festival 2026 | BRI | 888899994444 | Festival Karate Indonesia |
| Piala KONI 2026 | BSI | 7000123456 | KONIN Karate Division |

## Future Enhancements

1. **Admin Interface**: Add form fields in event management to edit bank account
2. **Multiple Bank Accounts**: Allow events to have multiple bank options
3. **QRIS**: Add QRIS payment option
4. **Email Invoice**: Send invoice directly to contingent email
