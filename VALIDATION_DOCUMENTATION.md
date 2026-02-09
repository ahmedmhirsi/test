# 📋 Documentation des Validations - SmartNexus

## 🎯 Résumé des Améliorations

J'ai renforcé **tous les contrôles de saisie** dans votre application Symfony en ajoutant des validations PHP strictes au niveau de :
1. **L'entité User** (validation côté base de données)
2. **Tous les formulaires** (validation côté formulaires)

---

## 🔐 Types de Validations Implémentées

### ✅ Validation à Deux Niveaux

**1. Niveau Entité (src/Entity/User.php)**
- Validation automatique lors de la persistance en base de données
- Utilise les annotations `#[Assert\...]`
- Protection même si un formulaire est contourné

**2. Niveau Formulaire (src/Form/...)**
- Validation avant soumission au serveur
- Messages d'erreur personnalisés en français
- Contraintes dans le `constraints` de chaque champ

---

## 📁 Fichiers Modifiés

### 1️⃣ **src/Entity/User.php**
L'entité principale avec validations renforcées sur **tous les champs**.

#### 📝 Champs Validés

| Champ | Contraintes | Message d'erreur |
|-------|-------------|------------------|
| **email** | `NotBlank`, `Email` (mode strict) | "L'email est obligatoire" / "Veuillez entrer une adresse email valide (ex: nom@exemple.com)" |
| **nom** | `NotBlank`, `Length` (2-100), `Regex` (lettres uniquement) | "Le nom est obligatoire" / "Le nom doit contenir au moins 2 caractères" / "Le nom ne doit contenir que des lettres, espaces, tirets et apostrophes" |
| **prenom** | `NotBlank`, `Length` (2-100), `Regex` (lettres uniquement) | "Le prénom est obligatoire" / "Le prénom doit contenir au moins 2 caractères" / "Le prénom ne doit contenir que des lettres, espaces, tirets et apostrophes" |
| **phoneNumber** | `Regex` (format international) | "Numéro de téléphone invalide (ex: +33 6 12 34 56 78 ou 0612345678)" |
| **photo** | `Url` | "URL photo invalide" |
| **bio** | `Length` (max 1000) | "La biographie ne peut pas dépasser 1000 caractères" |
| **expertise** | `Length` (max 255) | "L'expertise ne peut pas dépasser 255 caractères" |

#### 🔒 Contraintes au Niveau Classe
```php
#[UniqueEntity(fields: ['email'], message: 'Cet email existe déjà')]
```
Empêche la création de comptes avec un email déjà existant.

---

### 2️⃣ **src/Form/UserType.php**
Formulaire d'administration pour créer/modifier un utilisateur.

#### 🆕 Validations Ajoutées

**Prénom & Nom :**
```php
'constraints' => [
    new Assert\NotBlank(['message' => 'Le prénom est obligatoire']),
    new Assert\Length([
        'min' => 2,
        'max' => 100,
        'minMessage' => 'Le prénom doit contenir au moins {{ limit }} caractères',
        'maxMessage' => 'Le prénom ne peut pas dépasser {{ limit }} caractères',
    ]),
    new Assert\Regex([
        'pattern' => '/^[a-zA-ZÀ-ÿ\s\-\']+$/',
        'message' => 'Le prénom ne doit contenir que des lettres',
    ]),
],
```

**Email :**
```php
'constraints' => [
    new Assert\NotBlank(['message' => 'L\'email est obligatoire']),
    new Assert\Email([
        'message' => 'Veuillez entrer une adresse email valide',
        'mode' => 'strict',
    ]),
],
```

**Mot de passe :**
```php
'constraints' => [
    new Assert\NotBlank(['message' => 'Veuillez entrer un mot de passe']),
    new Assert\Length([
        'min' => 8,  // ✅ Augmenté de 6 à 8
        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
    ]),
    new Assert\Regex([
        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
        'message' => 'Le mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule et un chiffre',
    ]),
],
```

