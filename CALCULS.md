# 💰 Explication des Calculs - Mobile Money

## Règle Importante

**La commission est prise par l'opérateur DESTINATAIRE, pas l'expéditeur !**

---

## Exemple 1 : Transfert Simple Externe

### Contexte
- Mon numéro : **034** (Telma)
- Destinataire : **032** (Orange)  
- Commission Orange : **1%**
- Montant : **2000 Ar**
- Mon solde : **5000 Ar**

### Calcul

```
1. Frais de transfert Telma (mon opérateur)
   Selon barème Telma pour 2000 Ar = 50 Ar

2. Commission Orange (opérateur destinataire)
   2000 × 1% = 20 Ar

3. Total débité de mon compte
   2000 (montant) + 50 (frais) + 20 (commission) = 2070 Ar

4. Ce que le destinataire reçoit
   2000 Ar
```

### Rés: Envoi Multiple

### Contexte
- Mon numéro : **034** (Telma)
- 3 destinataires : **034111, 034222, 034333** (tous Telma)
- Montant total : **30000 Ar**

### Calcul

```
1. Division du montant
   30000 ÷ 3 = 10000 Ar par destinataire

2. Frais pour chaque transfert de 10000 Ar
   100 Ar × 3 = 300 Ar

3. Total débité
   30000 + 300 = 30300 Ar

4. Chaque destinataire reçoit
   10000 Ar
```

### Résultat
- **Débité** : 30300 Ar
- **Chaque dest. reçoit** : 10000 Ar
- **Total reçu** : 30000 Ar

---

## Statistiques Opérateur

### Pour Telma (après exemples ci-dessus)

#### Gains
```
Retrait : 0 Ar
Transfert interne : 50 + 100 + 100 + 100 = 350 Ar
Transfert externe : 50 Ar
Total : 400 Ar
```

#### Montants à Envoyer
```
À Orange : 2000 Ar (exemple 1) + 2050 Ar (exemple 2) = 4050 Ar
```

### Pour Orange

#### Gains
```
Commission reçue : 20 + 20 = 40 Ar
```

#### Montants à Recevoir
```
De Telma : 4050 Ar
```

---

## Règles Importantes

1. **Commission** = opérateur destinataire uniquement
2. **Frais transfert** = mon opérateur garde
3. **Montants à envoyer** = montant transféré (+ frais retrait si option)
4. **Envoi multiple