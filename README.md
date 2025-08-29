# Advanced Social Interactive Gallery

Eine fortschrittliche Bildergalerie mit echtem serverseitigem Like- und View-System, implementiert mit PHP und .txt-Dateien.

## Features

- **Echtes Like-System**: Server-seitige Speicherung von Likes mit Benutzer-Tracking
- **View-Tracking**: Automatisches Zählen von Bildaufrufen mit Spam-Schutz
- **Real-time Updates**: Polling-System für Live-Updates
- **Responsive Design**: Optimiert für Desktop und Mobile
- **Lazy Loading**: Effiziente Bildladung
- **Animierte Like-Counter**: Smooth Digit-Rolling Animationen
- **Admin Dashboard**: Übersicht über Statistiken und Daten

## Dateistruktur

```
├── index.html          # Hauptgalerie-Interface
├── admin.php           # Admin-Dashboard für Statistiken
├── toggle_like.php     # Like/Unlike API
├── increment_view.php  # View-Increment API
├── get_image_data.php  # Bilddaten-API
├── get_updates.php     # Real-time Updates API
├── data/               # Datenverzeichnis (wird automatisch erstellt)
│   ├── likes.txt       # Like-Zähler
│   ├── liked_by.txt    # Benutzer-Like-Historie
│   ├── views.txt       # View-Zähler
│   ├── view_history.txt # Benutzer-View-Historie
│   └── last_update.txt # Zeitstempel für Updates
└── README.md           # Diese Datei
```

## Installation

1. **Webserver mit PHP**: Stellen Sie sicher, dass Sie einen Webserver mit PHP-Unterstützung haben (Apache, Nginx, etc.)

2. **Dateien hochladen**: Laden Sie alle Dateien in Ihr Webverzeichnis hoch

3. **Berechtigungen**: Stellen Sie sicher, dass PHP Schreibrechte für das `data/` Verzeichnis hat:
   ```bash
   chmod 755 data/
   chmod 644 data/*.txt
   ```

4. **Zugriff**: Öffnen Sie `index.html` in Ihrem Browser

## API-Endpunkte

### POST /toggle_like.php
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
  "new_likes": 42,
  "user_liked": true
}
```

### POST /increment_view.php
Erhöht den View-Counter für ein Bild (mit Spam-Schutz).

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
  "new_views": 156,
  "view_incremented": true
}
```

### GET /get_image_data.php?image_id=1234567890.123
Holt die aktuellen Daten für ein Bild.

**Response:**
```json
{
  "success": true,
  "image_id": "1234567890.123",
  "likes": 42,
  "views": 156,
  "liked": true
}
```

### GET /get_updates.php
Holt Real-time Updates für alle Bilder.

**Optional Parameters:**
- `since=timestamp` - Nur Updates seit diesem Zeitstempel
- `image_ids=id1,id2,id3` - Nur spezifische Bild-IDs

**Response:**
```json
{
  "success": true,
  "updates": [
    {
      "image_id": "1234567890.123",
      "likes": 42,
      "views": 156
    }
  ],
  "last_update": 1640995200
}
```

## Datenformat

### likes.txt
```json
{
  "1234567890.123": 42,
  "1234567890.124": 15
}
```

### liked_by.txt
```json
{
  "1234567890.123": ["192.168.1.1", "192.168.1.2"],
  "1234567890.124": ["192.168.1.1"]
}
```

### views.txt
```json
{
  "1234567890.123": 156,
  "1234567890.124": 89
}
```

### view_history.txt
```json
{
  "1234567890.123": {
    "192.168.1.1": 1640995200,
    "192.168.1.2": 1640995300
  }
}
```

## Sicherheitsfeatures

- **Spam-Schutz**: Views werden nur einmal pro Stunde pro IP gezählt
- **File Locking**: Verhindert Race Conditions bei gleichzeitigen Zugriffen
- **Input Validation**: Alle Eingaben werden validiert
- **CORS Headers**: Korrekte Cross-Origin-Konfiguration

## Admin Dashboard

Besuchen Sie `admin.php` für eine Übersicht über:

- Gesamtstatistiken (Bilder, Likes, Views, Benutzer)
- Top-Bilder nach Likes und Views
- Recent Activity
- Datei-Informationen

## Anpassungen

### Neue Bilder hinzufügen
Bearbeiten Sie die Arrays in `index.html`:
```javascript
this.imageUrls = [
    'https://images.unsplash.com/photo-...',
    // Neue URLs hier hinzufügen
];

this.imageNames = [
    "Bildname 1",
    // Neue Namen hier hinzufügen
];
```

### Polling-Intervall ändern
In `index.html`, Zeile ~800:
```javascript
setInterval(async () => {
    await this.checkForUpdates();
}, 10000); // 10 Sekunden
```

### View-Spam-Schutz anpassen
In `increment_view.php`, Zeile ~50:
```php
if ($currentTime - $lastViewTime < 3600) { // 1 Stunde = 3600 Sekunden
```

## Troubleshooting

### Fehler: "Could not fetch image data from server"
- Überprüfen Sie die PHP-Fehlerprotokolle
- Stellen Sie sicher, dass das `data/` Verzeichnis beschreibbar ist
- Überprüfen Sie die Netzwerkverbindung

### Fehler: "Failed to update like data"
- Überprüfen Sie die Dateiberechtigungen
- Stellen Sie sicher, dass genügend Speicherplatz verfügbar ist

### Keine Updates in Echtzeit
- Überprüfen Sie die Browser-Konsole auf JavaScript-Fehler
- Stellen Sie sicher, dass `get_updates.php` erreichbar ist

## Performance-Optimierungen

- **File Locking**: Verhindert Datenverlust bei hoher Last
- **Lazy Loading**: Bilder werden nur bei Bedarf geladen
- **Optimistic Updates**: UI wird sofort aktualisiert
- **Efficient Polling**: Nur bei Änderungen werden Updates gesendet

## Browser-Kompatibilität

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## Lizenz

Dieses Projekt steht unter der MIT-Lizenz.
