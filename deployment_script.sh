#!/bin/bash
# psy-nexus-deployment script by Manus - Final Version

echo "Starte den finalen Deployment-Prozess für Psy-Nexus..."

# Definiere dein GitHub Repository URL
REPO_URL="https://github.com/poenixmoench/Psy-NEXUS-Tool.git"

# Definiere das lokale Verzeichnis, in das das Repository geklont wird
INSTALL_DIR=$(pwd)

echo "Klone/aktualisiere das Repository von $REPO_URL in $INSTALL_DIR..."

# Überprüfe, ob das Verzeichnis bereits existiert
if [ -d "$INSTALL_DIR/Psy-NEXUS-Tool" ]; then
    echo "Verzeichnis existiert bereits. Aktualisiere das Repository..."
    cd "$INSTALL_DIR/Psy-NEXUS-Tool"
    git pull
else
    echo "Klone das Repository..."
    git clone "$REPO_URL" "$INSTALL_DIR/Psy-NEXUS-Tool"
    cd "$INSTALL_DIR/Psy-NEXUS-Tool"
fi

echo "Repository wurde erfolgreich geklont/aktualisiert."
echo "Führe einen force-push aus, um den GitHub Pages Cache zu invalidieren..."

# Erstelle ein temporäres "Dummy-Commit", um den Cache zu zwingen, sich zu erneuern
git add -A
git commit --allow-empty -m "CI: Cache-Buster-Commit. Erzwinge Neu-Deployment." --no-verify

# Erzwinge den Push auf den Main-Branch. Dies ist der kritische Schritt.
git push origin main --force-with-lease

# Cleanup: Entferne den Dummy-Commit
git reset --hard HEAD~1

echo "Deployment-Prozess abgeschlossen. Der Cache von GitHub Pages sollte jetzt geleert sein."
echo "Bitte führe auf deinem Gerät einen Hard Refresh durch (Strg + F5 / Cmd + Shift + R)."
