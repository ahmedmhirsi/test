# Marketing Module - Integration Guide

## 📋 Overview

The Marketing module is a complete solution for managing leads, campaigns, channels, and budget tracking. It includes both a **Back-office** (admin) and a **Front-office** (client view).

---

## 🏗️ Architecture

```
src/
├── Controller/
│   ├── MarketingController.php    # Back-office (CRUD)
│   ├── FrontController.php        # Front-office (Read-only)
│   └── LeadApiController.php      # REST API for leads
├── Entity/
│   ├── MarketingLead.php
│   ├── MarketingCampaign.php
│   ├── MarketingChannel.php
│   ├── MarketingCampaignChannel.php
│   ├── MarketingBudget.php
│   ├── MarketingPerformance.php
│   └── MarketingMessage.php
├── Form/
│   ├── MarketingLeadType.php
│   └── MarketingCampaignType.php
├── Repository/
│   ├── MarketingLeadRepository.php
│   └── MarketingCampaignRepository.php
└── templates/
    ├── marketing/     # Back-office templates
    └── front/         # Front-office templates
```

---

## 🔗 Routes

### Back-Office (`/marketing/*`)

| Route | Method | Path | Description |
|-------|--------|------|-------------|
| `app_marketing_dashboard` | ANY | `/marketing` | Dashboard with stats |
| `app_marketing_campaigns` | ANY | `/marketing/campaigns` | List all campaigns |
| `app_marketing_campaign_new` | ANY | `/marketing/campaigns/new` | Create campaign |
| `app_marketing_campaign_show` | ANY | `/marketing/campaigns/{id}` | View campaign |
| `app_marketing_campaign_edit` | ANY | `/marketing/campaigns/{id}/edit` | Edit campaign |
| `app_marketing_campaign_delete` | POST | `/marketing/campaigns/{id}/delete` | Delete campaign |
| `app_marketing_leads` | ANY | `/marketing/leads` | List all leads |
| `app_marketing_lead_new` | ANY | `/marketing/leads/new` | Create lead |
| `app_marketing_lead_show` | ANY | `/marketing/leads/{id}` | View lead |
| `app_marketing_lead_edit` | ANY | `/marketing/leads/{id}/edit` | Edit lead |
| `app_marketing_lead_delete` | POST | `/marketing/leads/{id}/delete` | Delete lead |
| `app_marketing_channels` | ANY | `/marketing/channels` | List channels |
| `app_marketing_analytics` | ANY | `/marketing/analytics` | Analytics view |

### Front-Office (`/front/*`)

| Route | Method | Path | Description |
|-------|--------|------|-------------|
| `app_front_dashboard` | ANY | `/front` | Client dashboard |
| `app_front_leads` | ANY | `/front/leads` | View leads (read-only) |
| `app_front_lead_show` | ANY | `/front/leads/{id}` | View lead detail |
| `app_front_campaigns` | ANY | `/front/campaigns` | View campaigns |
| `app_front_campaign_show` | ANY | `/front/campaigns/{id}` | View campaign detail |

### API Endpoints

| Route | Method | Path | Description |
|-------|--------|------|-------------|
| `api_lead_create` | POST | `/api/leads` | Create lead via API |

---

## 🗄️ Database Schema

### MarketingLead
| Field | Type | Description |
|-------|------|-------------|
| id | int | Primary key |
| contact_name | string | Lead's full name |
| company_name | string | Company name |
| email | string | Email address |
| phone | string (nullable) | Phone number |
| position | string (nullable) | Job position |
| status | string | new, contacted, qualified, converted, lost |
| campaign_id | int (FK) | Related campaign |
| channel_id | int (FK) | Source channel |
| created_at | datetime | Creation date |
| updated_at | datetime | Last update |

### MarketingCampaign
| Field | Type | Description |
|-------|------|-------------|
| id | int | Primary key |
| name | string | Campaign name |
| objective | text (nullable) | Campaign objective |
| status | string | planned, active, paused, completed |
| target_leads | int | Target number of leads |
| start_date | date | Start date |
| end_date | date (nullable) | End date |
| created_by | string | Creator name |
| created_at | datetime | Creation date |

### MarketingChannel
| Field | Type | Description |
|-------|------|-------------|
| id | int | Primary key |
| name | string | Channel name (Facebook, LinkedIn, etc.) |
| type | string | social, email, website, referral, event |
| icon | string | Material icon name |
| is_active | bool | Active status |

---

## 🔧 Configuration

### Environment Variables (.env)
```env
DATABASE_URL="mysql://user:pass@localhost:3306/marketing?charset=utf8mb4"
```

### Security (config/packages/security.yaml)
```yaml
access_control:
    - { path: ^/login, roles: PUBLIC_ACCESS }
    - { path: ^/assistant, roles: ROLE_USER }
    # - { path: ^/marketing, roles: ROLE_ADMIN }  # Currently disabled
```

---

## 🔌 Integration Points

### 1. User Authentication
To integrate with your User module:
```php
// In any controller, inject the User
public function dashboard(#[CurrentUser] ?User $user)
{
    // Use $user for filtering or permissions
}
```

### 2. API Integration
External systems can create leads via POST:
```bash
curl -X POST http://localhost:8000/api/leads \
  -H "Content-Type: application/json" \
  -d '{
    "contact_name": "John Doe",
    "email": "john@example.com",
    "company_name": "ACME Inc",
    "campaign_id": 1
  }'
```

### 3. Navigation Links
Add to your base template:
```twig
<a href="{{ path('app_marketing_dashboard') }}">Marketing</a>
<a href="{{ path('app_front_dashboard') }}">Client View</a>
```

---

## 📦 Dependencies

```json
{
    "php": ">=8.1",
    "symfony/framework-bundle": "^7.0",
    "doctrine/orm": "^2.17",
    "doctrine/doctrine-bundle": "^2.11",
    "symfony/form": "^7.0",
    "symfony/security-bundle": "^7.0"
}
```

---

## 🚀 Setup Steps

1. **Copy entities** to your `src/Entity/` folder
2. **Copy controllers** to your `src/Controller/` folder
3. **Copy templates** to your `templates/` folder
4. **Copy form types** to your `src/Form/` folder
5. **Run migrations**:
   ```bash
   php bin/console doctrine:migrations:diff
   php bin/console doctrine:migrations:migrate
   ```
6. **Clear cache**:
   ```bash
   php bin/console cache:clear
   ```

---

## 📞 Contact

For questions about this module, contact the Marketing module developer.
