<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>KK Profiling Batch Print — {{ $profilingYear }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #111;
            background: #fff;
        }

        .kk-print-sheet {
            width: 100%;
            max-width: 194mm;
            margin: 0 auto;
            padding: 4mm 0;
            page-break-after: always;
            page-break-inside: avoid;
        }

        .kk-print-sheet:last-child {
            page-break-after: auto;
        }

        .kk-print-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 6px;
            border-bottom: 2px solid #213f99;
            padding-bottom: 6px;
        }

        .kk-print-header h1 {
            margin: 0;
            font-size: 15px;
            color: #213f99;
        }

        .kk-print-meta {
            margin: 2px 0 0;
            font-size: 9px;
            color: #555;
        }

        .kk-print-name {
            font-size: 12px;
            font-weight: 700;
            text-align: right;
            max-width: 45%;
            line-height: 1.3;
        }

        .kk-print-section-title {
            margin: 6px 0 4px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #213f99;
        }

        .kk-print-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 4px 8px;
        }

        .kk-print-field {
            border: 1px solid #d7dce5;
            border-radius: 4px;
            padding: 3px 5px;
            min-height: 28px;
        }

        .kk-print-label {
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #64748b;
            margin-bottom: 1px;
            line-height: 1.2;
        }

        .kk-print-value {
            font-size: 9px;
            font-weight: 600;
            line-height: 1.25;
            word-break: break-word;
        }

        .kk-print-signature {
            margin-top: 6px;
        }

        .kk-print-signature img {
            max-height: 42px;
            max-width: 180px;
            display: block;
            margin-top: 2px;
        }

        @media print {
            body { margin: 0; }
            .kk-print-sheet {
                max-width: none;
                padding: 0;
            }
        }
    </style>
</head>
<body onload="window.print()">
    @foreach ($records as $item)
        @include('Kabataan::partials.print-kk-profiling-sheet', [
            'registration' => $item['registration'],
            'formData' => $item['formData'],
            'profilingYear' => $profilingYear,
            'submittedAt' => $item['submittedAt'],
        ])
    @endforeach
</body>
</html>
