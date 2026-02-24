@extends('layouts.app')

@section('title', 'Two-Factor Challenge')

@section('content')
<div class="flex items-center justify-center min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Two-Factor Authentication
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Enter your authentication code to continue
            </p>
        </div>

        <div class="bg-white shadow rounded-lg p-8">
            <!-- Toggle between code and recovery code -->
            <div class="mb-6">
                <button 
                    id="toggleButton"
                    type="button"
                    class="text-sm text-blue-600 hover:text-blue-800"
                    onclick="toggleRecoveryCode()"
                >
                    Use recovery code instead
                </button>
            </div>

            <form method="POST" action="{{ url('/two-factor-challenge') }}">
                @csrf

                @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Authentication Code Input -->
                <div id="codeSection">
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                        Authentication Code
                    </label>
                    <input 
                        id="code" 
                        name="code" 
                        type="text" 
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        maxlength="6"
                        autofocus
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-center text-2xl font-mono tracking-widest focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                        placeholder="000000"
                    >
                    <p class="mt-2 text-xs text-gray-500">Enter the 6-digit code from your authenticator app</p>
                </div>

                <!-- Recovery Code Input (Hidden by default) -->
                <div id="recoverySection" style="display: none;">
                    <label for="recovery_code" class="block text-sm font-medium text-gray-700 mb-2">
                        Recovery Code
                    </label>
                    <input 
                        id="recovery_code" 
                        name="recovery_code" 
                        type="text" 
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-center font-mono tracking-wider focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                        placeholder="XXXXXXXXXX"
                    >
                    <p class="mt-2 text-xs text-gray-500">Enter one of your recovery codes</p>
                </div>

                <button 
                    type="submit" 
                    class="mt-6 w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    Verify
                </button>
            </form>
        </div>

        <div class="text-center text-xs text-gray-500">
            <p>Session will timeout after 10 minutes of inactivity</p>
        </div>
    </div>
</div>

<script>
function toggleRecoveryCode() {
    const codeSection = document.getElementById('codeSection');
    const recoverySection = document.getElementById('recoverySection');
    const toggleButton = document.getElementById('toggleButton');
    const codeInput = document.getElementById('code');
    const recoveryInput = document.getElementById('recovery_code');

    if (codeSection.style.display === 'none') {
        // Show code section
        codeSection.style.display = 'block';
        recoverySection.style.display = 'none';
        toggleButton.textContent = 'Use recovery code instead';
        codeInput.required = true;
        recoveryInput.required = false;
        recoveryInput.value = '';
    } else {
        // Show recovery section
        codeSection.style.display = 'none';
        recoverySection.style.display = 'block';
        toggleButton.textContent = 'Use authentication code instead';
        codeInput.required = false;
        codeInput.value = '';
        recoveryInput.required = true;
        recoveryInput.focus();
    }
}
</script>
@endsection
