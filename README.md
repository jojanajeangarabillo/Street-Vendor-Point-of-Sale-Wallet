# Project Name

VendorPay Stellar

## One-Line Description

A QR-code based digital payment system that enables street vendors to accept Stellar payments instantly without requiring a bank account.

## Track

**Track 2: Financial Inclusion & Everyday Payments**

## Problem It Solves

Millions of street vendors and small merchants still rely entirely on cash transactions because existing digital payment solutions often require bank accounts, expensive smartphones, or complex onboarding processes. This limits financial inclusion and prevents vendors from participating in the growing digital economy.

VendorPay Stellar provides a simple QR-code payment system where vendors generate a unique Stellar payment QR code. Customers scan the code using any Stellar-compatible wallet and send payment directly to the vendor's Stellar address. The vendor receives confirmation within seconds, enabling secure, fast, and cashless transactions even for micro-payments.

## How It Uses Stellar

VendorPay Stellar leverages the Stellar network for low-cost and near-instant payments.

Key Stellar integrations include:

* **Stellar Accounts** for vendor wallet management.
* **Stellar Payments API** to process direct peer-to-peer payments.
* **Stellar Testnet/Mainnet** for transaction settlement.
* **SEP-7 URI Generation** to create payment request QR codes.
* **Horizon API** for real-time transaction monitoring and payment confirmation.
* **Freighter Wallet Integration** for customer-side transaction signing.
* **USDC on Stellar** (optional) to provide stable-value payments and reduce volatility.

Payment Flow:

1. Vendor enters purchase amount.
2. System generates a SEP-7 compliant QR code.
3. Customer scans the QR code using a Stellar wallet.
4. Customer approves the transaction.
5. Stellar settles the payment in seconds.


## GitHub Repository

https://github.com/[your-organization]/vendorpay-stellar

## Network & Deployment

* Network: Testnet
* Live App URL (if any): Runs locally — see README
* Contract IDs / Asset Issuers: N/A (current version uses native Stellar payment operations)

## Team

* Jojana Jean B. Garabillo — @jojanajeangarabillo

## Novelty Note (Optional)

Unlike traditional QR payment systems that depend on banks, merchant acquiring services, or local payment processors, VendorPay Stellar allows direct wallet-to-wallet payments over Stellar. Street vendors can start accepting digital payments with only a Stellar wallet and a generated QR code, removing barriers such as bank account requirements, monthly fees, and expensive POS hardware.

The project focuses specifically on underserved micro-businesses and informal vendors, combining Stellar's fast settlement with a lightweight POS interface designed for low-cost devices.

## Anything Else

### Current Limitations

* Requires internet connectivity for payment verification.
* Currently supports Stellar-compatible wallets only.
* Merchant onboarding is manual.

### Future Enhancements

* Offline transaction queuing and synchronization.
* Inventory and sales analytics dashboard.
* Multi-vendor marketplace support.
* Soroban-powered loyalty and rewards system.
* Automated stablecoin conversion for price stability.

VendorPay Stellar aims to bring financial inclusion to street vendors by transforming any smartphone into a low-cost digital payment terminal powered by Stellar.