**Téléphone :**
```php
'constraints' => [
    new Assert\Regex([
        'pattern' => '/^(\+)?[0-9\s\-\.]{8,20}$/',
        'message' => 'Numéro de téléphone invalide (ex: +33 6 12 34 56 78)',
    ]),
],
```

**Bio & Expertise :**
```php
// Bio
'constraints' => [
    new Assert\Length([
        'max' => 1000,
        'maxMessage' => 'La biographie ne peut pas dépasser {{ limit }} caractères',
    ]),
],

// Expertise
'constraints' => [
    new Assert\Length([
        'max' => 255,
        'maxMessage' => 'L\'expertise ne peut pas dépasser {{ limit }} caractères',
    ]),
],
```

---

### 3️⃣ **src/Form/RegistrationFormType.php**
Formulaire d'inscription publique (déjà bien validé ✅).

**Validations existantes (maintenues) :**
- Nom & Prénom : `NotBlank`, `Length` (2-100), `Regex` (lettres uniquement)
- Email : `NotBlank`, `Email` (mode strict)
- Téléphone : `Regex` (format flexible)
- Mot de passe : `NotBlank`, `Length` (min 8), `Regex` (majuscule + minuscule + chiffre)
- Conditions d'utilisation : `IsTrue`

---

### 4️⃣ **src/Form/ProfileFormType.php**
Formulaire de modification du profil utilisateur (déjà bien validé ✅).

**Validations existantes (maintenues) :**
- Nom & Prénom : `NotBlank`, `Length` (2-100)
- Email : `NotBlank`, `Email` (mode strict)
- Téléphone : `Regex`
- Bio : `Length` (max 1000)
- Expertise : `Length` (max 255)

---

### 5️⃣ **src/Form/ChangePasswordFormType.php**
Formulaire de changement de mot de passe (déjà bien validé ✅).

**Validations existantes (maintenues) :**
- Mot de passe actuel : `NotBlank`
- Nouveau mot de passe : `NotBlank`, `Length` (min 8), `Regex` (complexité)

---

## 🔍 Types de Contraintes Utilisées

### 1. **NotBlank**
Vérifie que le champ n'est pas vide.
```php
#[Assert\NotBlank(message: 'Ce champ est obligatoire')]
```

### 2. **Email**
Valide le format d'email (mode strict = RFC complet).
```php
#[Assert\Email(message: 'Email invalide', mode: 'strict')]
```

### 3. **Length**
Limite la longueur minimale et/ou maximale.
```php
#[Assert\Length(min: 2, max: 100, minMessage: '...', maxMessage: '...')]
```

### 4. **Regex**
Valide avec une expression régulière.
```php
#[Assert\Regex(pattern: '/^[a-zA-ZÀ-ÿ\s\-\']+$/', message: 'Lettres uniquement')]
```

### 5. **Url**
Valide une URL complète.
```php
#[Assert\Url(message: 'URL invalide')]
```

### 6. **UniqueEntity**
Vérifie l'unicité en base de données.
```php
#[UniqueEntity(fields: ['email'], message: 'Cet email existe déjà')]
```

---

## 🛡️ Sécurité Renforcée

### ✅ Expressions Régulières (Regex)

