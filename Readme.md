# 📚 Projet Annuel

## 🎯 Introduction

Le projet annuel est un projet pluridisciplinaire mettant en pratique les compétences acquises au cours de la **3ème et de la 4ème année**.

Cette année, **le sujet du projet est libre**, néanmoins, certaines **technologies sont imposées**.

---

## ✅ Fonctionnalités (15 points)

### 🔁 Gestion de projet & Historisation de code ✅
- Le projet doit suivre une méthode **Agile**.
- Utilisation d'une plateforme de gestion de projet Agile (exemples non exhaustifs) :
 - GitHub Project ✅
 - Trello
 - Jira
- Tous les membres doivent travailler équitablement.
- Le code doit être historisé avec **Git**. ✅

### 🎨 Conception et Interface ✅
- L'application doit être **responsive** : ordinateur, mobile, tablette.
- Design **cohérent** et **agréable visuellement**.✅
- **Maquettes** des pages principales à présenter lors de la soutenance.

### 🖥️ Framework côté client ✅
- Le client Web doit être conçu avec **Vue.js**. ✅
- Possibilité d’utiliser les librairies de l’écosystème Vue.js et JavaScript.

### 🖧 Langage côté serveur ✅
- Langages autorisés : **PHP** ou **JavaScript (Node.js)**.✅
- Possibilité d’utiliser les deux (ex. : microservices).

### 🗄️ Modélisation, Requêtage SQL & Base de données ✅
- Utilisation d'une base de données **relationnelle SQL** :
 - MySQL ✅
 - MariaDB
 - PostgreSQL
- Le modèle de données doit être conçu en amont et justifié à la soutenance.

---

## 🐳 Docker ✅

- La base de données et le serveur doivent être déployés sous forme de **conteneurs Docker**.
- Utilisation obligatoire d’un **proxy inverse** exposé au public, comme :
 - Nginx ✅
 - Apache
 - Caddy
 - Traefik
- Les autres services doivent rester internes au réseau Docker.

---

## 🌐 Serveur & Nom de domaine ✅

- L'application doit être **disponible en ligne** via un nom de domaine public avec certificat **SSL** valide.
- Les conteneurs Docker doivent être déployés avec un **Docker Context**. ✅
- Création d’un utilisateur **non-privilégié** pour le déploiement (excluant l’administrateur). ✅

---

## 🔎 Optimisation pour la recherche naturelle (SEO)

Le site doit être optimisé pour le **référencement naturel**, incluant :

- Titres uniques et cohérents par page
- Descriptions optimisées
- Stratégie de **mots-clés** alignée avec le domaine de l'application

---

## 🌟 Bonus (5 points)

### 🔐 Authentification à deux facteurs (2FA)
- Implémentation **sans librairie** d’un algorithme **TOTP**.
- Compatible avec :
 - Google Authenticator
 - Microsoft Authenticator

### 📢 Campagne AdWords
- Lancement d’une **campagne publicitaire ciblée**, avec :
 - Stratégie claire
 - Périmètre géographique précis

### 📊 Analytics
- Instrumentation avec des outils comme **Matomo**.
- Informations à remonter :
 - Nombre d’utilisateurs
 - Durée moyenne des sessions
 - Taux de rebond

### ❗ Gestion des erreurs
- Utilisation de **GlitchTip** ou **Sentry (GitHub Student)**.
- Intégration avec SDK Vue.js et côté serveur.

### 🔒 Sécurité
- Analyse avec des outils comme :
 - Snyk.io
 - Docker Scout
 - SQLMap
- Production d’un **rapport de vulnérabilités** avec les **remédiations apportées**.

---


# 🌐 Projet Annuel 2025 – [Site E-commerce]

## 📌 Présentation du projet

**Nom du projet :** [Site E-commerce]  
**Objectif :** [Site e-commerce pour la vente de produits digitaux et physiques]
**Technologies :** Frontend : Vue.js, Backend : Symfony

## 🏗️ Technologies utilisées

| Type                       | Outils / Langages                           |
|----------------------------|---------------------------------------------|
| **Front**                  | Vue.js                                      |
| **Back**                   | Symfony                                     |
| **Base de données**        | MySQL                                       |
| **Conteneurisation**       | Docker                                      |
| **Reverse proxy**          | Nginx                                       |
| **Déploiement**            | Docker sur serveur distant                  |
| **Gestion de projet**      | GitHub Projects                             |
| **SEO & Analyse**          | Titres dynamiques, descriptions SEO, Matomo |

---