<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota_{{ $order->order_number }} | Z-MART Boutique</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap">
    <style>
        :root {
            --slate-900: #0f172a;
            --slate-600: #475569;
            --slate-400: #94a3b8;
            --slate-200: #e2e8f0;
            --slate-50: #f8fafc;
            --primary: #4f46e5;
            --success: #10b981;
            --font: 'Inter', sans-serif;
            --heading: 'Outfit', sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font);
            color: var(--slate-900);
            background-color: #ffffff;
            padding: 40px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .invoice-wrapper {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid var(--slate-200);
            padding: 40px;
            border-radius: 8px;
            position: relative;
        }

        /* Top control bar (hidden when printing) */
        .control-bar {
            max-width: 800px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: var(--slate-50);
            padding: 12px 24px;
            border-radius: 8px;
            border: 1px solid var(--slate-200);
        }

        .btn-print {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-family: var(--font);
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-close {
            color: var(--slate-600);
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
        }

        /* Header design */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid var(--slate-200);
            padding-bottom: 24px;
            margin-bottom: 30px;
        }

        .brand {
            font-family: var(--heading);
            font-size: 28px;
            font-weight: 800;
            color: var(--slate-900);
            letter-spacing: -0.5px;
        }

        .brand-dot {
            width: 8px;
            height: 8px;
            background-color: var(--primary);
            border-radius: 50%;
            display: inline-block;
            margin-left: 2px;
        }

        .invoice-title {
            text-align: right;
        }

        .invoice-title h1 {
            font-family: var(--heading);
            font-size: 22px;
            font-weight: 700;
            color: var(--slate-900);
            text-transform: uppercase;
        }

        .invoice-title p {
            font-family: monospace;
            font-size: 14px;
            color: var(--slate-600);
            margin-top: 4px;
        }

        /* Billing detail grids */
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 30px;
        }

        .details-col h3 {
            font-family: var(--heading);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--slate-400);
            margin-bottom: 8px;
        }

        .details-col p {
            font-size: 14px;
            line-height: 1.5;
            color: var(--slate-900);
        }

        /* Table styles */
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .invoice-table th {
            background-color: var(--slate-50);
            font-family: var(--heading);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--slate-600);
            padding: 10px 16px;
            border-bottom: 1.5px solid var(--slate-200);
            text-align: left;
        }

        .invoice-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--slate-200);
            font-size: 14px;
            color: var(--slate-900);
        }

        /* Totals section */
        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 40px;
        }

        .totals-table {
            width: 300px;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 8px 16px;
            font-size: 14px;
        }

        .totals-table tr.grand-total td {
            font-family: var(--heading);
            font-size: 16px;
            font-weight: 700;
            border-top: 1.5px solid var(--slate-200);
            color: var(--primary);
            padding-top: 12px;
        }

        /* Paid stamp */
        .stamp-lunas {
            position: absolute;
            top: 150px;
            right: 80px;
            border: 4px dashed var(--success);
            color: var(--success);
            font-family: var(--heading);
            font-size: 24px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 8px 16px;
            border-radius: 8px;
            transform: rotate(-12deg);
            opacity: 0.85;
            user-select: none;
        }

        .stamp-pending {
            position: absolute;
            top: 150px;
            right: 80px;
            border: 4px dashed #f59e0b;
            color: #f59e0b;
            font-family: var(--heading);
            font-size: 24px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 8px 16px;
            border-radius: 8px;
            transform: rotate(-12deg);
            opacity: 0.85;
            user-select: none;
        }

        /* Print media overrides */
        @media print {
            body {
                padding: 0;
                background-color: transparent;
            }
            .invoice-wrapper {
                border: none;
                padding: 0;
            }
            .control-bar {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- Top control bar (hidden in print) -->
    <div class="control-bar">
        <a href="{{ route('user.dashboard') }}" class="btn-close">← Kembali ke Dashboard</a>
        <button class="btn-print" onclick="window.print()">
            🖨️ Cetak Nota (PDF / Paper)
        </button>
    </div>

    <!-- Main Printable Invoice Wrapper -->
    <div class="invoice-wrapper">
        
        <!-- Status Stamp -->
        @if($order->status === 'success')
            <div class="stamp-lunas">LUNAS / SENT</div>
        @elseif($order->status === 'pending')
            <div class="stamp-pending">PENDING</div>
        @endif

        <!-- Header -->
        <div class="invoice-header">
            <div>
                <span class="brand">Z-MART<span class="brand-dot"></span></span>
                <p style="font-size: 12px; color: var(--slate-600); margin-top: 4px;">Premium Boutique & Fashion Store</p>
            </div>
            <div class="invoice-title">
                <h1>Nota Penjualan</h1>
                <p>{{ $order->order_number }}</p>
                <p style="font-size: 12px; color: var(--slate-400); margin-top: 2px;">
                    Tanggal: {{ date('d F Y', strtotime($order->created_at)) }}
                </p>
            </div>
        </div>

        <!-- Billing details grid -->
        <div class="details-grid">
            <div class="details-col">
                <h3>Penerima & Alamat</h3>
                <p><strong>{{ $order->user ? $order->user->full_name : 'Customer' }}</strong></p>
                <p style="color: var(--slate-600); margin-bottom: 4px;">{{ $order->user ? $order->user->email : '' }}</p>
                <p style="margin-top: 8px;">{{ $order->shipping_address }}</p>
            </div>
            <div class="details-col">
                <h3>Metode Pembayaran</h3>
                <p><strong>{{ $order->payment_method ?? 'COD (Bayar di Tempat)' }}</strong></p>
                
                <h3 style="margin-top: 16px; margin-bottom: 4px;">Pengirim</h3>
                <p><strong>Z-MART Boutique</strong></p>
                <p style="color: var(--slate-600);">Jakarta Selatan, Indonesia</p>
            </div>
        </div>

        <!-- Itemized Table -->
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Item Pakaian</th>
                    <th style="width: 120px; text-align: right;">Harga Unit</th>
                    <th style="width: 80px; text-align: center;">Qty</th>
                    <th style="width: 140px; text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->product_name }}</strong>
                            <p style="font-size: 11px; color: var(--slate-400); margin-top: 2px;">
                                Kategori: Pakaian
                            </p>
                        </td>
                        <td style="text-align: right; font-family: monospace;">
                            Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                        </td>
                        <td style="text-align: center;">
                            {{ $item->quantity }}
                        </td>
                        <td style="text-align: right; font-weight: 600; font-family: monospace;">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary section -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td style="color: var(--slate-600);">Total Item:</td>
                    <td style="text-align: right; font-weight: 500;">{{ $order->items->sum('quantity') }} pcs</td>
                </tr>
                <tr>
                    <td style="color: var(--slate-600);">Pengiriman:</td>
                    <td style="text-align: right; color: var(--success); font-weight: 600;">GRATIS</td>
                </tr>
                <tr class="grand-total">
                    <td>Total Bayar:</td>
                    <td style="text-align: right;">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div style="text-align: center; border-top: 1px solid var(--slate-200); padding-top: 24px; color: var(--slate-400); font-size: 12px; line-height: 1.5;">
            <p>Terima kasih telah berbelanja pakaian di Z-MART Boutique!</p>
            <p>Harap simpan nota penjualan ini sebagai bukti transaksi pembelian yang sah.</p>
        </div>

    </div>

    <!-- Automatic print trigger on load -->
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 800);
        }
    </script>
</body>
</html>