**Nom et Prénom :**
```regex
/^[a-zA-ZÀ-ÿ\s\-\']+$/
```
- `a-zA-Z` : Lettres anglaises minuscules et majuscules
- `À-ÿ` : Caractères accentués (é, è, ê, à, ù, ç, etc.)
- `\s` : Espaces
- `\-` : Tirets (ex: Jean-Pierre)
- `\'` : Apostrophes (ex: O'Connor)

**Téléphone :**
```regex
/^(\+)?[0-9\s\-\.]{8,20}$/
```
- `(\+)?` : + optionnel au début
- `[0-9\s\-\.]` : Chiffres, espaces, tirets, points
- `{8,20}` : Entre 8 et 20 caractères

Exemples valides :
- `+33 6 12 34 56 78`
- `0612345678`
- `01-23-45-67-89`

**Mot de passe :**
```regex
/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)$/
```
- `(?=.*[a-z])` : Au moins une minuscule
- `(?=.*[A-Z])` : Au moins une majuscule
- `(?=.*\d)` : Au moins un chiffre
- Minimum 8 caractères

Exemple valide : `Motdepasse123`

---

## 📊 Tableau Récapitulatif

| Formulaire | Fichier | Validations Ajoutées |
|------------|---------|---------------------|
| **Entité User** | `src/Entity/User.php` | ✅ Regex nom/prénom, Email strict, Length bio/expertise, Regex téléphone amélioré |
| **Admin UserType** | `src/Form/UserType.php` | ✅ Contraintes complètes sur tous les champs, mot de passe 8 caractères minimum |
| **Inscription** | `src/Form/RegistrationFormType.php` | ✅ Déjà complet (pas de modifications) |
| **Profil** | `src/Form/ProfileFormType.php` | ✅ Déjà complet (pas de modifications) |
| **Changement MDP** | `src/Form/ChangePasswordFormType.php` | ✅ Déjà complet (pas de modifications) |

---

## 🧪 Comment Tester

### 1. **Tester les validations dans les formulaires**

#### Formulaire d'inscription (`/register`) :
- ❌ Essayez de soumettre sans remplir les champs → Message "obligatoire"
- ❌ Entrez "test" comme email → Message "email invalide"
- ❌ Entrez un nom avec des chiffres "Jean123" → Message "lettres uniquement"
- ❌ Mot de passe "test" (4 caractères) → Message "au moins 8 caractères"
- ❌ Mot de passe "testtest" (pas de majuscule) → Message "doit contenir majuscule, minuscule et chiffre"
- ✅ Entrez "Test1234" → Validé !

#### Formulaire admin (`/admin/user/new`) :
- Mêmes tests que ci-dessus
- ❌ Bio avec 1500 caractères → Message "1000 caractères maximum"
- ❌ Téléphone "abc123" → Message "format invalide"
- ✅ Téléphone "+33 6 12 34 56 78" → Validé !

### 2. **Tester la validation au niveau entité**

Ouvrez un terminal Symfony et essayez :
```bash
php bin/console doctrine:validate:schema
```
Devrait afficher : `[OK] The mapping files are correct.`

### 3. **Tester en base de données**

Si vous essayez de créer un utilisateur avec un email existant :
```php
$user = new User();
$user->setEmail('admin@smartnexus.ai'); // Email déjà existant
$entityManager->persist($user);
$entityManager->flush(); // ❌ Exception UniqueEntity
```

---

## 🔄 Comment Ça Fonctionne

### Flux de Validation

```
┌─────────────────────┐
│ Utilisateur remplit │
│   le formulaire     │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Validation niveau   │ ◄── src/Form/*Type.php
│     Formulaire      │     (contraintes dans le buildForm)
└──────────┬──────────┘
           │
           ▼ Si valide
┌─────────────────────┐
│ Contrôleur reçoit   │
│    les données      │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Validation niveau   │ ◄── src/Entity/User.php
│      Entité         │     (annotations #[Assert\...])
└──────────┬──────────┘
           │
           ▼ Si valide
┌─────────────────────┐
│  Sauvegarde en BDD  │
└─────────────────────┘
```

### Avantages de la Double Validation

1. **Sécurité en profondeur** : Même si le formulaire est contourné (requête HTTP directe), l'entité refuse les données invalides
2. **Messages clairs** : Erreurs affichées directement dans le formulaire
3. **Performance** : Validation côté serveur PHP (pas de JavaScript désactivable)
4. **Maintenabilité** : Règles centralisées dans les fichiers PHP

---

## 🚀 Prochaines Étapes (Optionnel)

### Validation JavaScript (Frontend)
Pour améliorer l'expérience utilisateur, vous pouvez ajouter :
- Validation HTML5 native (`required`, `pattern`)
- Bibliothèque JavaScript (ex: Parsley.js, jQuery Validation)

### Validation Personnalisée
Créer des contraintes custom :
```php
// src/Validator/Constraints/StrongPassword.php
#[Attribute]
class StrongPassword extends Constraint
{
    public string $message = 'Le mot de passe doit contenir...';
}
```

---

## 📞 Support

Pour toute question sur les validations :
1. Consultez la [documentation Symfony Validation](https://symfony.com/doc/current/validation.html)
2. Vérifiez les logs : `var/log/dev.log`
3. Utilisez le profiler Symfony : `/_profiler`

---

## ✅ Résumé des Améliorations

| Aspect | Avant | Après |
|--------|-------|-------|
| **Entité User** | Validations basiques | ✅ Contraintes complètes avec Regex |
| **UserType (admin)** | Champs sans validation | ✅ Toutes les contraintes ajoutées |
| **Mot de passe min** | 6 caractères | ✅ 8 caractères |
| **Email validation** | Mode normal | ✅ Mode strict (RFC complet) |
| **Bio / Expertise** | Aucune limite | ✅ Limites 1000 / 255 caractères |
| **Téléphone** | Pattern restrictif | ✅ Pattern flexible international |
| **Messages d'erreur** | Génériques | ✅ Messages explicites en français |

---

**🎉 Votre application SmartNexus dispose maintenant d'un système de validation robuste et complet en PHP !**

---

## 🤖 Chatbot RAG - Assistant Intelligent

### 📖 Architecture du Système

Le chatbot SmartNexus AI utilise une architecture RAG (Retrieval-Augmented Generation) orchestrée par **n8n** :

```
┌─────────────────┐
│  Landing Page   │ ◄── Utilisateur pose une question
└────────┬────────┘
         │
         ▼
┌─────────────────────────┐
│ chatbot_widget.html.twig│ ◄── UI avec drag & drop
│    (Frontend JavaScript) │
└────────┬────────────────┘
         │ POST /api/chatbot/message
         ▼
┌─────────────────────────┐
│  ChatbotController.php  │ ◄── API REST endpoint
└────────┬────────────────┘
         │
         ▼
┌─────────────────────────┐
│   ChatbotService.php    │ ◄── Communique avec n8n
└────────┬────────────────┘
         │ HTTP POST
         ▼
┌─────────────────────────┐
│   n8n Webhook Workflow  │ ◄── Orchestration RAG
│  ┌──────────────────┐   │
│  │ 1. Reçoit query  │   │
│  │ 2. Embed (Ollama)│   │
│  │ 3. Search Qdrant │   │
│  │ 4. LLM (Llama)   │   │
│  │ 5. Return answer │   │
│  └──────────────────┘   │
└────────┬────────────────┘
         │
         ▼
┌─────────────────────────┐
│  Ollama (Llama 3.2)     │ ◄── Modèle LLM local
└─────────────────────────┘
         │
         ▼
┌─────────────────────────┐
│  Qdrant Vector DB       │ ◄── Base vectorielle
└─────────────────────────┘
```

---

### 📂 Fichiers Créés

#### 1️⃣ **src/Service/ChatbotService.php**
**Rôle :** Service Symfony qui communique avec le webhook n8n

**Méthode principale :**
```php
public function sendMessage(string $message, ?string $sessionId = null): array
```

**Paramètres :**
- `$message` : Question de l'utilisateur
- `$sessionId` : ID de session pour conserver le contexte conversationnel

**Retour :**
```php
[
    'success' => true,
    'response' => 'Réponse du chatbot...',
    'sessionId' => 'session_xyz123'
]
```

**Gestion des erreurs :**
- Timeout de 30 secondes
- Retourne un message d'erreur convivial si n8n est indisponible

---

#### 2️⃣ **src/Controller/Api/ChatbotController.php**
**Rôle :** API REST endpoint pour le chatbot

**Route :** `POST /api/chatbot/message`

**Payload attendu :**
```json
{
    "message": "Qu'est-ce que SmartNexus ?",
    "sessionId": "session_12345" // optionnel
}
```

**Réponse JSON :**
```json
{
    "success": true,
    "response": "SmartNexus est une plateforme...",
    "sessionId": "session_12345"
}
```

**Validation :**
- Vérifie que le message n'est pas vide
- Retourne une erreur 400 si la validation échoue

---

#### 3️⃣ **templates/components/chatbot_widget.html.twig**
**Rôle :** Widget UI du chatbot (frontend complet)

**Fonctionnalités :**
- ✅ **Bouton flottant** (bottom-right, icône smart_toy)
- ✅ **Fenêtre popup** (384x600px, design moderne)
- ✅ **Messages animés** (fadeIn animation)
- ✅ **Indicateur de chargement** (3 points animés)
- ✅ **Scroll automatique** vers le dernier message
- ✅ **Session persistante** (même ID pour toute la conversation)
- ✅ **Échappement XSS** (escapeHtml pour sécurité)

**Design :**
- Header dégradé navy → electric blue
- Messages bot : fond blanc, icône smart_toy
- Messages user : fond navy, icône person
- Input avec bouton d'envoi
- Footer "Propulsé par Llama 3.2 + Qdrant"

**JavaScript :**
```javascript
// Objet principal
chatbotWidget.init()        // Initialise les événements
chatbotWidget.openChat()    // Ouvre la fenêtre
chatbotWidget.sendMessage() // Envoie un message via fetch API
chatbotWidget.addMessage()  // Ajoute un message au DOM
```

---

### ⚙️ Configuration

#### **Fichier : config/services.yaml**
```yaml
parameters:
    n8n_webhook_url: '%env(default::N8N_WEBHOOK_URL)%'

services:
    App\Service\ChatbotService:
        arguments:
            $n8nWebhookUrl: '%n8n_webhook_url%'
```

#### **Fichier : .env**
Ajoutez cette ligne :
```bash
# URL du webhook n8n (workflow RAG)
N8N_WEBHOOK_URL=http://localhost:5678/webhook/chatbot
```

**⚠️ Important :** Remplacez par l'URL réelle de votre workflow n8n en production.

---

### 🔗 Intégration dans la Landing Page

#### **Fichier : templates/landing/index.html.twig**
Ajoutez ce code avant la fermeture de `</body>` :

```twig
{# Chatbot widget #}
{{ include('components/chatbot_widget.html.twig') }}
```

Le widget sera automatiquement visible en bas à droite de toutes les pages où vous l'incluez.

---

### 🧪 Tests

#### 1. **Test de l'API REST**
```bash
# Via curl (Windows PowerShell)
curl -X POST http://localhost:8000/api/chatbot/message `
  -H "Content-Type: application/json" `
  -d '{"message": "Bonjour"}'
```

**Réponse attendue :**
```json
{
    "success": true,
    "response": "Bonjour ! Comment puis-je vous aider ?",
    "sessionId": "session_1234567890"
}
```

#### 2. **Test du Service**
Créez un test unitaire :
```php
// tests/Service/ChatbotServiceTest.php
public function testSendMessage(): void
{
    $service = new ChatbotService($httpClient, 'http://localhost:5678/webhook/chatbot');
    $result = $service->sendMessage('Test');
    
    $this->assertTrue($result['success']);
    $this->assertArrayHasKey('response', $result);
    $this->assertArrayHasKey('sessionId', $result);
}
```

#### 3. **Test du Widget UI**
1. Ouvrez votre landing page : `http://localhost:8000`
2. Cliquez sur le bouton flottant (icône robot)
3. Tapez un message et appuyez sur Entrée
4. Vérifiez que la réponse s'affiche correctement

---

### 🐛 Débogage

#### **Problème : "Le chatbot est temporairement indisponible"**

**Causes possibles :**
1. n8n n'est pas démarré
2. L'URL du webhook est incorrecte
3. Le workflow n8n n'est pas activé
4. Ollama ou Qdrant ne sont pas accessibles

**Solutions :**
```bash
# 1. Vérifier que n8n est lancé
n8n start

# 2. Tester le webhook directement
curl -X POST http://localhost:5678/webhook/chatbot \
  -H "Content-Type: application/json" \
  -d '{"message": "Test", "sessionId": "test123"}'

# 3. Vérifier les logs Symfony
tail -f var/log/dev.log

# 4. Vérifier Ollama
curl http://localhost:11434/api/tags

# 5. Vérifier Qdrant
curl http://localhost:6333/collections
```

---

#### **Problème : Messages ne s'affichent pas**

**Solution :**
1. Ouvrez la console développeur (F12)
2. Vérifiez les erreurs JavaScript
3. Vérifiez que Material Icons est chargé :
```html
<!-- Dans base.html.twig -->
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
```

---

#### **Problème : Session perdue entre messages**

**Solution :**
Le widget génère un `sessionId` unique au chargement :
```javascript
sessionId: 'session_' + Date.now()
```

Pour persister entre rechargements de page, utilisez localStorage :
```javascript
sessionId: localStorage.getItem('chatbot_session') || 'session_' + Date.now(),

// Après réception de réponse
localStorage.setItem('chatbot_session', data.sessionId);
```

---

### 📊 Workflow n8n Attendu

**Étapes du workflow :**
1. **Webhook Trigger** → Reçoit `{ message, sessionId }`
2. **Ollama Embeddings** → Convertit le message en vecteur
3. **Qdrant Vector Search** → Trouve les documents similaires (top 5)
4. **Function Node** → Construit le prompt avec contexte
5. **Ollama Chat** → Génère la réponse avec Llama 3.2
6. **Respond to Webhook** → Retourne `{ response, sessionId }`

**Exemple de configuration n8n :**
```json
{
  "nodes": [
    {
      "name": "Webhook",
      "type": "n8n-nodes-base.webhook",
      "parameters": {
        "path": "chatbot",
        "responseMode": "responseNode",
        "options": {}
      }
    },
    {
      "name": "Ollama Embeddings",
      "type": "n8n-nodes-base.embeddings.ollama",
      "parameters": {
        "model": "llama3.2:latest",
        "text": "={{ $json.message }}"
      }
    },
    {
      "name": "Qdrant Search",
      "type": "n8n-nodes-base.vectorStore.qdrant",
      "parameters": {
        "operation": "search",
        "collectionName": "smartnexus_docs",
        "queryVector": "={{ $json.embedding }}",
        "limit": 5
      }
    },
    {
      "name": "Llama Chat",
      "type": "n8n-nodes-base.ollamaChat",
      "parameters": {
        "model": "llama3.2:latest",
        "prompt": "={{ $json.prompt }}"
      }
    },
    {
      "name": "Respond",
      "type": "n8n-nodes-base.respondToWebhook",
      "parameters": {
        "respondWith": "json",
        "responseBody": "={{ { \"response\": $json.response, \"sessionId\": $('Webhook').item.json.body.sessionId } }}"
      }
    }
  ]
}
```

---

### 🚀 Améliorations Futures

1. **Historique de conversation**
   - Stocker les messages en base de données (table `chatbot_messages`)
   - Afficher l'historique au chargement du widget

2. **Streaming des réponses**
   - Utiliser Server-Sent Events (SSE) pour afficher la réponse mot par mot

3. **Suggestions de questions**
   - Afficher des boutons avec questions prédéfinies

4. **Mode vocal**
   - Intégrer Web Speech API pour reconnaissance vocale

5. **Analytics**
   - Tracker les questions fréquentes avec Matomo/Google Analytics

---

### ✅ Checklist de Déploiement

- [x] ChatbotService.php créé avec gestion d'erreurs
- [x] ChatbotController.php avec validation stricte
- [x] Widget UI avec design moderne et animations
- [x] Configuration n8n_webhook_url dans services.yaml
- [x] JavaScript sans dépendances externes (fetch API vanilla)
- [ ] Ajouter `N8N_WEBHOOK_URL` dans .env
- [ ] Inclure widget dans landing page
- [ ] Configurer workflow n8n (Webhook + Ollama + Qdrant)
- [ ] Tester API endpoint avec curl
- [ ] Tester UI dans le navigateur
- [ ] Vérifier Material Icons chargé
- [ ] Clear cache Symfony : `php bin/console cache:clear`

---

**🎉 Votre chatbot RAG est prêt à utiliser ! Il combine la puissance de Llama 3.2, la rapidité de Qdrant et la flexibilité de n8n.**
