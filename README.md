# Mobile Money — Projet Final S4

Application de gestion d'argent mobile (Mobile Money) développée avec **CodeIgniter 4**, **SQLite** embarqué et **Bootstrap 5**.

```bash
# 1. Aller dans le répertoire du projet
cd projet_final_S4

# 2. Installer CodeIgniter 4 et les dépendances
composer install

# 3. Copier et configurer l'environnement
cp .env .env.bak    # (le .env est déjà configuré)

# 4. Créer la base de données et les tables
php spark migrate

# 5. Insérer les données initiales (compte opérateur + barèmes)
php spark db:seed DatabaseSeeder

# 6. Lancer le serveur de développement
php spark serve
```

### Accès à l'application

- **Application** : http://localhost:8080
- **Espace Client** : http://localhost:8080/client
- **Espace Opérateur** : http://localhost:8080/operator

### Compte opérateur par défaut

| Champ | Valeur |
|---|---|
| Téléphone | `0340000000` |
| Mot de passe | `admin` |

---



### PHP Built-in server (développement)

```bash
php spark serve

