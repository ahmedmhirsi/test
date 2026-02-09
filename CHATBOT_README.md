# 🤖 Chatbot RAG SmartNexus - Guide de Démarrage

## 📦 Installation Complète

### Étape 1 : Vérifier les fichiers créés

✅ **Fichiers créés automatiquement :**
- `src/Service/ChatbotService.php` - Service de communication avec n8n
- `src/Controller/Api/ChatbotController.php` - API REST endpoint
- `templates/components/chatbot_widget.html.twig` - Widget UI
- `config/services.yaml` - Configuration mise à jour
- `.env.local` - Variable d'environnement N8N_WEBHOOK_URL
- `public/test-chatbot.html` - Page de test de l'API

✅ **Fichiers modifiés :**
- `templates/home/index.html.twig` - Widget inclus dans la landing page
- `VALIDATION_DOCUMENTATION.md` - Documentation complète ajoutée

---

## 🚀 Démarrage Rapide (3 minutes)

### 1. Démarrer le serveur Symfony

```bash
cd "C:\Users\omarc\New folder\smartnexus"
symfony server:start
```

Ou avec PHP :
```bash
php -S localhost:8000 -t public
```

### 2. Vérifier que n8n est démarré

```bash
# Si n8n n'est pas lancé :
n8n start

# Ou en mode développement :
n8n start --tunnel
```

### 3. Tester l'API du chatbot

**Option A - Via la page de test :**
Ouvrez dans votre navigateur :
```
http://localhost:8000/test-chatbot.html
```

**Option B - Via curl (PowerShell) :**
```powershell
$body = @{
    message = "Bonjour, qu'est-ce que SmartNexus ?"
    sessionId = "test123"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/chatbot/message" `
    -Method POST `
    -ContentType "application/json" `
    -Body $body
```

**Réponse attendue :**
```json
{
    "success": true,
    "response": "SmartNexus AI est une plateforme de gestion des ressources humaines...",
    "sessionId": "test123"
}
```

### 4. Tester le widget sur la landing page

```
http://localhost:8000
```

Cliquez sur le bouton flottant en bas à droite (icône robot 🤖).

---

## ⚙️ Configuration du Workflow n8n

### Créer le workflow

1. **Ouvrir n8n :** `http://localhost:5678`

2. **Créer un nouveau workflow** avec les nœuds suivants :

#### Nœud 1 : Webhook (Trigger)
```yaml
Type: Webhook
Path: chatbot
Method: POST
Response Mode: Using 'Respond to Webhook' Node
```

#### Nœud 2 : Extract Message
```yaml
Type: Code (JavaScript)
Code:
  const message = $input.item.json.body.message;
  const sessionId = $input.item.json.body.sessionId || 'session_' + Date.now();
  return {
    json: { message, sessionId }
  };
```

#### Nœud 3 : Ollama Embeddings
```yaml
Type: Embeddings Ollama
Model: llama3.2:latest
Text: {{ $json.message }}
```

#### Nœud 4 : Qdrant Vector Search
```yaml
Type: Vector Store Qdrant
Operation: Retrieve Documents
Collection: smartnexus_docs
Query Vector: {{ $json.embedding }}
Limit: 5
```

#### Nœud 5 : Build Context
```yaml
Type: Code (JavaScript)
Code:
  const documents = $input.all().map(item => item.json.pageContent || item.json.text);
  const context = documents.join('\n\n');
  const message = $('Extract Message').item.json.message;
  const prompt = `Contexte:\n${context}\n\nQuestion: ${message}\n\nRéponse:`;
  return {
    json: { prompt, message }
  };
```

#### Nœud 6 : Ollama Chat Model
```yaml
Type: Chat Ollama
Model: llama3.2:latest
Prompt: {{ $json.prompt }}
Temperature: 0.7
Max Tokens: 500
```

#### Nœud 7 : Respond to Webhook
```yaml
Type: Respond to Webhook
Respond With: JSON
Response Body:
  {
    "response": "{{ $json.text }}",
    "sessionId": "{{ $('Extract Message').item.json.sessionId }}"
  }
```

3. **Activer le workflow**

4. **Copier l'URL du webhook** et mettre à jour `.env.local` :
```bash
N8N_WEBHOOK_URL=https://votre-n8n.app/webhook/chatbot
```

---

## 🔍 Vérifications (Checklist)

### ✅ Backend Symfony

```bash
# 1. Cache cleared
php bin/console cache:clear

# 2. Service registered
php bin/console debug:container ChatbotService

# 3. Route API exists
php bin/console debug:router | Select-String chatbot
```

**Résultat attendu :**
```
api_chatbot_message  POST  /api/chatbot/message
```

### ✅ n8n Workflow

```bash
# Test direct du webhook
curl -X POST http://localhost:5678/webhook/chatbot `
  -H "Content-Type: application/json" `
  -d '{"message":"test","sessionId":"test123"}'
```

### ✅ Ollama

```bash
# Vérifier que Llama 3.2 est installé
ollama list

# Tester Ollama
curl http://localhost:11434/api/tags
```

### ✅ Qdrant

```bash
# Vérifier les collections
curl http://localhost:6333/collections
```

**Réponse attendue (si collection existe) :**
```json
{
  "result": {
    "collections": [
      {"name": "smartnexus_docs"}
    ]
  }
}
```

