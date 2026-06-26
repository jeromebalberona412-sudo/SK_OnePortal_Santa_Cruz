param(
    [Parameter(Mandatory = $true)]
    [string]$ImagePath
)

$ErrorActionPreference = 'Stop'

function Await-Operation {
    param(
        $AsyncOperation,
        [Type]$ResultType
    )

    $asTaskGeneric = ([System.WindowsRuntimeSystemExtensions].GetMethods() | Where-Object {
        $_.Name -eq 'AsTask' -and $_.GetParameters().Count -eq 1 -and $_.GetParameters()[0].ParameterType.Name -eq 'IAsyncOperation`1'
    })[0]

    $asTask = $asTaskGeneric.MakeGenericMethod($ResultType)
    $netTask = $asTask.Invoke($null, @($AsyncOperation))
    $netTask.Wait(-1) | Out-Null

    return $netTask.Result
}

if (-not (Test-Path -LiteralPath $ImagePath)) {
    Write-Output (@{ success = $false; message = 'Image file not found.' } | ConvertTo-Json -Compress)
    exit 1
}

try {
    Add-Type -AssemblyName System.Runtime.WindowsRuntime
    [Windows.Storage.StorageFile, Windows.Storage, ContentType = WindowsRuntime] | Out-Null
    [Windows.Graphics.Imaging.BitmapDecoder, Windows.Graphics.Imaging, ContentType = WindowsRuntime] | Out-Null
    [Windows.Media.Ocr.OcrEngine, Windows.Media.Ocr, ContentType = WindowsRuntime] | Out-Null
    [Windows.Graphics.Imaging.SoftwareBitmap, Windows.Graphics.Imaging, ContentType = WindowsRuntime] | Out-Null

    $resolved = (Resolve-Path -LiteralPath $ImagePath).Path
    $file = Await-Operation ([Windows.Storage.StorageFile]::GetFileFromPathAsync($resolved)) ([Windows.Storage.StorageFile])
    $stream = Await-Operation ($file.OpenAsync([Windows.Storage.FileAccessMode]::Read)) ([Windows.Storage.Streams.IRandomAccessStream])
    $decoder = Await-Operation ([Windows.Graphics.Imaging.BitmapDecoder]::CreateAsync($stream)) ([Windows.Graphics.Imaging.BitmapDecoder])
    $bitmap = Await-Operation ($decoder.GetSoftwareBitmapAsync()) ([Windows.Graphics.Imaging.SoftwareBitmap])
    $engine = [Windows.Media.Ocr.OcrEngine]::TryCreateFromUserProfileLanguages()

    if (-not $engine) {
        Write-Output (@{ success = $false; message = 'Windows OCR engine unavailable.' } | ConvertTo-Json -Compress)
        exit 1
    }

    $result = Await-Operation ($engine.RecognizeAsync($bitmap)) ([Windows.Media.Ocr.OcrResult])
    $lines = @()
    $confidences = @()

    foreach ($line in $result.Lines) {
        $text = ''
        if ($null -ne $line.Text) {
            $text = $line.Text.Trim()
        }

        if ($text -ne '') {
            $lines += @{ text = $text; confidence = 0.75 }
            $confidences += 0.75
        }
    }

    if ($lines.Count -eq 0) {
        Write-Output (@{ success = $false; message = 'No text detected in image.'; lines = @(); full_text = '' } | ConvertTo-Json -Compress -Depth 5)
        exit 0
    }

    $fullText = ($lines | ForEach-Object { $_.text }) -join ' '
    $avg = ($confidences | Measure-Object -Average).Average

    $payload = @{
        success = $true
        average_confidence = [math]::Round($avg, 3)
        lines = $lines
        full_text = $fullText
        engine = 'windows'
    }

    Write-Output ($payload | ConvertTo-Json -Compress -Depth 5)
    exit 0
}
catch {
    Write-Output (@{ success = $false; message = $_.Exception.Message } | ConvertTo-Json -Compress)
    exit 1
}
