<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PaymentMethod::with('tenant');
        
        if ($request->has('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }
        
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        
        if ($request->has('is_default')) {
            $query->where('is_default', $request->boolean('is_default'));
        }
        
        $paymentMethods = $query->paginate(20);
        return response()->json($paymentMethods);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'type' => 'required|in:credit_card,debit_card,bank_account,cash,check',
            'last_four' => 'required|string|size:4',
            'card_type' => 'nullable|required_if:type,credit_card,debit_card|in:visa,mastercard,amex,discover',
            'bank_name' => 'nullable|required_if:type,bank_account|string|max:200',
            'account_type' => 'nullable|required_if:type,bank_account|in:checking,savings',
            'expiration_date' => 'nullable|required_if:type,credit_card,debit_card|date|after:today',
            'is_default' => 'boolean',
            'billing_address' => 'nullable|array',
            'billing_address.street' => 'required_with:billing_address|string',
            'billing_address.city' => 'required_with:billing_address|string',
            'billing_address.state' => 'required_with:billing_address|string',
            'billing_address.zip_code' => 'required_with:billing_address|string',
            'is_active' => 'boolean',
            'payment_token' => 'nullable|string|max:500',
        ]);

        // If this is set as default, unset other defaults for this tenant
        if ($validated['is_default'] ?? false) {
            PaymentMethod::where('tenant_id', $validated['tenant_id'])
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $paymentMethod = PaymentMethod::create($validated);
        return response()->json($paymentMethod, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(PaymentMethod $paymentMethod)
    {
        $paymentMethod->load(['tenant', 'payments']);
        return response()->json($paymentMethod);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $validated = $request->validate([
            'tenant_id' => 'sometimes|required|exists:tenants,id',
            'type' => 'sometimes|required|in:credit_card,debit_card,bank_account,cash,check',
            'last_four' => 'sometimes|required|string|size:4',
            'card_type' => 'nullable|required_if:type,credit_card,debit_card|in:visa,mastercard,amex,discover',
            'bank_name' => 'nullable|required_if:type,bank_account|string|max:200',
            'account_type' => 'nullable|required_if:type,bank_account|in:checking,savings',
            'expiration_date' => 'nullable|required_if:type,credit_card,debit_card|date',
            'is_default' => 'boolean',
            'billing_address' => 'nullable|array',
            'billing_address.street' => 'required_with:billing_address|string',
            'billing_address.city' => 'required_with:billing_address|string',
            'billing_address.state' => 'required_with:billing_address|string',
            'billing_address.zip_code' => 'required_with:billing_address|string',
            'is_active' => 'boolean',
            'payment_token' => 'nullable|string|max:500',
        ]);

        // If this is set as default, unset other defaults for this tenant
        if (isset($validated['is_default']) && $validated['is_default']) {
            PaymentMethod::where('tenant_id', $validated['tenant_id'] ?? $paymentMethod->tenant_id)
                ->where('id', '!=', $paymentMethod->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $paymentMethod->update($validated);
        return response()->json($paymentMethod);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        // Don't allow deletion of default payment method
        if ($paymentMethod->is_default) {
            return response()->json([
                'message' => 'Cannot delete default payment method'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Don't allow deletion if there are associated payments
        if ($paymentMethod->payments()->exists()) {
            return response()->json([
                'message' => 'Cannot delete payment method with associated payments'
            ], Response::HTTP_BAD_REQUEST);
        }

        $paymentMethod->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Restore soft deleted payment method.
     */
    public function restore($id)
    {
        $paymentMethod = PaymentMethod::withTrashed()->findOrFail($id);
        $paymentMethod->restore();
        return response()->json($paymentMethod);
    }

    /**
     * Set payment method as default.
     */
    public function setDefault(PaymentMethod $paymentMethod)
    {
        // Unset other defaults for this tenant
        PaymentMethod::where('tenant_id', $paymentMethod->tenant_id)
            ->where('id', '!=', $paymentMethod->id)
            ->where('is_default', true)
            ->update(['is_default' => false]);

        $paymentMethod->update(['is_default' => true]);
        return response()->json($paymentMethod);
    }

    /**
     * Deactivate payment method.
     */
    public function deactivate(PaymentMethod $paymentMethod)
    {
        // Don't allow deactivation of default payment method
        if ($paymentMethod->is_default) {
            return response()->json([
                'message' => 'Cannot deactivate default payment method'
            ], Response::HTTP_BAD_REQUEST);
        }

        $paymentMethod->update(['is_active' => false]);
        return response()->json($paymentMethod);
    }

    /**
     * Activate payment method.
     */
    public function activate(PaymentMethod $paymentMethod)
    {
        $paymentMethod->update(['is_active' => true]);
        return response()->json($paymentMethod);
    }

    /**
     * Get payment method's payment history.
     */
    public function paymentHistory(PaymentMethod $paymentMethod)
    {
        $payments = $paymentMethod->payments()
            ->with(['bill', 'bill.lease'])
            ->orderBy('payment_date', 'desc')
            ->paginate(20);
            
        return response()->json($payments);
    }

    /**
     * Validate payment method (check expiration, etc.)
     */
    public function validatePaymentMethod(PaymentMethod $paymentMethod)
    {
        $validation = [
            'is_valid' => true,
            'issues' => [],
        ];

        // Check expiration for cards
        if (in_array($paymentMethod->type, ['credit_card', 'debit_card']) && $paymentMethod->expiration_date) {
            if (now()->greaterThan($paymentMethod->expiration_date)) {
                $validation['is_valid'] = false;
                $validation['issues'][] = 'Payment method has expired';
            } elseif (now()->addMonths(1)->greaterThan($paymentMethod->expiration_date)) {
                $validation['issues'][] = 'Payment method expires soon';
            }
        }

        // Check if payment method is active
        if (!$paymentMethod->is_active) {
            $validation['is_valid'] = false;
            $validation['issues'][] = 'Payment method is inactive';
        }

        return response()->json($validation);
    }

    /**
     * Get tenant's payment methods.
     */
    public function byTenant(Tenant $tenant)
    {
        $paymentMethods = $tenant->paymentMethods()
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->get();
            
        return response()->json($paymentMethods);
    }
}