# Marketing Module - API Reference

## Lead API

### Create Lead
Creates a new lead in the system.

**Endpoint:** `POST /api/leads`

**Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
    "contact_name": "John Doe",
    "email": "john.doe@example.com",
    "company_name": "ACME Corporation",
    "phone": "+216 12 345 678",
    "position": "Marketing Director",
    "campaign_id": 1,
    "channel_id": 2
}
```

**Required Fields:**
- `contact_name` (string)
- `email` (string)
- `company_name` (string)

**Optional Fields:**
- `phone` (string)
- `position` (string)
- `campaign_id` (integer) - Links to existing campaign
- `channel_id` (integer) - Links to existing channel

**Success Response (201 Created):**
```json
{
    "success": true,
    "message": "Lead created successfully",
    "lead_id": 42
}
```

**Error Response (400 Bad Request):**
```json
{
    "success": false,
    "error": "Missing required field: email"
}
```

---

## Usage Examples

### cURL
```bash
curl -X POST http://localhost:8000/api/leads \
  -H "Content-Type: application/json" \
  -d '{
    "contact_name": "Ahmed Ben Ali",
    "email": "ahmed@company.tn",
    "company_name": "TechStart Tunisia",
    "phone": "+216 98 765 432",
    "position": "CTO",
    "campaign_id": 1
  }'
```

### JavaScript (Fetch)
```javascript
fetch('http://localhost:8000/api/leads', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        contact_name: 'Ahmed Ben Ali',
        email: 'ahmed@company.tn',
        company_name: 'TechStart Tunisia',
        phone: '+216 98 765 432',
        position: 'CTO',
        campaign_id: 1
    })
})
.then(res => res.json())
.then(data => console.log(data));
```

### Python (Requests)
```python
import requests

response = requests.post(
    'http://localhost:8000/api/leads',
    json={
        'contact_name': 'Ahmed Ben Ali',
        'email': 'ahmed@company.tn',
        'company_name': 'TechStart Tunisia',
        'phone': '+216 98 765 432',
        'position': 'CTO',
        'campaign_id': 1
    }
)

print(response.json())
```

### n8n Workflow
Use HTTP Request node with:
- Method: POST
- URL: `http://your-server:8000/api/leads`
- Body Type: JSON
- JSON Body: (map your form fields)

---

## Lead Statuses

| Status | Description |
|--------|-------------|
| `new` | Just created, not contacted |
| `contacted` | Initial contact made |
| `qualified` | Lead is qualified and interested |
| `converted` | Lead became a customer |
| `lost` | Lead is no longer interested |

---

## Campaign Statuses

| Status | Description |
|--------|-------------|
| `planned` | Campaign scheduled for future |
| `active` | Currently running |
| `paused` | Temporarily stopped |
| `completed` | Campaign finished |