---

## 🐛 Dépannage

### Problème 1 : "Le chatbot est temporairement indisponible"

**Causes possibles :**
- ❌ n8n n'est pas démarré
- ❌ URL du webhook incorrecte dans `.env.local`
- ❌ Workflow n8n non activé

**Solutions :**
```bash
# 1. Vérifier n8n
curl http://localhost:5678

# 2. Vérifier .env.local
cat .env.local

# 3. Tester le webhook directement
curl -X POST http://localhost:5678/webhook/chatbot \
  -H "Content-Type: application/json" \
  -d '{"message":"test"}'
```

### Problème 2 : Widget ne s'affiche pas

**Causes possibles :**
- ❌ Material Icons non chargé
- ❌ Erreur JavaScript

**Solutions :**
```html
<!-- Vérifier dans templates/base.html.twig -->
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
```

```bash
# Ouvrir la console navigateur (F12)
# Chercher les erreurs JavaScript
```

### Problème 3 : Messages ne s'envoient pas

**Solution :**
```javascript
// Ouvrir F12 → Console
// Vérifier les requêtes réseau → /api/chatbot/message
// Code 200 = OK
// Code 400 = Validation échouée
// Code 500 = Erreur serveur
```

### Problème 4 : Ollama ne répond pas

```bash
# Redémarrer Ollama
ollama serve

# Dans un autre terminal
ollama run llama3.2
```

---

## 📊 Architecture Complète

```
┌───────────────────┐
│   Utilisateur     │
└─────────┬─────────┘
          │ Tape un message
          ▼
┌─────────────────────────────┐
│  chatbot_widget.html.twig   │ ◄── Widget flottant (bottom-right)
│  • Bouton toggle            │
│  • Fenêtre popup 384x600px  │
│  • Messages animés          │
└─────────┬───────────────────┘
          │ fetch('/api/chatbot/message', ...)
          ▼
┌─────────────────────────────┐
│  ChatbotController.php      │ ◄── POST /api/chatbot/message
│  • Validation message       │
│  • Route API                │
└─────────┬───────────────────┘
          │ $chatbotService->sendMessage()
          ▼
┌─────────────────────────────┐
│   ChatbotService.php        │ ◄── HttpClient Symfony
│  • POST vers n8n webhook    │
│  • Gestion timeout 30s      │
│  • Gestion erreurs          │
└─────────┬───────────────────┘
          │ HTTP POST
          ▼
┌─────────────────────────────┐
│   n8n Workflow              │ ◄── Orchestration RAG
│  1. Webhook receive         │
│  2. Embeddings (Ollama)     │
│  3. Vector search (Qdrant)  │
│  4. Build context           │
│  5. LLM generation (Llama)  │
│  6. Respond to webhook      │
└─────────┬───────────────────┘
          │
          ├──────────┐
          ▼          ▼
    ┌─────────┐  ┌─────────┐
    │ Ollama  │  │ Qdrant  │
    │ :11434  │  │ :6333   │
    └─────────┘  └─────────┘
```

---

## 🎨 Personnalisation

### Changer le message de bienvenue

**Fichier :** `templates/components/chatbot_widget.html.twig`

```html
<!-- Ligne ~28 -->
<p class="text-sm text-gray-800">
    Votre nouveau message de bienvenue
</p>
```

### Modifier le style du widget

```html
<!-- Dans chatbot_widget.html.twig -->
<div id="chatbot-window" class="... w-96 h-[600px] ...">
    <!-- Changer w-96 pour la largeur -->
    <!-- Changer h-[600px] pour la hauteur -->
</div>
```

### Ajouter un historique de conversation

**Fichier :** `src/Service/ChatbotService.php`

```php
// Stocker en base de données
private function saveMessage(string $sessionId, string $message, string $response): void
{
    // Créer une entité ChatbotMessage
    // Sauvegarder avec Doctrine
}
```

---

## 📝 Commandes Utiles

```bash
# Démarrer tout le stack
symfony server:start &
n8n start &
ollama serve &

# Logs Symfony
tail -f var/log/dev.log

# Profiler Symfony
# http://localhost:8000/_profiler

# Debug routes
php bin/console debug:router

# Debug services
php bin/console debug:container | Select-String Chatbot

# Clear cache
php bin/console cache:clear
```

---

## 🎉 Résultat Final

**Ce que vous avez maintenant :**

✅ Chatbot RAG fonctionnel avec Llama 3.2 + Qdrant
✅ Widget UI moderne avec animations
✅ API REST `/api/chatbot/message`
✅ Page de test `test-chatbot.html`
✅ Documentation complète dans `VALIDATION_DOCUMENTATION.md`
✅ Gestion des sessions
✅ Gestion des erreurs
✅ Design responsive

**Prochaines étapes suggérées :**

1. Alimenter Qdrant avec vos documents SmartNexus
2. Améliorer le prompt système dans n8n
3. Ajouter un historique de conversation en BDD
4. Implémenter le streaming des réponses (SSE)
5. Ajouter des analytics (questions fréquentes)

---

**📧 Support :** Consultez `VALIDATION_DOCUMENTATION.md` section "Chatbot RAG" pour plus de détails.
