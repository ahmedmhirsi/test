# 💻 Code Complet des Pages - SmartNexus AI

Ce document contient le code complet de toutes les pages principales de l'application SmartNexus AI.

---

## 📑 Table des Matières

1. [Landing Page (Page d'Accueil)](#1-landing-page-page-daccueil)
2. [Page de Connexion (Login)](#2-page-de-connexion-login)
3. [Page d'Inscription (Register)](#3-page-dinscription-register)
4. [Dashboard Admin](#4-dashboard-admin)

---

## 1. Landing Page (Page d'Accueil)

**Fichier**: `templates/home/index.html.twig`

**Route**: `/` (app_home)

**Description**: Page d'accueil publique présentant SmartNexus AI avec hero section, features et CTA.

```twig
{% extends 'base.html.twig' %}

{% block title %}SmartNexus AI - Gestion Intelligente{% endblock %}

{% block body %}
<div class="min-h-screen bg-gradient-to-b from-background-light to-white dark:from-background-dark dark:to-[#1a1d2d]">
    <!-- Navigation -->
    <nav class="w-full border-b border-[#e5e7eb]/50 dark:border-[#2d3142]/50 bg-white/80 dark:bg-[#101218]/80 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="size-10 text-primary">
                    <svg class="h-full w-full" fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <path d="M36.7273 44C33.9891 44 31.6043 39.8386 30.3636 33.69C29.123 39.8386 26.7382 44 24 44C21.2618 44 18.877 39.8386 17.6364 33.69C16.3957 39.8386 14.0109 44 11.2727 44C7.25611 44 4 35.0457 4 24C4 12.9543 7.25611 4 11.2727 4C14.0109 4 16.3957 8.16144 17.6364 14.31C18.877 8.16144 21.2618 4 24 4C26.7382 4 29.123 8.16144 30.3636 14.31C31.6043 8.16144 33.9891 4 36.7273 4C40.7439 4 44 12.9543 44 24C44 35.0457 40.7439 44 36.7273 44Z" fill="currentColor"></path>
                    </svg>
                </div>
                <span class="text-2xl font-black tracking-tight text-navy dark:text-white">SmartNexus <span class="text-primary">AI</span></span>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ path('app_login') }}" class="px-5 py-2.5 text-sm font-bold text-navy dark:text-white hover:text-primary transition-colors">
                    Connexion
                </a>
                <a href="{{ path('app_register') }}" class="px-6 py-2.5 rounded-lg bg-primary hover:bg-primary/90 text-navy text-sm font-bold shadow-lg shadow-primary/30 transition-all">
                    S'inscrire
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 py-24 md:py-32">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="space-y-8">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-electric/10 text-electric text-sm font-bold">
                        <span class="material-symbols-outlined text-[18px]">auto_awesome</span>
                        Intelligence Artificielle intégrée
                    </div>
                    <h1 class="text-5xl md:text-6xl lg:text-7xl font-black tracking-tight text-navy dark:text-white leading-[1.1]">
                        Gestion des
                        <span class="relative">
                            <span class="bg-gradient-to-r from-primary via-electric to-primary bg-clip-text text-transparent">talents</span>
                            <span class="absolute bottom-2 left-0 w-full h-3 bg-primary/20 -z-10"></span>
                        </span>
                        simplifiée
                    </h1>
                    <p class="text-lg md:text-xl text-[#5e658d] dark:text-[#9ca3af] leading-relaxed max-w-xl">
                        SmartNexus AI révolutionne la gestion des ressources humaines grâce à l'intelligence artificielle. 
                        Recrutement, suivi des employés, gestion des compétences — tout en un seul endroit.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="{{ path('app_register') }}" class="flex items-center justify-center gap-2 px-8 py-4 rounded-xl bg-primary hover:bg-primary/90 text-navy text-base font-bold shadow-xl shadow-primary/30 transition-all">
                            <span>Commencer gratuitement</span>
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </a>
                        <a href="#features" class="flex items-center justify-center gap-2 px-8 py-4 rounded-xl border-2 border-navy/20 dark:border-white/20 hover:border-primary text-navy dark:text-white text-base font-bold transition-all">
                            <span class="material-symbols-outlined">play_circle</span>
                            <span>Voir la démo</span>
                        </a>
                    </div>
                    <div class="flex items-center gap-6 pt-4">
                        <div class="flex -space-x-3">
                            <div class="size-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 ring-2 ring-white flex items-center justify-center text-white font-bold text-sm">JD</div>
                            <div class="size-10 rounded-full bg-gradient-to-br from-green-400 to-green-600 ring-2 ring-white flex items-center justify-center text-white font-bold text-sm">ML</div>
                            <div class="size-10 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 ring-2 ring-white flex items-center justify-center text-white font-bold text-sm">KR</div>
                            <div class="size-10 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 ring-2 ring-white flex items-center justify-center text-white font-bold text-sm">+5k</div>
                        </div>
                        <div class="text-sm text-[#5e658d] dark:text-[#9ca3af]">
                            <span class="font-bold text-navy dark:text-white">5,000+</span> entreprises nous font confiance
                        </div>
                    </div>
                </div>
                <div class="relative hidden lg:block">
                    <div class="absolute -inset-4 bg-gradient-to-r from-primary/20 via-electric/20 to-primary/20 rounded-3xl blur-3xl"></div>
                    <div class="relative bg-white dark:bg-[#1a1d2d] rounded-2xl shadow-2xl border border-[#e5e7eb] dark:border-[#2d3142] p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-bold text-[#5e658d]">Dashboard SmartNexus</span>
                            <div class="flex gap-1.5">
                                <div class="size-3 rounded-full bg-red-400"></div>
                                <div class="size-3 rounded-full bg-yellow-400"></div>
                                <div class="size-3 rounded-full bg-green-400"></div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 p-4 rounded-xl">
                                <span class="material-symbols-outlined text-blue-600 mb-2">group</span>
                                <p class="text-2xl font-black text-navy dark:text-white">1,234</p>
                                <p class="text-xs text-[#5e658d]">Utilisateurs actifs</p>
                            </div>
                            <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 p-4 rounded-xl">
                                <span class="material-symbols-outlined text-green-600 mb-2">trending_up</span>
                                <p class="text-2xl font-black text-navy dark:text-white">+24%</p>
                                <p class="text-xs text-[#5e658d]">Croissance</p>
                            </div>
                        </div>
                        <div class="h-32 bg-gradient-to-r from-primary/10 via-electric/10 to-primary/10 rounded-xl flex items-center justify-center">
                            <span class="material-symbols-outlined text-5xl text-primary/50">analytics</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24 bg-white dark:bg-[#101218]">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary/10 text-primary text-sm font-bold mb-4">
                    <span class="material-symbols-outlined text-[18px]">star</span>
                    Fonctionnalités
                </span>
                <h2 class="text-4xl md:text-5xl font-black text-navy dark:text-white mb-4">
                    Tout ce dont vous avez besoin
                </h2>
                <p class="text-lg text-[#5e658d] dark:text-[#9ca3af] max-w-2xl mx-auto">
                    Une plateforme complète pour gérer l'ensemble de vos ressources humaines avec l'aide de l'IA.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="group p-8 rounded-2xl border border-[#e5e7eb] dark:border-[#2d3142] hover:border-primary/50 hover:shadow-xl transition-all bg-white dark:bg-[#1a1d2d]">
                    <div class="size-14 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center mb-6 shadow-lg shadow-purple-500/30">
                        <span class="material-symbols-outlined text-white text-[28px]">person_search</span>
                    </div>
                    <h3 class="text-xl font-bold text-navy dark:text-white mb-3">Gestion des Candidats</h3>
                    <p class="text-[#5e658d] dark:text-[#9ca3af] leading-relaxed">
                        Suivez le processus de recrutement de A à Z. CV, lettres de motivation, entretiens et décisions — tout centralisé.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="group p-8 rounded-2xl border border-[#e5e7eb] dark:border-[#2d3142] hover:border-primary/50 hover:shadow-xl transition-all bg-white dark:bg-[#1a1d2d]">
                    <div class="size-14 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center mb-6 shadow-lg shadow-blue-500/30">
                        <span class="material-symbols-outlined text-white text-[28px]">badge</span>
                    </div>
                    <h3 class="text-xl font-bold text-navy dark:text-white mb-3">Gestion des Employés</h3>
                    <p class="text-[#5e658d] dark:text-[#9ca3af] leading-relaxed">
                        Gérez vos équipes efficacement. Matricules, départements, postes et statuts en temps réel.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="group p-8 rounded-2xl border border-[#e5e7eb] dark:border-[#2d3142] hover:border-primary/50 hover:shadow-xl transition-all bg-white dark:bg-[#1a1d2d]">
                    <div class="size-14 rounded-xl bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center mb-6 shadow-lg shadow-green-500/30">
                        <span class="material-symbols-outlined text-white text-[28px]">psychology</span>
                    </div>
                    <h3 class="text-xl font-bold text-navy dark:text-white mb-3">Compétences & IA</h3>
                    <p class="text-[#5e658d] dark:text-[#9ca3af] leading-relaxed">
                        Cartographiez les compétences de votre organisation. L'IA vous aide à identifier les talents et les besoins.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="group p-8 rounded-2xl border border-[#e5e7eb] dark:border-[#2d3142] hover:border-primary/50 hover:shadow-xl transition-all bg-white dark:bg-[#1a1d2d]">
                    <div class="size-14 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center mb-6 shadow-lg shadow-orange-500/30">
                        <span class="material-symbols-outlined text-white text-[28px]">security</span>
                    </div>
                    <h3 class="text-xl font-bold text-navy dark:text-white mb-3">Sécurité Avancée</h3>
                    <p class="text-[#5e658d] dark:text-[#9ca3af] leading-relaxed">
                        Authentification 2FA (TOTP/SMS), codes de secours, vérification email. Vos données sont protégées.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="group p-8 rounded-2xl border border-[#e5e7eb] dark:border-[#2d3142] hover:border-primary/50 hover:shadow-xl transition-all bg-white dark:bg-[#1a1d2d]">
                    <div class="size-14 rounded-xl bg-gradient-to-br from-pink-500 to-pink-600 flex items-center justify-center mb-6 shadow-lg shadow-pink-500/30">
                        <span class="material-symbols-outlined text-white text-[28px]">folder_managed</span>
                    </div>
                    <h3 class="text-xl font-bold text-navy dark:text-white mb-3">Gestion de Projets</h3>
                    <p class="text-[#5e658d] dark:text-[#9ca3af] leading-relaxed">
                        Assignez des chefs de projet, suivez leur charge de travail et optimisez les ressources.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="group p-8 rounded-2xl border border-[#e5e7eb] dark:border-[#2d3142] hover:border-primary/50 hover:shadow-xl transition-all bg-white dark:bg-[#1a1d2d]">
                    <div class="size-14 rounded-xl bg-gradient-to-br from-cyan-500 to-cyan-600 flex items-center justify-center mb-6 shadow-lg shadow-cyan-500/30">
                        <span class="material-symbols-outlined text-white text-[28px]">analytics</span>
                    </div>
                    <h3 class="text-xl font-bold text-navy dark:text-white mb-3">Tableaux de Bord</h3>
                    <p class="text-[#5e658d] dark:text-[#9ca3af] leading-relaxed">
                        Visualisez vos KPIs en temps réel. Statistiques de recrutement, turnover, compétences et plus.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-24 bg-gradient-to-r from-navy via-[#283593] to-navy">
        <div class="max-w-4xl mx-auto px-6 text-center">
            <h2 class="text-4xl md:text-5xl font-black text-white mb-6">
                Prêt à transformer votre gestion RH?
            </h2>
            <p class="text-lg text-white/80 mb-8 max-w-2xl mx-auto">
                Rejoignez des milliers d'entreprises qui font confiance à SmartNexus AI pour gérer leurs talents.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ path('app_register') }}" class="px-8 py-4 rounded-xl bg-primary hover:bg-primary/90 text-navy text-base font-bold shadow-xl shadow-primary/30 transition-all">
                    Créer un compte gratuit
                </a>
                <a href="#" class="px-8 py-4 rounded-xl border-2 border-white/30 hover:border-white text-white text-base font-bold transition-all">
                    Nous contacter
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 bg-[#0d1017] text-white">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-3">
                    <div class="size-8 text-primary">
                        <svg class="h-full w-full" fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                            <path d="M36.7273 44C33.9891 44 31.6043 39.8386 30.3636 33.69C29.123 39.8386 26.7382 44 24 44C21.2618 44 18.877 39.8386 17.6364 33.69C16.3957 39.8386 14.0109 44 11.2727 44C7.25611 44 4 35.0457 4 24C4 12.9543 7.25611 4 11.2727 4C14.0109 4 16.3957 8.16144 17.6364 14.31C18.877 8.16144 21.2618 4 24 4C26.7382 4 29.123 8.16144 30.3636 14.31C31.6043 8.16144 33.9891 4 36.7273 4C40.7439 4 44 12.9543 44 24C44 35.0457 40.7439 44 36.7273 44Z" fill="currentColor"></path>
                        </svg>
                    </div>
                    <span class="text-lg font-bold">SmartNexus AI</span>
                </div>
                <p class="text-sm text-white/60">© 2025 SmartNexus AI. Tous droits réservés.</p>
            </div>
        </div>
    </footer>
</div>
{% endblock %}
```

---

## 2. Page de Connexion (Login)

**Fichier**: `templates/security/login.html.twig`

**Route**: `/login` (app_login)

**Description**: Formulaire de connexion avec panneau gauche branding et panneau droit pour le formulaire.

```twig
{% extends 'base.html.twig' %}

{% block title %}Connexion - SmartNexus AI{% endblock %}

{% block body %}
<div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="w-full border-b border-solid border-gray-200 px-6 py-4 bg-white z-50">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-2 text-navy">
                <div class="size-8 text-electric">
                    <svg fill="currentColor" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <path d="M36.7273 44C33.9891 44 31.6043 39.8386 30.3636 33.69C29.123 39.8386 26.7382 44 24 44C21.2618 44 18.877 39.8386 17.6364 33.69C16.3957 39.8386 14.0109 44 11.2727 44C7.25611 44 4 35.0457 4 24C4 12.9543 7.25611 4 11.2727 4C14.0109 4 16.3957 8.16144 17.6364 14.31C18.877 8.16144 21.2618 4 24 4C26.7382 4 29.123 8.16144 30.3636 14.31C31.6043 8.16144 33.9891 4 36.7273 4C40.7439 4 44 12.9543 44 24C44 35.0457 40.7439 44 36.7273 44Z"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-bold tracking-tight">SmartNexus AI</h2>
            </div>
            <div class="flex items-center gap-4">
                <span class="hidden md:block text-sm text-gray-500">Nouveau sur SmartNexus?</span>
                <a href="{{ path('app_register') }}" class="flex min-w-[140px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 bg-navy text-white text-sm font-bold transition-all hover:bg-electric">
                    <span class="truncate">Créer un compte</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex overflow-hidden">
        <!-- Left Panel - Branding -->
        <div class="hidden lg:flex w-1/2 relative items-center justify-center p-20 overflow-hidden" style="background-color: #1A237E; background-image: radial-gradient(at 0% 0%, rgba(83, 109, 254, 0.4) 0, transparent 50%), radial-gradient(at 100% 100%, rgba(255, 193, 7, 0.15) 0, transparent 50%);">
            <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(circle at 2px 2px, #FFC107 1px, transparent 0); background-size: 40px 40px;"></div>
            
            <div class="relative z-10 flex flex-col gap-12 max-w-lg">
                <div class="space-y-6">
                    <h1 class="text-white text-5xl font-black tracking-tight leading-tight">
                        Empowering the next generation of AI collaboration.
                    </h1>
                    <div class="h-1.5 w-20 bg-primary rounded-full"></div>
                </div>
                
                <div class="bg-navy/40 backdrop-blur-xl border border-white/10 p-8 rounded-xl shadow-2xl">
                    <div class="flex gap-1 mb-4">
                        {% for i in 1..5 %}
                        <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">star</span>
                        {% endfor %}
                    </div>
                    <p class="text-white text-lg font-medium italic leading-relaxed mb-6">
                        "SmartNexus transformed our workflow. The talent pool is unmatched and the AI integration is seamless."
                    </p>
                    <div class="flex items-center gap-4">
                        <div class="size-12 rounded-full bg-gradient-to-br from-primary to-electric border-2 border-primary flex items-center justify-center">
                            <span class="text-navy font-bold text-lg">DC</span>
                        </div>
                        <div>
                            <p class="text-white font-bold">David Chen</p>
                            <p class="text-primary/90 text-sm">CTO, TechCorp Global</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="absolute -bottom-20 -right-20 size-80 bg-electric/20 blur-[100px] rounded-full"></div>
        </div>

        <!-- Right Panel - Login Form -->
        <div class="w-full lg:w-1/2 bg-white flex items-center justify-center p-6 md:p-12 overflow-y-auto">
            <div class="w-full max-w-md flex flex-col gap-8">
                <div class="flex flex-col gap-2">
                    <h2 class="text-navy text-3xl font-bold tracking-tight">Portail d'accès sécurisé</h2>
                    <p class="text-gray-500 text-base">Entrez vos identifiants pour accéder au tableau de bord SmartNexus.</p>
                </div>

                {% if error %}
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center gap-3">
                    <span class="material-symbols-outlined text-red-500">error</span>
                    <span class="text-sm font-medium">{{ error.messageKey|trans(error.messageData, 'security') }}</span>
                </div>
                {% endif %}

                <form method="post" class="flex flex-col gap-5" novalidate>
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-bold text-navy" for="email">Adresse Email</label>
                        <input type="email" 
                               id="email" 
                               name="_username" 
                               value="{{ last_username }}"
                               required 
                               autofocus
                               class="h-12 px-4 rounded-lg border border-gray-300 bg-transparent focus:ring-2 focus:ring-electric focus:border-electric outline-none text-navy"
                               placeholder="nom@entreprise.com">
                    </div>

                    <div class="flex flex-col gap-2">
                        <div class="flex justify-between items-center">
                            <label class="text-sm font-bold text-navy" for="password">Mot de passe</label>
                            <a href="#" class="text-sm text-electric font-bold hover:underline">Mot de passe oublié?</a>
                        </div>
                        <input type="password" 
                               id="password" 
                               name="_password" 
                               required
                               class="h-12 px-4 rounded-lg border border-gray-300 bg-transparent focus:ring-2 focus:ring-electric focus:border-electric outline-none text-navy"
                               placeholder="••••••••">
                    </div>

                    <div class="flex items-center gap-2 mt-2">
                        <input type="checkbox" 
                               id="remember_me" 
                               name="_remember_me"
                               class="size-4 rounded border-gray-300 text-electric focus:ring-electric">
                        <label class="text-sm text-gray-600" for="remember_me">Rester connecté pendant 30 jours</label>
                    </div>

                    <input type="hidden" name="_csrf_token" value="{{ csrf_token('authenticate') }}">

                    <button type="submit" class="w-full h-12 bg-primary hover:brightness-105 text-navy font-bold rounded-lg transition-all shadow-lg shadow-primary/20 flex items-center justify-center gap-2">
                        Connexion
                        <span class="material-symbols-outlined text-[20px]">login</span>
                    </button>
                </form>

                <div class="relative flex items-center py-2">
                    <div class="flex-grow border-t border-gray-200"></div>
                    <span class="flex-shrink mx-4 text-xs uppercase tracking-widest text-gray-400 font-bold">Ou continuer avec</span>
                    <div class="flex-grow border-t border-gray-200"></div>
                </div>

                <button type="button" class="flex w-full items-center justify-center gap-3 h-12 px-6 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 transition-colors text-gray-700 font-bold">
                    <svg class="size-5" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"></path>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"></path>
                    </svg>
                    Se connecter avec Google
                </button>

                <div class="text-center pt-4">
                    <p class="text-sm text-gray-500">
                        Vous n'avez pas de compte? 
                        <a href="{{ path('app_register') }}" class="text-electric font-bold hover:underline">Créer un compte</a>
                    </p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer Mobile -->
    <footer class="lg:hidden w-full border-t border-gray-200 p-6 text-center bg-white">
        <p class="text-xs text-gray-400 uppercase tracking-widest font-bold">© {{ "now"|date("Y") }} SmartNexus AI Inc. Tous droits réservés.</p>
    </footer>
</div>
{% endblock %}
```

---

## 3. Page d'Inscription (Register)

**Fichier**: `templates/security/register.html.twig`

**Route**: `/register` (app_register)

**Description**: Formulaire d'inscription pour les candidats avec validation complète.

```twig
{% extends 'base.html.twig' %}

{% block title %}Inscription - SmartNexus AI{% endblock %}

{% block body %}
<div class="flex min-h-screen w-full flex-row">
    <!-- Left Panel - Branding -->
    <div class="hidden lg:flex w-[40%] flex-col justify-between bg-navy relative overflow-hidden p-12 text-white">
        <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none">
            <div class="absolute right-[-10%] top-[-10%] w-[500px] h-[500px] rounded-full bg-primary blur-[120px]"></div>
            <div class="absolute left-[-10%] bottom-[-10%] w-[400px] h-[400px] rounded-full bg-blue-400 blur-[100px]"></div>
            <div class="absolute inset-0" style="background-image: radial-gradient(rgba(255,255,255,0.2) 1px, transparent 1px); background-size: 40px 40px;"></div>
        </div>
        
        <div class="relative z-10 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-navy">
                <span class="material-symbols-outlined text-2xl">dataset</span>
            </div>
            <span class="text-xl font-bold tracking-wide">SmartNexus AI</span>
        </div>
        
        <div class="relative z-10 max-w-md">
            <h1 class="text-4xl font-bold leading-tight tracking-tight mb-6 text-white">
                Rejoignez la révolution de l'Intelligence Active
            </h1>
            <p class="text-blue-100 text-lg leading-relaxed font-light">
                Nous donnons aux entreprises les moyens d'exploiter les modèles prédictifs de nouvelle génération. Libérez le potentiel de vos données avec nos solutions IA de niveau entreprise.
            </p>
            <div class="mt-12 flex items-center gap-4">
                <div class="flex -space-x-3">
                    <div class="h-10 w-10 rounded-full border-2 border-navy bg-gradient-to-br from-primary to-orange-400 flex items-center justify-center text-navy font-bold text-sm">AB</div>
                    <div class="h-10 w-10 rounded-full border-2 border-navy bg-gradient-to-br from-electric to-blue-400 flex items-center justify-center text-white font-bold text-sm">CD</div>
                    <div class="h-10 w-10 rounded-full border-2 border-navy bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center text-white font-bold text-sm">EF</div>
                </div>
                <p class="text-sm font-medium text-blue-100">Rejoint par 10,000+ innovateurs</p>
            </div>
        </div>
        
        <div class="relative z-10 text-xs text-blue-200/60">
            © {{ "now"|date("Y") }} SmartNexus AI. Tous droits réservés.
        </div>
    </div>

    <!-- Right Panel - Registration Form -->
    <div class="flex flex-1 flex-col items-center justify-center overflow-y-auto px-4 py-8 lg:px-16">
        <div class="w-full max-w-[520px] flex flex-col gap-6">
            <!-- Mobile Logo -->
            <div class="lg:hidden flex items-center gap-2 mb-4 text-navy">
                <span class="material-symbols-outlined text-3xl text-primary">dataset</span>
                <span class="text-lg font-bold">SmartNexus AI</span>
            </div>

            <div class="bg-white dark:bg-[#1e1e1e] rounded-2xl shadow-sm border border-[#e7e3da] dark:border-gray-800 p-8 md:p-10">
                <div class="mb-8">
                    <h2 class="text-[#181610] dark:text-white tracking-tight text-3xl font-bold leading-tight mb-2">Créer votre compte</h2>
                    <p class="text-[#8d815e] dark:text-gray-400 text-sm font-normal">
                        Vous avez déjà un compte? <a class="text-navy dark:text-primary underline hover:opacity-80 transition-opacity" href="{{ path('app_login') }}">Se connecter</a>
                    </p>
                </div>

                <!-- Progress Steps -->
                <div class="flex flex-col gap-3 mb-8">
                    <div class="flex gap-6 justify-between items-end">
                        <p class="text-[#181610] dark:text-gray-200 text-sm font-semibold uppercase tracking-wider">Étape 1 sur 2</p>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Informations du compte</span>
                    </div>
                    <div class="h-1.5 w-full rounded-full bg-[#e7e3da] dark:bg-gray-700 overflow-hidden">
                        <div class="h-full bg-primary rounded-full" style="width: 50%;"></div>
                    </div>
                </div>

                {% if app.flashes('verify_email_error') %}
                    {% for flash in app.flashes('verify_email_error') %}
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-3">
                            <span class="material-symbols-outlined text-red-500">error</span>
                            <span class="text-sm font-medium">{{ flash }}</span>
                        </div>
                    {% endfor %}
                {% endif %}

                {# Afficher les erreurs flash #}
                {% for flash in app.flashes('error') %}
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-2 flex items-center gap-3">
                        <span class="material-symbols-outlined text-red-500">error</span>
                        <span class="text-sm font-medium">{{ flash }}</span>
                    </div>
                {% endfor %}

                {{ form_start(registrationForm, {'attr': {'class': 'flex flex-col gap-5', 'novalidate': 'novalidate'}}) }}
                    
                    {# Afficher toutes les erreurs du formulaire #}
                    {% if registrationForm.vars.errors|length > 0 %}
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex flex-col gap-2">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-red-500">error</span>
                                <span class="text-sm font-bold">Erreurs de validation :</span>
                            </div>
                            <ul class="list-disc list-inside text-sm">
                                {% for error in registrationForm.vars.errors %}
                                    <li>{{ error.message }}</li>
                                {% endfor %}
                            </ul>
                        </div>
                    {% endif %}

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="flex flex-col gap-1.5">
                            {{ form_label(registrationForm.prenom, 'Prénom', {'label_attr': {'class': 'text-sm font-medium text-[#181610] dark:text-gray-300'}}) }}
                            {{ form_widget(registrationForm.prenom, {'attr': {'class': 'w-full rounded-lg border border-[#e7e3da] dark:border-gray-700 bg-background-light dark:bg-gray-800 px-4 py-3 text-base text-[#181610] dark:text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-gray-400', 'placeholder': 'Jean'}}) }}
                            {{ form_errors(registrationForm.prenom) }}
                        </div>
                        <div class="flex flex-col gap-1.5">
                            {{ form_label(registrationForm.nom, 'Nom', {'label_attr': {'class': 'text-sm font-medium text-[#181610] dark:text-gray-300'}}) }}
                            {{ form_widget(registrationForm.nom, {'attr': {'class': 'w-full rounded-lg border border-[#e7e3da] dark:border-gray-700 bg-background-light dark:bg-gray-800 px-4 py-3 text-base text-[#181610] dark:text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-gray-400', 'placeholder': 'Dupont'}}) }}
                            {{ form_errors(registrationForm.nom) }}
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        {{ form_label(registrationForm.email, 'Email professionnel', {'label_attr': {'class': 'text-sm font-medium text-[#181610] dark:text-gray-300'}}) }}
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-3 top-3.5 text-gray-400 group-focus-within:text-primary transition-colors text-[20px]">mail</span>
                            {{ form_widget(registrationForm.email, {'attr': {'class': 'w-full rounded-lg border border-[#e7e3da] dark:border-gray-700 bg-background-light dark:bg-gray-800 pl-10 pr-4 py-3 text-base text-[#181610] dark:text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-gray-400', 'placeholder': 'nom@entreprise.com'}}) }}
                        </div>
                        {{ form_errors(registrationForm.email) }}
                    </div>

                    <div class="flex flex-col gap-1.5">
                        {{ form_label(registrationForm.plainPassword.first, 'Mot de passe', {'label_attr': {'class': 'text-sm font-medium text-[#181610] dark:text-gray-300'}}) }}
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-3 top-3.5 text-gray-400 group-focus-within:text-primary transition-colors text-[20px]">lock</span>
                            {{ form_widget(registrationForm.plainPassword.first, {'attr': {'class': 'w-full rounded-lg border border-[#e7e3da] dark:border-gray-700 bg-background-light dark:bg-gray-800 pl-10 pr-10 py-3 text-base text-[#181610] dark:text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-gray-400', 'placeholder': 'Créer un mot de passe sécurisé'}}) }}
                            <button type="button" class="absolute right-3 top-3.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 toggle-password">
                                <span class="material-symbols-outlined text-[20px]">visibility</span>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.</p>
                        {{ form_errors(registrationForm.plainPassword.first) }}
                    </div>

                    <div class="flex flex-col gap-1.5">
                        {{ form_label(registrationForm.plainPassword.second, 'Confirmer le mot de passe', {'label_attr': {'class': 'text-sm font-medium text-[#181610] dark:text-gray-300'}}) }}
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-3 top-3.5 text-gray-400 group-focus-within:text-primary transition-colors text-[20px]">lock</span>
                            {{ form_widget(registrationForm.plainPassword.second, {'attr': {'class': 'w-full rounded-lg border border-[#e7e3da] dark:border-gray-700 bg-background-light dark:bg-gray-800 pl-10 pr-10 py-3 text-base text-[#181610] dark:text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-gray-400', 'placeholder': 'Confirmer votre mot de passe'}}) }}
                            <button type="button" class="absolute right-3 top-3.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 toggle-password">
                                <span class="material-symbols-outlined text-[20px]">visibility</span>
                            </button>
                        </div>
                        {{ form_errors(registrationForm.plainPassword.second) }}
                    </div>

                    <div class="flex flex-col gap-1.5">
                        {{ form_label(registrationForm.phoneNumber, 'Téléphone (optionnel)', {'label_attr': {'class': 'text-sm font-medium text-[#181610] dark:text-gray-300'}}) }}
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-3 top-3.5 text-gray-400 group-focus-within:text-primary transition-colors text-[20px]">phone</span>
                            {{ form_widget(registrationForm.phoneNumber, {'attr': {'class': 'w-full rounded-lg border border-[#e7e3da] dark:border-gray-700 bg-background-light dark:bg-gray-800 pl-10 pr-4 py-3 text-base text-[#181610] dark:text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-gray-400', 'placeholder': '+33 6 12 34 56 78'}}) }}
                        </div>
                        {{ form_errors(registrationForm.phoneNumber) }}
                    </div>

                    <div class="flex items-start gap-2 mt-2">
                        {{ form_widget(registrationForm.agreeTerms, {'attr': {'class': 'size-4 rounded border-gray-300 text-primary focus:ring-primary mt-0.5'}}) }}
                        <label class="text-sm text-gray-600" for="{{ registrationForm.agreeTerms.vars.id }}">
                            J'accepte les <a href="#" class="text-navy underline hover:opacity-80">Conditions d'utilisation</a> et la <a href="#" class="text-navy underline hover:opacity-80">Politique de confidentialité</a>
                        </label>
                    </div>
                    {{ form_errors(registrationForm.agreeTerms) }}

                    <button type="submit" class="mt-4 w-full rounded-lg bg-primary py-3.5 text-base font-bold text-[#181610] shadow-sm hover:bg-yellow-400 active:scale-[0.99] transition-all flex items-center justify-center gap-2">
                        Créer mon compte
                    </button>

                    <div class="relative my-4">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-[#e7e3da] dark:border-gray-700"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="bg-white dark:bg-[#1e1e1e] px-4 text-gray-500 font-bold tracking-wider uppercase">Ou continuer avec</span>
                        </div>
                    </div>

                    <button type="button" class="w-full flex items-center justify-center gap-3 rounded-lg border border-[#e7e3da] dark:border-gray-700 bg-white dark:bg-gray-800 py-3 px-4 text-base font-bold text-gray-700 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"></path>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"></path>
                        </svg>
                        S'inscrire avec Google
                    </button>
                {{ form_end(registrationForm) }}
            </div>

            <div class="flex justify-center gap-6 text-sm text-gray-500 dark:text-gray-400">
                <a class="hover:text-navy dark:hover:text-primary transition-colors" href="#">Politique de confidentialité</a>
                <a class="hover:text-navy dark:hover:text-primary transition-colors" href="#">Conditions d'utilisation</a>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', function() {
        const input = this.parentElement.querySelector('input');
        const icon = this.querySelector('.material-symbols-outlined');
        if (input.type === 'password') {
            input.type = 'text';
            icon.textContent = 'visibility_off';
        } else {
            input.type = 'password';
            icon.textContent = 'visibility';
        }
    });
});
</script>
{% endblock %}
```

---

## 4. Dashboard Admin

**Fichier**: `templates/back_office/dashboard.html.twig`

**Route**: `/admin` (admin_dashboard)

**Description**: Tableau de bord administrateur avec statistiques, graphiques et actions rapides. Nécessite `ROLE_ADMIN`.

```twig
{% extends 'back_office/base.html.twig' %}

{% block title %}Dashboard - Admin SmartNexus AI{% endblock %}

{% block breadcrumb %}
    <span class="text-[#9ca3af] material-symbols-outlined text-[16px]">chevron_right</span>
    <span class="text-primary font-semibold">Dashboard</span>
{% endblock %}

{% block content %}
<!-- Page Heading -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-2">
    <div>
        <h1 class="text-3xl md:text-4xl font-black tracking-tight text-[#101218] dark:text-white mb-2">
            Bienvenue, {{ app.user.prenom }}
        </h1>
        <p class="text-[#5e658d] dark:text-[#9ca3af] text-base">
            Vue d'ensemble de l'activité sur SmartNexus AI
        </p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ path('admin_user_new') }}" class="flex items-center justify-center gap-2 rounded-lg h-11 px-6 bg-primary hover:bg-primary/90 text-navy text-sm font-bold shadow-lg shadow-primary/30 transition-all">
            <span class="material-symbols-outlined text-[20px]">add</span>
            <span>Nouvel Utilisateur</span>
        </a>
    </div>
</div>

<!-- Stats Overview -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <!-- Total Users -->
    <div class="bg-white dark:bg-[#1a1d2d] p-6 rounded-xl border border-[#e5e7eb] dark:border-[#2d3142] shadow-sm flex flex-col gap-3 group hover:shadow-md hover:border-primary/30 transition-all">
        <div class="flex items-center justify-between">
            <p class="text-[#5e658d] dark:text-[#9ca3af] font-medium text-sm">Utilisateurs Total</p>
            <span class="bg-gradient-to-br from-blue-500 to-blue-600 p-2 rounded-lg shadow-md shadow-blue-500/20">
                <span class="material-symbols-outlined text-white text-[24px]">group</span>
            </span>
        </div>
        <div class="flex items-end gap-3">
            <p class="text-4xl font-black dark:text-white tracking-tight">{{ stats.total_users }}</p>
            <p class="text-sm text-green-600 font-semibold flex items-center gap-1 mb-1">
                <span class="material-symbols-outlined text-[16px]">trending_up</span>
                {{ stats.active_users }} actifs
            </p>
        </div>
    </div>

    <!-- Candidats -->
    <div class="bg-white dark:bg-[#1a1d2d] p-6 rounded-xl border border-[#e5e7eb] dark:border-[#2d3142] shadow-sm flex flex-col gap-3 group hover:shadow-md hover:border-primary/30 transition-all">
        <div class="flex items-center justify-between">
            <p class="text-[#5e658d] dark:text-[#9ca3af] font-medium text-sm">Candidats</p>
            <span class="bg-gradient-to-br from-purple-500 to-purple-600 p-2 rounded-lg shadow-md shadow-purple-500/20">
                <span class="material-symbols-outlined text-white text-[24px]">person_search</span>
            </span>
        </div>
        <div class="flex items-end gap-3">
            <p class="text-4xl font-black dark:text-white tracking-tight">{{ stats.candidats }}</p>
        </div>
    </div>

    <!-- Employés -->
    <div class="bg-white dark:bg-[#1a1d2d] p-6 rounded-xl border border-[#e5e7eb] dark:border-[#2d3142] shadow-sm flex flex-col gap-3 group hover:shadow-md hover:border-primary/30 transition-all">
        <div class="flex items-center justify-between">
            <p class="text-[#5e658d] dark:text-[#9ca3af] font-medium text-sm">Employés</p>
            <span class="bg-gradient-to-br from-green-500 to-green-600 p-2 rounded-lg shadow-md shadow-green-500/20">
                <span class="material-symbols-outlined text-white text-[24px]">badge</span>
            </span>
        </div>
        <div class="flex items-end gap-3">
            <p class="text-4xl font-black dark:text-white tracking-tight">{{ stats.employees }}</p>
        </div>
    </div>

    <!-- Admins -->
    <div class="bg-white dark:bg-[#1a1d2d] p-6 rounded-xl border border-[#e5e7eb] dark:border-[#2d3142] shadow-sm flex flex-col gap-3 group hover:shadow-md hover:border-primary/30 transition-all">
        <div class="flex items-center justify-between">
            <p class="text-[#5e658d] dark:text-[#9ca3af] font-medium text-sm">Administrateurs</p>
            <span class="bg-gradient-to-br from-orange-500 to-orange-600 p-2 rounded-lg shadow-md shadow-orange-500/20">
                <span class="material-symbols-outlined text-white text-[24px]">admin_panel_settings</span>
            </span>
        </div>
        <div class="flex items-end gap-3">
            <p class="text-4xl font-black dark:text-white tracking-tight">{{ stats.admins }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Candidatures par statut -->
    <div class="bg-white dark:bg-[#1a1d2d] rounded-xl border border-[#e5e7eb] dark:border-[#2d3142] shadow-sm overflow-hidden">
        <div class="p-6 border-b border-[#f0f0f5] dark:border-[#2d3142] flex items-center justify-between">
            <h2 class="text-lg font-bold dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">analytics</span>
                Candidatures par statut
            </h2>
        </div>
        <div class="p-6 space-y-4">
            {% set statusColors = {
                'en_attente': {'bg': 'bg-gray-100', 'text': 'text-gray-700', 'bar': 'bg-gray-500'},
                'en_cours': {'bg': 'bg-blue-100', 'text': 'text-blue-700', 'bar': 'bg-blue-500'},
                'entretien': {'bg': 'bg-purple-100', 'text': 'text-purple-700', 'bar': 'bg-purple-500'},
                'accepte': {'bg': 'bg-green-100', 'text': 'text-green-700', 'bar': 'bg-green-500'},
                'refuse': {'bg': 'bg-red-100', 'text': 'text-red-700', 'bar': 'bg-red-500'},
                'liste_attente': {'bg': 'bg-orange-100', 'text': 'text-orange-700', 'bar': 'bg-orange-500'}
            } %}
            {% set statusLabels = {
                'en_attente': 'En attente',
                'en_cours': 'En cours',
                'entretien': 'Entretien',
                'accepte': 'Accepté',
                'refuse': 'Refusé',
                'nouveau': 'Nouveau',
                'liste_attente': 'Liste d\'attente'
            } %}
            {% set totalCandidats = stats.candidats|default(1) %}
            
            {% for statut, count in candidat_stats %}
            {% set colors = statusColors[statut]|default({'bg': 'bg-gray-100', 'text': 'text-gray-700', 'bar': 'bg-gray-500'}) %}
            <div class="flex items-center gap-4">
                <div class="w-28 flex items-center gap-2">
                    <span class="size-2 rounded-full {{ colors.bar }}"></span>
                    <span class="text-sm font-medium text-[#5e658d]">{{ statusLabels[statut]|default(statut|capitalize) }}</span>
                </div>
                <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="{{ colors.bar }} h-full rounded-full transition-all" style="width: {{ (count / totalCandidats * 100)|round }}%;"></div>
                </div>
                <span class="text-sm font-bold dark:text-white w-8 text-right">{{ count }}</span>
            </div>
            {% else %}
            <p class="text-sm text-[#5e658d] text-center py-4">Aucune candidature</p>
            {% endfor %}
        </div>
    </div>

    <!-- Derniers utilisateurs -->
    <div class="lg:col-span-2 bg-white dark:bg-[#1a1d2d] rounded-xl border border-[#e5e7eb] dark:border-[#2d3142] shadow-sm overflow-hidden">
        <div class="p-6 border-b border-[#f0f0f5] dark:border-[#2d3142] flex items-center justify-between">
            <h2 class="text-lg font-bold dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">person_add</span>
                Dernières inscriptions
            </h2>
            <a href="{{ path('admin_user_index') }}" class="text-sm font-semibold text-electric hover:underline flex items-center gap-1">
                Voir tout
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>
        <div class="divide-y divide-[#f3f4f6] dark:divide-[#2d3142]">
            {% for user in recent_users %}
            <div class="p-4 flex items-center justify-between hover:bg-[#f9fafb] dark:hover:bg-[#252836] transition-colors">
                <div class="flex items-center gap-3">
                    {% if user.photo %}
                        <div class="size-10 rounded-full bg-cover bg-center" style="background-image: url('{{ user.photo }}');"></div>
                    {% else %}
                        <div class="size-10 rounded-full bg-gradient-to-br from-primary to-electric flex items-center justify-center">
                            <span class="text-navy font-bold text-sm">{{ user.prenom|slice(0, 1)|upper }}{{ user.nom|slice(0, 1)|upper }}</span>
                        </div>
                    {% endif %}
                    <div>
                        <p class="font-bold text-[#101218] dark:text-white">{{ user.fullName }}</p>
                        <p class="text-xs text-[#5e658d]">{{ user.email }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    {% set typeColors = {
                        'candidat': 'bg-purple-100 text-purple-700 border-purple-200',
                        'employee': 'bg-blue-100 text-blue-700 border-blue-200',
                        'project_manager': 'bg-green-100 text-green-700 border-green-200',
                        'admin': 'bg-orange-100 text-orange-700 border-orange-200'
                    } %}
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold {{ typeColors[user.userType] }} border">
                        {% if user.userType == 'candidat' %}Candidat
                        {% elseif user.userType == 'employee' %}Employé
                        {% elseif user.userType == 'project_manager' %}Chef de Projet
                        {% elseif user.userType == 'admin' %}Admin{% endif %}
                    </span>
                    <span class="text-xs text-[#9ca3af]">{{ user.createdAt|date('d/m/Y') }}</span>
                    <a href="{{ path('admin_user_show', {'id': user.id}) }}" class="size-8 inline-flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-[#373b50] text-[#5e658d] transition-colors">
                        <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                    </a>
                </div>
            </div>
            {% else %}
            <div class="p-8 text-center text-[#5e658d]">
                <span class="material-symbols-outlined text-4xl mb-2 block">person_off</span>
                Aucun utilisateur inscrit
            </div>
            {% endfor %}
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="bg-white dark:bg-[#1a1d2d] rounded-xl border border-[#e5e7eb] dark:border-[#2d3142] shadow-sm p-6">
    <h2 class="text-lg font-bold dark:text-white mb-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">bolt</span>
        Actions rapides
    </h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ path('admin_user_new', {'type': 'candidat'}) }}" class="flex flex-col items-center gap-3 p-4 rounded-xl border border-[#e5e7eb] dark:border-[#2d3142] hover:border-purple-300 hover:bg-purple-50 dark:hover:bg-purple-900/10 transition-all group">
            <span class="p-3 rounded-xl bg-purple-100 text-purple-600 group-hover:bg-purple-200 transition-colors">
                <span class="material-symbols-outlined text-[28px]">person_add</span>
            </span>
            <span class="text-sm font-bold text-center">Nouveau Candidat</span>
        </a>
        <a href="{{ path('admin_user_new', {'type': 'employee'}) }}" class="flex flex-col items-center gap-3 p-4 rounded-xl border border-[#e5e7eb] dark:border-[#2d3142] hover:border-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/10 transition-all group">
            <span class="p-3 rounded-xl bg-blue-100 text-blue-600 group-hover:bg-blue-200 transition-colors">
                <span class="material-symbols-outlined text-[28px]">badge</span>
            </span>
            <span class="text-sm font-bold text-center">Nouvel Employé</span>
        </a>
        <a href="{{ path('admin_user_new', {'type': 'project_manager'}) }}" class="flex flex-col items-center gap-3 p-4 rounded-xl border border-[#e5e7eb] dark:border-[#2d3142] hover:border-green-300 hover:bg-green-50 dark:hover:bg-green-900/10 transition-all group">
            <span class="p-3 rounded-xl bg-green-100 text-green-600 group-hover:bg-green-200 transition-colors">
                <span class="material-symbols-outlined text-[28px]">folder_managed</span>
            </span>
            <span class="text-sm font-bold text-center">Nouveau Chef de Projet</span>
        </a>
        <a href="{{ path('admin_user_new', {'type': 'admin'}) }}" class="flex flex-col items-center gap-3 p-4 rounded-xl border border-[#e5e7eb] dark:border-[#2d3142] hover:border-orange-300 hover:bg-orange-50 dark:hover:bg-orange-900/10 transition-all group">
            <span class="p-3 rounded-xl bg-orange-100 text-orange-600 group-hover:bg-orange-200 transition-colors">
                <span class="material-symbols-outlined text-[28px]">admin_panel_settings</span>
            </span>
            <span class="text-sm font-bold text-center">Nouvel Admin</span>
        </a>
    </div>
</div>
{% endblock %}
```

---

## 🎨 Détails Techniques

### Framework CSS
- **Tailwind CSS** (CDN)
- Custom colors: `primary=#FFC105`, `navy=#1A237E`, `electric=#536DFE`

### Icônes
- **Material Symbols Outlined** (Google Fonts)
- CDN: https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined

### Dépendances Twig
Toutes les pages étendent `base.html.twig` qui contient :
- Meta tags viewport/charset
- Liens vers Tailwind CSS et Material Icons
- Scripts JavaScript (si nécessaire)

### Formulaires Symfony
Les pages d'inscription et de connexion utilisent :
- **CSRF Protection** (`csrf_token()`)
- **Form Components** Symfony
- **Validation** côté serveur
- **Flash Messages** pour les erreurs/succès

### Variables Contrôleur

#### Dashboard Admin
Variables passées au template :
```php
[
    'stats' => [
        'total_users' => int,      // Total utilisateurs
        'active_users' => int,     // Utilisateurs actifs
        'candidats' => int,        // Nombre de candidats
        'employees' => int,        // Nombre d'employés + PM
        'admins' => int            // Nombre d'admins
    ],
    'candidat_stats' => [
        'statut_name' => count     // Ex: 'en_attente' => 3
    ],
    'recent_users' => [            // 5 derniers utilisateurs
        {
            'id' => int,
            'prenom' => string,
            'nom' => string,
            'fullName' => string,
            'email' => string,
            'photo' => string|null,
            'userType' => string,
            'createdAt' => DateTime
        }
    ]
]
```

---

## 📝 Notes d'Implémentation

### Authentification
- Login utilise `form_login` de Symfony Security
- Remember Me configuré pour 7 jours (604800 secondes)
- Redirection selon rôle via `AuthenticationSuccessHandler`

### Validation
- Email unique via `UniqueEntity` constraint
- Mot de passe: min 8 caractères, 1 maj, 1 min, 1 chiffre
- Téléphone: format flexible accepté

### Responsive Design
- Mobile-first approach
- Breakpoints: `sm:`, `md:`, `lg:`, `xl:`
- Panneau gauche masqué sur mobile (<lg)

### Dark Mode
- Classes Tailwind: `dark:bg-[color]`, `dark:text-[color]`
- Support complet sur toutes les pages

---

**Dernière mise à jour** : 4 février 2026  
**Version** : 1.0.0
