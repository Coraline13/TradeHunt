#Trade Hunt
###Bodea Coralia-Mihaela, Vîjdea Cristian-Ioan

* source: https://github.com/Coraline13/ProgWeb
* hosted: https://progweb.cvjd.me/
* iut network: http://tp.iut-infobio.priv.univ-lille1.fr/~vijdeac/pw/public/
* required PHP modules: 
    - pdo_sqlite (PDO SQLite connection), 
    - php_gd2 (image processing)
    - php_mbstring (unicode processing)
* the website must be served out of `$PROJECT_ROOT/public` (e.g. tilleul01.iut-infobio.priv.univ-lille1.fr/~vijdeac/pw/public/)
* the database is saved in `$PROJECT_ROOT/data`, so the web server user must have write access there; the database is automatically created from SQL files in the project root if it does not exist
* there are two default users, `cvijdea` and `cbodea`, both with password `password`



Notre idée pour le projet à été de faire un site pour les collectionneurs qui veulent compléter leurs collections sans payer des sommes énormes sur les sites qui vendent des objets de collection, mais en faisant des échanges avec les autre collectionneurs qui ont des doublons ou des objets de quels il n’ont plus besoin. On a cherché sur l'Internet et on n’a pas trouvé un site qui fait déjà ça, alors qu’on a décidé de le faire.

En principe, la plateforme qu’on a créé permet aux plusieurs utilisateurs de faire des trocs entre eux. Chaque utilisateur se connecte avec son compte et il peut:
- s’authentifier avec le username ou email (au choix) et le mot de passe
- ajouter des posts avec des objets de collection qu’il veut donner
- ajouter des posts avec des objets de collection qu’il cherche (demande)
- faire des trocs avec les autres utilisateurs en échangeant des objets parmi celles ajoutées par lui avec des objets ajouté par un autre utilisateur
- chercher des objects en utilisant la barre de recherche (il peut chercher d’après la catégorie d’objets ou d’après une suite des mots qu’il saisit - la fonction de search cherche aussi parmi les descriptions dans les posts)
- voir son profil

On a utilisé PHP, SQLite3, HTML, CSS, JS, JSON, Bootstrap et Python. On a eu des problèmes avec la configuration initiale de SQLite3, mais on a réussi finalement de les résoudre. On a aussi rencontré des difficultés avec la base de données concernant la mise à jour automatique, mais en a résolu ce problème aussi.

Pour que la page soit en plusieurs langues, on a utilisé des cookies. Premièrement, la page est dans la langue utilisé par le browser, mais elle peut être changé dans la barre de navigation en utilisant le dropdown. Ensuite, tout le texte va se modifier dans la langue sélectionnée. Pour cela, on a mis toutes les strings dans un fichier json avec les variantes de traduction pour chaque langue (anglais, français, roumain).

Pour que notre travail avec la manipulation et déclaration des strings soit moins difficile et plus efficace, on a écrit une fonction en python avec laquelle on a saisi les strings. La fonction se retrouve dans le projet : add-string.py

On n’a pas réussi à terminer l'interface pour la page de profil, la page pour ajouter des objets de collection et la page pour éditer le troc, mais la fonctionnalité est fini et elle marche correctement. Aussi, on a réussi à faire que l'interface soit responsive sur le portable.
