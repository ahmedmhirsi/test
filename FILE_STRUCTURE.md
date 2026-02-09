# Files to Copy for Integration

## Directory Structure
```
src/
├── Controller/
│   ├── MarketingController.php
│   ├── FrontController.php
│   └── LeadApiController.php
│
├── Entity/
│   ├── MarketingLead.php
│   ├── MarketingCampaign.php
│   ├── MarketingChannel.php
│   ├── MarketingCampaignChannel.php
│   ├── MarketingBudget.php
│   ├── MarketingPerformance.php
│   └── MarketingMessage.php
│
├── Form/
│   ├── MarketingLeadType.php
│   └── MarketingCampaignType.php
│
├── Repository/
│   ├── MarketingLeadRepository.php
│   ├── MarketingCampaignRepository.php
│   ├── MarketingChannelRepository.php
│   ├── MarketingBudgetRepository.php
│   └── MarketingPerformanceRepository.php
│
templates/
├── marketing/
│   ├── base.html.twig
│   ├── dashboard.html.twig
│   ├── campaigns/
│   │   ├── index.html.twig
│   │   ├── new.html.twig
│   │   └── edit.html.twig
│   ├── leads/
│   │   ├── index.html.twig
│   │   ├── show.html.twig
│   │   ├── new.html.twig
│   │   └── edit.html.twig
│   ├── channels/
│   │   └── index.html.twig
│   └── analytics/
│       └── index.html.twig
│
└── front/
    ├── base.html.twig
    ├── dashboard.html.twig
    ├── leads.html.twig
    ├── lead_show.html.twig
    ├── campaigns.html.twig
    └── campaign_show.html.twig
```

## Quick Copy Commands (PowerShell)

```powershell
# From Marketing project root, copy to target project
$source = "C:\Users\ridha\Desktop\Marketing"
$target = "C:\path\to\your\project"

# Copy Controllers
Copy-Item "$source\src\Controller\MarketingController.php" "$target\src\Controller\"
Copy-Item "$source\src\Controller\FrontController.php" "$target\src\Controller\"
Copy-Item "$source\src\Controller\LeadApiController.php" "$target\src\Controller\"

# Copy Entities
Copy-Item "$source\src\Entity\Marketing*.php" "$target\src\Entity\"

# Copy Forms
Copy-Item "$source\src\Form\Marketing*.php" "$target\src\Form\"

# Copy Repositories
Copy-Item "$source\src\Repository\Marketing*.php" "$target\src\Repository\"

# Copy Templates
Copy-Item -Recurse "$source\templates\marketing" "$target\templates\"
Copy-Item -Recurse "$source\templates\front" "$target\templates\"
```

## After Copying

1. Update namespaces if different
2. Run `php bin/console doctrine:migrations:diff`
3. Run `php bin/console doctrine:migrations:migrate`
4. Run `php bin/console cache:clear`
