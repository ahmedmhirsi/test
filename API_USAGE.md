# Documentation des Intégrations API

## 1. DeepSeek AI (via OpenRouter)
**Note**: DeepSeek remplace Gemini et Llama.

### Configuration
1.  Obtenez une clé API sur [OpenRouter](https://openrouter.ai/).
2.  Ajoutez-la dans `.env.local` :
    ```env
    OPENROUTER_API_KEY=votre_cle_api
    ```

### Usage
-   Dans le chat, tapez : `@AI` ou `!deepseek` suivi de votre question.
-   Exemple : `@AI Raconte une blague.`
-   **Réponse** : L'IA répondra directement dans le chat avec le préfixe `🤖 **DeepSeek AI:**`.

## 2. Twilio (SMS Notification)
**Fonctionnalité** : Envoi de SMS automatiques pour les événements urgents.
**Déclencheur** :
- Création d'un nouveau Meeting.
- **Condition** : L'utilisateur doit avoir renseigné son numéro de téléphone dans son profil (Format international : `+33...`, `+216...`).

## 3. SendGrid (Email Notification)
**Fonctionnalité** : Envoi d'emails transactionnels.
**Déclencheur** :
- Création d'un nouveau Meeting / Invitation.
- **Contenu** : Détails du meeting et lien pour rejoindre.

## 4. Slack (Channel Updates)
**Fonctionnalité** : Notifications globales dans un channel Slack.
**Déclencheur** :
- Création d'un nouveau Meeting.
- **Message** : "📅 [Meeting] Titre : Description..." envoyé au Webhook configuré.

## Configuration requise (.env.local)
```dotenv
# API Keys
SLACK_WEBHOOK_URL=https://hooks.slack.com/services/...
TWILIO_SID=AC...
TWILIO_TOKEN=...
TWILIO_NUMBER=+1...
GEMINI_API_KEY=AIza...
SENDGRID_API_KEY=SG...
```
