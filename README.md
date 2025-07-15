# 📚 Projet Annuel

Projet Ecommerce pour la vente de produits digitaux et physiques, développé avec Vue.js pour le frontend et Symfony pour le backend. Le projet inclut des fonctionnalités d'authentification, de gestion des comptes, de produits, de panier, de commandes, ainsi que des outils d'administration pour les administrateurs.

## 🔐 Authentification & Gestion des Comptes
- Inscription (avec confirmation de mail)
- Confirmation de compte (via lien dans le mail avec token associé)
- Mot de passe oublié (envoi d’un lien de réinitialisation avec token)
- Connexion (Authentification via JWT)
    - Vérification de la confirmation de l'email
    - Vérification du soft delete
    - Vérification du 2FA Authentication
- Déconnexion
- Gestion des rôles :
    - Utilisateur
    - Admin

## 👤 Gestion du Compte
- Informations personnelles (CRUD)
- Suppression du compte
    - Soft delete
    - Hard delete
-Gestion du double authentification (2FA)
    - Activation / Désactivation
    - Test de la 2FA
- Adresses de livraison (CRUD)
- Adresses de facturation (CRUD)

## 📦 Commandes & Historique
- Historique des commandes :
    - Prix total
    - Date de commande
    - Nombre de produits
- Détails d’une commande :
    - Produit, quantité, prix
    - Adresses (livraison & facturation)
    - Statut de la commande
    - Date

## 🛒 Panier
### Navbar :
- Produits
- Quantité
- Prix total
- Suppression / modification de quantité

### Page Panier :
- Liste des produits
- Quantité
- Prix total
- Supprimer un produit
- Supprimer tous les produits
- Modifier la quantité
- Passer la commande

## 🛍️ Produits
### Page Produits :
- Filtrage par :
    - Catégorie
    - Barre de recherche
    - Type de produit
    - Prix (min/max)
    - Prix croissant / décroissant
    - Date de création

### Détails d’un produit :
- Tags
- Catégorie principale / associée
- Image
- Titre & Description dynamique (produit physique ou digital)
- Type : physique / digital
- Prix
- Quantité
- Date de création
- Moyenne des notes
- Ajout au panier
- Section d’avis
- File d’ariane dynamique

## ⭐ Avis Produit
- Ajouter un avis (si connecté et produit commandé)
- Modifier / Supprimer un avis
- Note par étoiles
- Date de création
- Statut de l’avis (approuvé, en attente, refusé)
- Commentaire
- Logo avec initiales du prénom

## 💳 Tunnel de Paiement
1. **Étape 1/2** :
    - Sélection adresse de livraison
    - Sélection adresse de facturation
2. **Étape 2/2** :
    - Formulaire de paiement
    - Paiement via Stripe

### Page de confirmation de commande :
- Numéro de commande
- Date
- Prix total
- Détails produits
- Adresse de livraison / facturation
- Téléchargement de la facture (PDF)

## 🏠 Page d’accueil
- Produits récemment consultés
- Les mieux notés
- Les moins chers
- Les plus vendus
- Derniers ajouts
- Tags

---

## 🛠️ Backoffice Admin

### Tableau de bord :
- Évolution des commandes

### Pages de gestion (CRUD) :
- Produits
- Commandes
- Factures
- Paniers
- Utilisateurs
- Adresses
- Catégories
- Avis
- Tags

## 🛠️ SEO
-Sitemap du site (en plus des titres, descriptions mots clée)

## ✅ Fonctionnalités demandées (15 points)

### 🔁 Gestion de projet & Historisation de code ✅
- Le projet doit suivre une méthode **Agile**. ✅
- Utilisation d'une plateforme de gestion de projet Agile : GitHub Project ✅
- Tous les membres doivent travailler équitablement. ✅
- Le code doit être historisé avec **Git**. ✅

### 🎨 Conception et Interface ✅
- L'application doit être **responsive** : ordinateur, mobile, tablette.✅
- Design **cohérent** et **agréable visuellement**.✅
- **Maquettes** des pages principales à présenter lors de la soutenance.

### 🖥️ Framework côté client ✅
- Le client Web doit être conçu avec **Vue.js**. ✅

### 🖧 Langage côté serveur ✅
- Langages autorisés : **PHP** ou **JavaScript (Node.js)**.✅
- Possibilité d’utiliser les deux (ex. : microservices). ✅

### 🗄️ Modélisation, Requêtage SQL & Base de données ✅
- Utilisation d'une base de données **relationnelle SQL** : MySQL ✅
- Le modèle de données doit être conçu en amont et justifié à la soutenance.

---

## 🐳 Docker ✅

- La base de données et le serveur doivent être déployés sous forme de **conteneurs Docker**. ✅
- Utilisation obligatoire d’un **proxy inverse** exposé au public, comme : Nginx ✅
- Les autres services doivent rester internes au réseau Docker. ✅

---

## 🌐 Serveur & Nom de domaine ✅

- L'application doit être **disponible en ligne** via un nom de domaine public avec certificat **SSL** valide. ✅
- Les conteneurs Docker doivent être déployés avec un **Docker Context**. ✅
- Création d’un utilisateur **non-privilégié** pour le déploiement (excluant l’administrateur). ✅

---

## 🔎 Optimisation pour la recherche naturelle (SEO)
## 🔎 Optimisation pour la recherche naturelle (SEO)

Le site doit être optimisé pour le **référencement naturel**, incluant :

- Titres uniques et cohérents par page ✅
- Descriptions optimisées ✅
- Stratégie de **mots-clés** alignée avec le domaine de l'application ✅
+ Sitemap (pas demandé mais bonus ✅)
---

## 🌟 Bonus (5 points)

### 🔐 Authentification à deux facteurs (2FA) ✅
- Implémentation **sans librairie** d’un algorithme **TOTP**. ✅
- Google Authenticator ✅
- Microsoft Authenticator ✅

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
## 🧱 Stack Technique

| Élément              | Technologie / Outil                       |
|----------------------|-------------------------------------------|
| **Front**            | Vue.js                                    |
| **Back**             | Symfony                                   |
| **Base de données**  | MySQL                                     |
| **Conteneurisation** | Docker                                    |
| **Reverse proxy**    | Nginx                                     |
| **Serveur**          | Azure                                     |
| **Nom de domaine**   | Infomaniak                                |
| **SSL**              | Certbot                                   |
| **Déploiement**      | Docker                                    |
| **Gestion de projet**| GitHub Projects                           |
| **SEO & Analyse**    | Titres, descriptions SEO, Sitemap, Matomo |

---
## 🚀 Lien en production déployée sous docker

[https://bytemeuh-bytemeuh.fr](https://bytemeuh-bytemeuh.fr)

---

## 🛠️ Installation & Configuration en Local

```bash
git clone https://github.com/BotanESGI/Projet-annuel.git
cd Projet-annuel
cd mini-ecommerce
npm install
npm run build
docker-compose build
docker-compose up -d
docker exec -it projet-annuel_web_1 php bin/console doctrine:migrations:migrate --no-interaction
docker exec -it projet-annuel_web_1 php bin/console doctrine:fixtures:load --no-interaction
```
