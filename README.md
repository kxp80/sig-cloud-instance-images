Advanced Social Interactive Gallery

Lokal starten (mit PHP Built-in Server):

1. PHP installieren (falls nicht vorhanden).
2. Im Projektverzeichnis starten:

```bash
php -S 0.0.0.0:8000 -t /workspace
```

3. Im Browser öffnen: `http://localhost:8000/`

Server-API:
- Endpoint: `/api/api.php`
- Aktionen: `get`, `get_many`, `like`, `unlike`, `view`
- Speicherung: Text-Dateien unter `/workspace/data/*.txt` (Format: `likes=0`, `views=0`)
