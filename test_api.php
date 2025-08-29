<?php
/**
 * Test Script für die Gallery API
 * Führt grundlegende Tests der API-Endpunkte durch
 */

echo "<h1>Gallery API Test</h1>\n";

// Test 1: Verzeichnis-Erstellung
echo "<h2>Test 1: Verzeichnis-Erstellung</h2>\n";
$dataDir = './data';
if (!is_dir($dataDir)) {
    if (mkdir($dataDir, 0755, true)) {
        echo "✅ data/ Verzeichnis erfolgreich erstellt\n";
    } else {
        echo "❌ Fehler beim Erstellen des data/ Verzeichnisses\n";
    }
} else {
    echo "✅ data/ Verzeichnis existiert bereits\n";
}

// Test 2: Datei-Schreibrechte
echo "<h2>Test 2: Datei-Schreibrechte</h2>\n";
$testFile = $dataDir . '/test.txt';
if (file_put_contents($testFile, 'test')) {
    echo "✅ Schreibrechte funktionieren\n";
    unlink($testFile); // Aufräumen
} else {
    echo "❌ Keine Schreibrechte im data/ Verzeichnis\n";
}

// Test 3: JSON-Funktionen
echo "<h2>Test 3: JSON-Funktionen</h2>\n";
$testData = ['test' => 'value'];
$jsonString = json_encode($testData);
$decodedData = json_decode($jsonString, true);

if ($decodedData === $testData) {
    echo "✅ JSON-Funktionen funktionieren korrekt\n";
} else {
    echo "❌ JSON-Funktionen haben Probleme\n";
}

// Test 4: API-Endpunkte Verfügbarkeit
echo "<h2>Test 4: API-Endpunkte</h2>\n";
$endpoints = [
    'api/toggle_like.php',
    'api/increment_view.php',
    'api/get_image_data.php',
    'api/get_updates.php'
];

foreach ($endpoints as $endpoint) {
    if (file_exists($endpoint)) {
        echo "✅ $endpoint existiert\n";
    } else {
        echo "❌ $endpoint fehlt\n";
    }
}

// Test 5: PHP-Version
echo "<h2>Test 5: PHP-Version</h2>\n";
$version = phpversion();
echo "PHP Version: $version\n";
if (version_compare($version, '7.4.0', '>=')) {
    echo "✅ PHP-Version ist kompatibel\n";
} else {
    echo "⚠️ PHP-Version sollte 7.4+ sein\n";
}

// Test 6: Erforderliche PHP-Erweiterungen
echo "<h2>Test 6: PHP-Erweiterungen</h2>\n";
$required_extensions = ['json', 'fileinfo'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext Erweiterung geladen\n";
    } else {
        echo "❌ $ext Erweiterung fehlt\n";
    }
}

echo "<h2>Test abgeschlossen!</h2>\n";
echo "<p>Wenn alle Tests ✅ zeigen, sollte die Gallery funktionieren.</p>\n";
echo "<p><a href='index.html'>Zur Gallery</a></p>\n";
?>