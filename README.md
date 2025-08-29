# Advanced Social Interactive Gallery

Eine moderne, interaktive Bildergalerie mit echtem Server-Backend für Likes und Views.

## Features

- **Responsive Design**: Funktioniert auf Desktop und Mobile
- **Auto-Slider**: Automatisch wechselnde Bilder im Hauptbereich
- **Modal-Ansicht**: Vollbild-Darstellung der Bilder
- **Like-System**: Echtes serverseitiges Like-System mit PHP
- **View-Counter**: Serverseitige Verfolgung der Bildaufrufe
- **Real-time Updates**: Automatische Aktualisierung von Likes und Views
- **Lazy Loading**: Optimierte Bildladung für bessere Performance

## Installation

1. **Webserver-Setup**: Stellen Sie sicher, dass PHP aktiviert ist
2. **Dateien hochladen**: Kopieren Sie alle Dateien in Ihr Webverzeichnis
3. **Berechtigungen setzen**: 
   ```bash
   chmod 755 data/
   ```
4. **Browser öffnen**: Navigieren Sie zu `index.html`

## Dateistruktur

```
/
├── index.html          # Hauptdatei der Galerie
├── api.php            # PHP Backend API
├── .htaccess          # URL-Rewriting für API
├── data/              # Datenverzeichnis (automatisch erstellt)
│   ├── .htaccess      # Schutz vor direktem Zugriff
│   ├── likes_*.txt    # Like-Counter für jedes Bild
│   ├── views_*.txt    # View-Counter für jedes Bild
│   └── user_likes_*.txt # User-Like-Status
└── README.md          # Diese Datei
```

## API Endpoints

### GET Requests
- `api/stats/{imageId}` - Alle Statistiken für ein Bild
- `api/likes/{imageId}` - Like-Count für ein Bild
- `api/views/{imageId}` - View-Count für ein Bild

### POST Requests
- `api/like` - Toggle Like-Status
- `api/view` - Inkrementiere View-Counter

### Request Format (POST)
```json
{
  "imageId": "unique_image_identifier"
}
```

### Response Format
```json
{
  "success": true,
  "data": {
    "imageId": "123456",
    "likes": 42,
    "views": 128,
    "isLiked": true,
    "action": "liked"
  }
}
```

## Technische Details

### Frontend (JavaScript)
- **ES6+ Features**: Moderne JavaScript-Syntax
- **Async/Await**: Für saubere API-Kommunikation
- **Intersection Observer**: Für Lazy Loading
- **CSS Transitions**: Smooth Animationen
- **Error Handling**: Robuste Fehlerbehandlung

### Backend (PHP)
- **RESTful API**: Saubere API-Struktur
- **File-Based Storage**: Einfache .txt-Dateien als Datenbank
- **CORS Headers**: Cross-Origin Resource Sharing
- **Error Handling**: Comprehensive Fehlerbehandlung
- **Security**: Geschütztes Datenverzeichnis

### Features im Detail

#### Like-System
- **Toggle-Funktionalität**: Like/Unlike mit einem Klick
- **Animierte Counter**: Smooth rollende Zahlen
- **Synchronisation**: Updates in allen UI-Elementen
- **Persistenz**: Serverseitige Speicherung

#### View-System
- **Automatisches Tracking**: Views werden bei Modal-Öffnung gezählt
- **Echtzeit-Updates**: Sofortige UI-Aktualisierung
- **Polling**: Regelmäßige Updates von anderen Nutzern

#### Performance
- **Lazy Loading**: Bilder werden nur bei Bedarf geladen
- **Image Optimization**: Unsplash API mit optimierten Parametern
- **Caching**: Browser-Caching für bessere Performance
- **Debouncing**: Verhindert zu häufige API-Calls

## Browser-Kompatibilität

- Chrome 70+
- Firefox 65+
- Safari 12+
- Edge 79+

## Anpassungen

### Neue Bilder hinzufügen
Bearbeiten Sie das `imageUrls` Array in `index.html`:

```javascript
this.imageUrls = [
    'https://images.unsplash.com/photo-YOUR-ID',
    // Weitere URLs...
];
```

### Bildnamen anpassen
Bearbeiten Sie das `imageNames` Array in `index.html`:

```javascript
this.imageNames = [
    "Ihr Bildname",
    // Weitere Namen...
];
```

### API-Endpunkt ändern
Ändern Sie die `apiBaseUrl` in der JavaScript-Klasse:

```javascript
this.apiBaseUrl = './ihr-api-pfad';
```

## Troubleshooting

### Likes funktionieren nicht
1. Prüfen Sie PHP-Aktivierung
2. Kontrollieren Sie Dateiberechtigungen für `data/`
3. Überprüfen Sie Browser-Konsole auf Fehler

### Bilder laden nicht
1. Überprüfen Sie Internetverbindung
2. Kontrollieren Sie Unsplash-URLs
3. Prüfen Sie Browser-Konsole auf CORS-Fehler

### Langsame Performance
1. Reduzieren Sie Anzahl der Bilder
2. Optimieren Sie Bildgrößen
3. Erhöhen Sie Polling-Intervall

## Lizenz

MIT License - Freie Nutzung für kommerzielle und nicht-kommerzielle Projekte.