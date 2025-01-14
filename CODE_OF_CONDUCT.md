# Code de Conduite - Gestion des Branches et Déploiement

## Gestion des Branches Features

### Création et Développement
- Toute nouvelle fonctionnalité doit être développée dans une branche dédiée
- La branche feature doit toujours être créée à partir de la branche main
- Format de nommage : `features/XXXX` où XXXX décrit la fonctionnalité
- Les modifications doivent être ciblées et concises
- Éviter de mélanger les reformattages de code avec les modifications fonctionnelles

### Tests et Validation
- Chaque fonctionnalité doit être accompagnée d'au moins 3 tests :
    - Tests unitaires
    - Tests d'intégration
- Tous les tests doivent passer avant l'intégration de la branche
- La validation des tests est obligatoire pour la fusion

## Processus d'Intégration

### Merge dans Main
- La fusion dans main se fait par squash
- Peut être réalisée via Pull Request ou directement selon le contexte
- Le merge déclenche automatiquement :
    - Le déploiement en environnement de staging
    - L'éligibilité pour une future release en production

## Gestion des Dépendances

### Mises à Jour
- Les mises à jour générales des dépendances (Composer, Node) sont gérées par le mainteneur du dépôt
- Les développeurs ne doivent déclarer que les besoins spécifiques à leur fonctionnalité
- Toute mise à jour de dépendance doit être justifiée et documentée

## Déploiement en Production

### Processus de Release
- Le déploiement en production nécessite une Pull Request sur main
- Utilisation du plugin release-please pour la gestion des releases
- Le processus :
    1. Création automatique de la PR de release
    2. Validation de la PR
    3. Déploiement automatique en production

## Bonnes Pratiques

### Commits et Messages
- Les messages de commit doivent suivre la convention "Conventional Commits"
- Format requis : `type(scope): description`
- Types principaux :
    - `feat(...)`: Nouvelles fonctionnalités
    - `fix(...)`: Corrections de bugs
    - `chore(...)`: Tâches de maintenance
    - `docs(...)`: Modifications de la documentation
    - `style(...)`: Changements de formatage
    - `test(...)`: Ajout ou modification de tests
    - `refactor(...)`: Refactoring du code
- Cette convention est utilisée par release-please pour générer automatiquement le fichier CHANGELOG
- Chaque commit doit représenter une modification logique et cohérente
- Éviter les commits qui mélangent plusieurs fonctionnalités

### Cohérence du Code
- Lorsque le framework offre plusieurs options d'implémentation, privilégier l'approche déjà utilisée dans le code existant
- Maintenir la cohérence avec les patterns et conventions déjà établis dans le projet
- En cas de doute, examiner le code similaire déjà en place et suivre les mêmes patterns

### Documentation
- Les modifications importantes doivent être documentées
- Les changements d'API ou de configuration doivent être clairement expliqués
- Mettre à jour la documentation technique si nécessaire

### Revue de Code
- Privilégier les revues de code avant l'intégration
- Vérifier la qualité et la clarté du code
- S'assurer du respect des standards du projet
