<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Recovery Codes - Admin Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('modules/authentication/css/admin-auth.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('modules/authentication/css/admin-two-factor.css') }}?v={{ time() }}">
</head>
<body>
    <div class="background-animation">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <div class="two-factor-wrapper">
        <div class="two-factor-container" style="max-width: 700px;">
            <div class="two-factor-header">
                <div class="header-icon">
                    <img src="{{ asset('modules/authentication/images/SKOneportal_logo.webp') }}" alt="SK One Portal Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                </div>
                <h1>
                    @if(isset($regenerated) && $regenerated)
                        New Recovery Codes Generated
                    @else
                        Two-Factor Authentication Enabled!
                    @endif
                </h1>
                <p>Save these recovery codes in a secure location</p>
            </div>

            <div class="two-factor-body">
                <div class="space-y-6">
                    <!-- Warning -->
                    <div style="background: #fef3c7; border: 1px solid #fbbf24; border-radius: 10px; padding: 16px; display: flex; gap: 12px;">
                        <i class="fas fa-exclamation-triangle" style="color: #f59e0b; font-size: 20px; flex-shrink: 0;"></i>
                        <div style="flex: 1;">
                            <strong style="display: block; color: #92400e; margin-bottom: 8px; font-size: 14px;">Important: Store these codes securely</strong>
                            <ul style="list-style: disc; padding-left: 20px; font-size: 13px; color: #78350f; line-height: 1.8;">
                                <li>Each recovery code can only be used once</li>
                                <li>Use a recovery code if you lose access to your authenticator app</li>
                                <li>Store these codes in a password manager or secure location</li>
                                <li>Never share these codes with anyone</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Recovery Codes -->
                    <div>
                        <h3 style="font-size: 16px; font-weight: 600; color: #1e293b; margin-bottom: 16px;">Your Recovery Codes</h3>
                        <div style="background: #f8fafc; border: 2px solid #cbd5e1; border-radius: 12px; padding: 24px;">
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
                                @foreach($recoveryCodes as $code)
                                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; text-align: center;">
                                    <code style="font-size: 14px; font-family: monospace; color: #475569;">{{ $code }}</code>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                        <button 
                            onclick="printCodes()"
                            style="flex: 1; min-width: 150px; padding: 12px 16px; border: 2px solid #cbd5e1; background: white; color: #475569; font-size: 14px; font-weight: 600; border-radius: 10px; cursor: pointer; transition: all 0.3s ease;"
                            onmouseover="this.style.background='#f8fafc'"
                            onmouseout="this.style.background='white'"
                        >
                            <i class="fas fa-print"></i> Print Codes
                        </button>
                        <button 
                            onclick="downloadCodes()"
                            style="flex: 1; min-width: 150px; padding: 12px 16px; border: 2px solid #cbd5e1; background: white; color: #475569; font-size: 14px; font-weight: 600; border-radius: 10px; cursor: pointer; transition: all 0.3s ease;"
                            onmouseover="this.style.background='#f8fafc'"
                            onmouseout="this.style.background='white'"
                        >
                            <i class="fas fa-download"></i> Download
                        </button>
                        <a 
                            href="{{ route('dashboard') }}"
                            style="flex: 1; min-width: 150px; padding: 12px 16px; background: linear-gradient(135deg, #044fa7 0%, #1b7fbf 50%, #41a33b 100%); color: white; font-size: 14px; font-weight: 600; border-radius: 10px; text-decoration: none; text-align: center; transition: all 0.3s ease; display: inline-block;"
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 30px rgba(65, 163, 59, 0.3)'"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'"
                        >
                            <i class="fas fa-arrow-right"></i> Continue to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    const recoveryCodes = @json($recoveryCodes);

    function printCodes() {
        const printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Recovery Codes</title>');
        printWindow.document.write('<style>body { font-family: monospace; padding: 20px; } h1 { font-size: 18px; } .code { padding: 10px; margin: 5px 0; border: 1px solid #ccc; }</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write('<h1>SK OnePortal Admin - Recovery Codes</h1>');
        printWindow.document.write('<p>Store these codes in a secure location. Each code can only be used once.</p>');
        recoveryCodes.forEach(code => {
            printWindow.document.write(`<div class="code">${code}</div>`);
        });
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    }

    function downloadCodes() {
        let content = 'SK OnePortal Admin - Recovery Codes\n';
        content += '====================================\n\n';
        content += 'Store these codes in a secure location.\n';
        content += 'Each code can only be used once.\n\n';
        recoveryCodes.forEach((code, index) => {
            content += `${index + 1}. ${code}\n`;
        });
        
        const blob = new Blob([content], { type: 'text/plain' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'recovery-codes.txt';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }
    </script>
</body>
</html>
