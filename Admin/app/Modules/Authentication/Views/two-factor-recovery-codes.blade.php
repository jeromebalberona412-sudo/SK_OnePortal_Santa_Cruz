@extends('layouts.app')

@section('title', 'Recovery Codes')

@section('content')
<div class="flex items-center justify-center min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                @if(isset($regenerated) && $regenerated)
                    New Recovery Codes Generated
                @else
                    Two-Factor Authentication Enabled!
                @endif
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Save these recovery codes in a secure location
            </p>
        </div>

        <div class="bg-white shadow rounded-lg p-8">
            <div class="space-y-6">
                <!-- Warning -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">
                                Important: Store these codes securely
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Each recovery code can only be used once</li>
                                    <li>You can use a recovery code if you lose access to your authenticator app</li>
                                    <li>Store these codes in a password manager or secure location</li>
                                    <li>Never share these codes with anyone</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recovery Codes -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Your Recovery Codes</h3>
                    <div class="bg-gray-50 border-2 border-gray-300 rounded-lg p-6">
                        <div class="grid grid-cols-2 gap-3">
                            @foreach($recoveryCodes as $code)
                            <div class="bg-white border border-gray-200 rounded px-4 py-3 text-center">
                                <code class="text-sm font-mono text-gray-800">{{ $code }}</code>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <button 
                        onclick="printCodes()"
                        class="flex-1 py-2 px-4 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Print Codes
                    </button>
                    <button 
                        onclick="downloadCodes()"
                        class="flex-1 py-2 px-4 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Download as Text
                    </button>
                    <a 
                        href="{{ route('dashboard') }}"
                        class="flex-1 py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 text-center"
                    >
                        Continue to Dashboard
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
@endsection
