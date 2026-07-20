# 🧪 Tests Rapides

## 1. Créer un Opérateur
```
1. Aller sur /operateur
2. Cliquer "Créer un nouvel opérateur"
3. Remplir :
   - Nom : MVola
   - Préfixes : 034
   - Commission : 2.5
   - Username : mvola
   - Password : mvola123
4. Se connecter avec mvola/mvola123
```

## 2. Envoi Multiple (Même Opérateur)
```
1. Login client : 0340001111
2. Aller sur Transfert
3. Numéros : 0340002222, 0340003333, 0340004444
4. Montant : 30000
5. Résultat : Chaque dest. reçoit 10000 Ar
```

## 3. Transfert Externe avec Commission
```
Client Telma : 0340001234 (solde 125000)
→ Vers Orange : 0320001111
Montant : 2000

Calcul :
- Frais Telma : 50 Ar
- Commission Orange (1%) : 20 Ar
- Total débité : 2070 Ar
- Dest. reçoit : 2000 Ar

Vérifier dans Stats Telma :
- Montants à envoyer à Orange : 2000 Ar
```

## 4. Avec Frais Retrait Inclus
```
Même transfert mais cocher "Inclure frais retrait"

Calcul :
- Frais Telma : 50 Ar
- Commission Orange : 20 Ar  
- Frais retrait Orange : 50 Ar
- Total débité : 2120 Ar
- Dest. reçoit : 2050 Ar

Vérifier Stats Telma :
- Montants à envoyer à Orange : 2050 Ar
```

## 5. Statistiques
```
Login Telma → Statistiques

Vérifier :
✓ Gains Retrait avec %
✓ Gains Transfert Interne avec %
✓ Gains Transfert Externe avec %
✓ Montants à envoyer avec %
✓ Total = 100%
```
