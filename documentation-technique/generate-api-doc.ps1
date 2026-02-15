param(
  [string]$SourceDir = "../src",
  [string]$OutputFile = "./api-technique.md"
)

$root = Split-Path -Parent $MyInvocation.MyCommand.Path
$srcPath = Resolve-Path (Join-Path $root $SourceDir)
$outPath = Join-Path $root $OutputFile

$files = Get-ChildItem -Path $srcPath -Filter *.php -File | Sort-Object Name
$lines = @()
$lines += "# Documentation technique API REST"
$lines += ""
$lines += "Generation automatique depuis les sources PHP."
$lines += ""
$lines += "Date de generation: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")"
$lines += ""

foreach ($file in $files) {
  $classMatches = Select-String -Path $file.FullName -Pattern '^\s*class\s+([A-Za-z_][A-Za-z0-9_]*)' -AllMatches
  $funcMatches = Select-String -Path $file.FullName -Pattern '^\s*(public|protected|private)?\s*(static\s+)?function\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(' -AllMatches

  $lines += "## $($file.Name)"
  $lines += ""

  if ($classMatches.Count -gt 0) {
    $classNames = @()
    foreach ($cm in $classMatches) {
      foreach ($m in $cm.Matches) {
        $classNames += $m.Groups[1].Value
      }
    }
    $lines += "Classes: " + ($classNames -join ', ')
  } else {
    $lines += "Classes: (aucune declaration de classe)"
  }

  $lines += ""
  $lines += "Methodes:"

  if ($funcMatches.Count -gt 0) {
    foreach ($fm in $funcMatches) {
      foreach ($m in $fm.Matches) {
        $lines += "- ``$($m.Groups[3].Value)``"
      }
    }
  } else {
    $lines += "- (aucune methode detectee)"
  }

  $lines += ""
}

Set-Content -Path $outPath -Value $lines -Encoding UTF8
Write-Output "Documentation API generee: $outPath"
