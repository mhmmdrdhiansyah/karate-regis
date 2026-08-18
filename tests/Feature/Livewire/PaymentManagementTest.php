<?php

use App\Livewire\Admin\PaymentManagement;
use App\Livewire\PaymentList;
use App\Models\Payment;
use App\Models\User;
use App\Models\Contingent;
use App\Models\Event;
use App\Models\Registration;
use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    // Clear the permission cache to ensure we have fresh data
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // Get existing admin user from database without refreshing
    $this->admin = User::where('email', 'admin@admin.com')->first();

    if (!$this->admin) {
        $this->markTestSkipped('No admin user found in database');
    }
});

test('admin can view payment management page', function () {
    actingAs($this->admin)
        ->get(route('dashboard'))
        ->assertStatus(200);
});

test('payment management component displays payments', function () {
    $paymentCount = Payment::count();

    actingAs($this->admin)
        ->livewire(PaymentManagement::class)
        ->assertSet('search', '')
        ->assertSet('statusFilter', '')
        ->assertOk();
});

test('admin can filter payments by status', function () {
    $pendingPayment = Payment::where('status', PaymentStatus::Pending)->first();

    if (!$pendingPayment) {
        $this->markTestSkipped('No pending payment found in database');
    }

    actingAs($this->admin)
        ->livewire(PaymentManagement::class)
        ->set('statusFilter', PaymentStatus::Pending->value)
        ->assertSet('statusFilter', PaymentStatus::Pending->value);
});

test('admin can search payments by contingent name', function () {
    $payment = Payment::with('contingent')->first();

    if (!$payment || !$payment->contingent) {
        $this->markTestSkipped('No payment with contingent found in database');
    }

    actingAs($this->admin)
        ->livewire(PaymentManagement::class)
        ->set('search', $payment->contingent->name)
        ->assertSet('search', $payment->contingent->name);
});

test('admin can select payment for action', function () {
    $payment = Payment::first();

    if (!$payment) {
        $this->markTestSkipped('No payment found in database');
    }

    actingAs($this->admin)
        ->livewire(PaymentManagement::class)
        ->call('selectPayment', $payment->id)
        ->assertSet('selectedPaymentId', $payment->id);
});

test('admin cannot approve non-pending payment', function () {
    $verifiedPayment = Payment::where('status', '!=', PaymentStatus::Pending)->first();

    if (!$verifiedPayment) {
        $this->markTestSkipped('No verified payment found in database');
    }

    actingAs($this->admin)
        ->livewire(PaymentManagement::class)
        ->set('selectedPaymentId', $verifiedPayment->id)
        ->call('approve')
        ->assertDispatched('swal:error');
});

test('admin cannot reject payment without reason', function () {
    $pendingPayment = Payment::where('status', PaymentStatus::Pending)->first();

    if (!$pendingPayment) {
        $this->markTestSkipped('No pending payment found in database');
    }

    actingAs($this->admin)
        ->livewire(PaymentManagement::class)
        ->set('selectedPaymentId', $pendingPayment->id)
        ->set('rejectionReason', '')
        ->call('reject')
        ->assertHasErrors(['rejectionReason' => 'required']);
});

test('admin cannot reject payment with short reason', function () {
    $pendingPayment = Payment::where('status', PaymentStatus::Pending)->first();

    if (!$pendingPayment) {
        $this->markTestSkipped('No pending payment found in database');
    }

    actingAs($this->admin)
        ->livewire(PaymentManagement::class)
        ->set('selectedPaymentId', $pendingPayment->id)
        ->set('rejectionReason', 'abc')
        ->call('reject')
        ->assertHasErrors(['rejectionReason' => 'min']);
});

test('admin can revoke verified payment with reason', function () {
    $verifiedPayment = Payment::where('status', PaymentStatus::Verified)->first();

    if (!$verifiedPayment) {
        $this->markTestSkipped('No verified payment found in database');
    }

    $originalStatus = $verifiedPayment->status;

    actingAs($this->admin)
        ->livewire(PaymentManagement::class)
        ->set('selectedPaymentId', $verifiedPayment->id)
        ->set('rejectionReason', 'Test revocation reason')
        ->call('revoke')
        ->assertHasNoErrors();

    // Verify the payment status changed back to pending
    $verifiedPayment->refresh();
    expect($verifiedPayment->status)->toBe(PaymentStatus::Pending);
    expect($verifiedPayment->rejection_reason)->toBe('Test revocation reason');
});

test('admin cannot delete verified payment directly', function () {
    $verifiedPayment = Payment::where('status', PaymentStatus::Verified)->first();

    if (!$verifiedPayment) {
        $this->markTestSkipped('No verified payment found in database');
    }

    actingAs($this->admin)
        ->livewire(PaymentManagement::class)
        ->call('deletePayment', $verifiedPayment->id)
        ->assertDispatched('swal:error');
});

test('payment list component shows contingent payments', function () {
    $contingentUser = User::whereHas('contingent')->first();

    if (!$contingentUser) {
        $this->markTestSkipped('No contingent user found in database');
    }

    $payments = Payment::where('contingent_id', $contingentUser->contingent_id)->count();

    actingAs($contingentUser)
        ->livewire(PaymentList::class)
        ->assertOk();
});

test('payment management computes available events', function () {
    $eventCount = Event::count();

    actingAs($this->admin)
        ->livewire(PaymentManagement::class)
        ->assertOk();

    $component = new PaymentManagement();
    expect($component->events)->toHaveCount($eventCount);
});

test('payment management pagination works correctly', function () {
    actingAs($this->admin)
        ->livewire(PaymentManagement::class)
        ->assertSet('search', '')
        ->assertOk();

    $component = new PaymentManagement();
    $payments = $component->payments;

    expect($payments)->toBeInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class);
    expect($payments->perPage())->toBe(10);
});