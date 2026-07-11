# Sudamaseva — Testing

## Manual Testing Scenarios

### Auto Monthly Flow
1. Visit `/sudamaseva` → select "Auto Monthly" mode
2. Fill form with test name, phone, email, amount
3. Submit → Razorpay checkout should open with subscription
4. Complete payment with test card → redirect to success page
5. Check `sudamaseva_subscriptions` and `sudamaseva_payments` for records

### Pay Monthly Flow
1. Visit `/sudamaseva` → select "Pay Monthly" mode
2. Fill form → submit → Razorpay checkout with order (not subscription)
3. Complete payment → redirect to dashboard with paid installment
4. Check `collection_mode = 'manual'` and `payment_source = 'manual_order'`

### Donor Lookup
1. Visit `/sudamaseva/lookup` → enter phone of existing donor
2. Should redirect to dashboard with subscription/payment info

### Dashboard
1. Auto Monthly: View-only schedule with installments progress
2. Pay Monthly: Installment grid with "Pay Now" buttons on unpaid installments

## Known Gotchas
- Test with RAZORPAY_TEST_MODE=true in .env
- Use test card numbers from Razorpay documentation
- Webhook testing requires ngrok or similar tunnel
