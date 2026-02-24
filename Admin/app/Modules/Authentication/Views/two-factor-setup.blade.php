@extends('layouts.app')

@section('title', 'Setup Two-Factor Authentication')

@section('content')
<div class="flex items-center justify-center min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Enable Two-Factor Authentication
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Two-Factor Authentication (2FA) is required for all administrators
            </p>
        </div>

        <div class="bg-white shadow rounded-lg p-8">
            <div class="space-y-6">
                <!-- Instructions -->
                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                    <h3 class="text-lg font-medium text-blue-900 mb-2">Setup Instructions</h3>
                    <ol class="list-decimal list-inside space-y-2 text-sm text-blue-800">
                        <li>Install an authenticator app on your phone (Google Authenticator, Authy, 1Password, etc.)</li>
                        <li>Scan the QR code below with your authenticator app</li>
                        <li>Enter the 6-digit code from your app to confirm</li>
                        <li>Save your recovery codes in a secure location</li>
                    </ol>
                </div>

                <!-- QR Code -->
                <div class="text-center">
                    <div class="inline-block p-4 bg-white border-2 border-gray-300 rounded-lg">
                        {!! $QRCode !!}
                    </div>
                </div>

                <!-- Manual Entry -->
                <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                    <p class="text-sm font-medium text-gray-700 mb-2">Can't scan? Enter this code manually:</p>
                    <code class="block text-center text-lg font-mono bg-white px-4 py-2 rounded border border-gray-300 select-all">
                        {{ $secretKey }}
                    </code>
                </div>

                <!-- Confirmation Form -->
                <form method="POST" action="{{ route('two-factor.confirm') }}" class="space-y-4">
                    @csrf

                    @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                            Enter the 6-digit code from your authenticator app
                        </label>
                        <input 
                            id="code" 
                            name="code" 
                            type="text" 
                            inputmode="numeric"
                            pattern="[0-9]{6}"
                            maxlength="6"
                            required 
                            autofocus
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-center text-2xl font-mono tracking-widest focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('code') border-red-500 @enderror" 
                            placeholder="000000"
                        >
                    </div>

                    <button 
                        type="submit" 
                        class="w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Confirm and Enable 2FA
                    </button>
                </form>
            </div>
        </div>

        <div class="text-center text-xs text-gray-500">
            <p>Your security is our priority. 2FA adds an extra layer of protection to your account.</p>
        </div>
    </div>
</div>
@endsection
