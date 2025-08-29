# Advanced Social Interactive Gallery

Eine fortschrittliche soziale Bildergalerie mit echtem serverseitigem Like- und View-System, implementiert mit PHP und .txt-Dateien.

## Features

- **Interaktive Bildergalerie** mit automatischem Slider
- **Echtes Like-System** mit serverseitiger Speicherung
- **View-Counter** mit persistenter Datenspeicherung
- **Animierte Like-Counter** mit rollenden Ziffern
- **Lazy Loading** für optimale Performance
- **Responsive Design** für alle Geräte
- **Modal-Ansicht** für große Bilder
- **Echtzeit-Updates** durch Polling-System

## Technische Details

### Frontend
- Vanilla JavaScript (ES6+)
- CSS3 mit modernen Animationen
- Intersection Observer API für Lazy Loading
- Fetch API für Server-Kommunikation

### Backend
- PHP 7.4+ für API-Endpunkte
- JSON-basierte Datenspeicherung in .txt-Dateien
- RESTful API-Design
- CORS-Unterstützung

### Datenspeicherung
Die Daten werden in folgenden .txt-Dateien gespeichert:
- `data/likes.txt` - Like-Zähler für jedes Bild
- `data/views.txt` - View-Zähler für jedes Bild
- `data/liked_images.txt` - Status der gelikten Bilder

## Installation

1. **Webserver-Setup**
   ```bash
   # Stellen Sie sicher, dass PHP installiert ist
   php --version
   
   # Starten Sie einen lokalen PHP-Server
   php -S localhost:8000
   ```

2. **Dateistruktur**
   ```
   /workspace
   ├── index.html          # Hauptdatei
   ├── api/                # PHP API-Endpunkte
   │   ├── toggle_like.php
   │   ├── increment_view.php
   │   ├── get_image_data.php
   │   └── get_updates.php
   ├── data/               # Automatisch erstellt
   │   ├── likes.txt
   │   ├── views.txt
   │   └── liked_images.txt
   └── README.md
   ```

3. **Berechtigungen**
   ```bash
   # Stellen Sie sicher, dass PHP Schreibrechte hat
   chmod 755 api/
   chmod 644 api/*.php
   ```

## API-Endpunkte

### POST /api/toggle_like.php
Liked oder unliked ein Bild.

**Request:**
```json
{
  "image_id": "1234567890.123",
  "action": "like" // oder "unlike"
}
```

**Response:**
```json
{
  "success": true,
  "image_id": "1234567890.123",
  "action": "like",
  "likes": 42,
  "liked": true
}
```

### POST /api/increment_view.php
Erhöht den View-Counter eines Bildes.

**Request:**
```json
{
  "image_id": "1234567890.123"
}
```

**Response:**
```json
{
  "success": true,
  "image_id": "1234567890.123",
  "views": 156
}
```

### GET /api/get_image_data.php?image_id=1234567890.123
Holt die aktuellen Daten eines Bildes.

**Response:**
```json
{
  "likes": 42,
  "views": 156,
  "liked": true
}
```

### GET /api/get_updates.php
Holt alle Updates für Echtzeit-Synchronisation.

**Response:**
```json
[
  {
    "image_id": "1234567890.123",
    "likes": 42,
    "views": 156
  },
  {
    "image_id": "1234567890.124",
    "likes": 15,
    "views": 89
  }
]
```

## Verwendung

1. **Öffnen Sie die Galerie**
   ```
   http://localhost:8000
   ```

2. **Interaktionen**
   - Klicken Sie auf ein Bild, um es im Modal zu öffnen
   - Klicken Sie auf das Herz-Symbol, um zu liken/unliken
   - Der Slider wechselt automatisch alle 8 Sekunden
   - Views werden automatisch beim Öffnen eines Bildes erhöht

3. **Echtzeit-Features**
   - Like-Counter werden sofort aktualisiert
   - View-Counter werden in Echtzeit synchronisiert
   - Polling alle 5 Sekunden für Updates von anderen Nutzern

## Datenformat

### likes.txt
```json
{
  "1234567890.123": 42,
  "1234567890.124": 15,
  "1234567890.125": 7
}
```

### views.txt
```json
{
  "1234567890.123": 156,
  "1234567890.124": 89,
  "1234567890.125": 203
}
```

### liked_images.txt
```json
{
  "1234567890.123": true,
  "1234567890.124": false,
  "1234567890.125": true
}
```

## Sicherheit

- **Input-Validierung** in allen PHP-Endpunkten
- **CORS-Headers** für Cross-Origin-Requests
- **Fehlerbehandlung** mit aussagekräftigen Nachrichten
- **Datei-Berechtigungen** werden automatisch gesetzt

## Performance

- **Lazy Loading** für Bilder außerhalb des Viewports
- **Optimistische UI-Updates** für bessere UX
- **Efficiente Datenspeicherung** in JSON-Format
- **Minimale Server-Requests** durch intelligentes Polling

## Browser-Unterstützung

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## Troubleshooting

### Häufige Probleme

1. **PHP-Fehler**
   ```bash
   # Überprüfen Sie PHP-Version
   php --version
   
   # Testen Sie PHP-Server
   php -S localhost:8000
   ```

2. **Schreibrechte**
   ```bash
   # Stellen Sie sicher, dass das data/ Verzeichnis beschreibbar ist
   chmod 755 data/
   chmod 644 data/*.txt
   ```

3. **CORS-Fehler**
   - Alle API-Endpunkte haben CORS-Headers
   - Überprüfen Sie Browser-Konsole für Details

4. **Daten werden nicht gespeichert**
   - Überprüfen Sie PHP-Fehler-Logs
   - Stellen Sie sicher, dass das data/ Verzeichnis existiert
   - Überprüfen Sie Datei-Berechtigungen

## Entwicklung

### Hinzufügen neuer Features

1. **Neue API-Endpunkte** in `api/` Verzeichnis
2. **Frontend-Logik** in `index.html` JavaScript
3. **Styling** in CSS-Bereich von `index.html`

### Debugging

```javascript
// Browser-Konsole für Frontend-Debugging
console.log('Gallery state:', gallery.allImages);

// PHP-Logs für Backend-Debugging
error_log('Debug message');
```

## Lizenz

Dieses Projekt ist Open Source und kann frei verwendet werden.
