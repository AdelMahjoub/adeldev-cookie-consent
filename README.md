# Adeldev Cookie Consent

Plugin WordPress de consentement aux cookies pour les sites qui chargent GA4 et Google Ads avec Google Tag Manager. Il s'appuie sur [tarteaucitron](https://github.com/AmauriC/tarteaucitron.js), hébergé dans le plugin, et sur Google Consent Mode v2.

## Fonctionnement

- **Avant GTM :** le plugin écrit le consentement par défaut, en tout début de `<head>`. Les quatre signaux (`ad_storage`, `ad_user_data`, `ad_personalization`, `analytics_storage`) sont à `denied`, sauf si le visiteur a déjà accepté lors d'une visite précédente.
- **Bandeau :** tarteaucitron affiche un bandeau en bas de page, avec « Tout accepter », « Tout refuser » et « Personnaliser ». Les deux premiers boutons ont le même style, comme le demande la CNIL.
- **Services :** il y a deux services au choix, « Google Analytics » et « Google Ads ». Chacun envoie la mise à jour `gtag('consent', 'update', …)` correspondante et supprime ses cookies en cas de refus.
- **Conservation du choix :** le choix est conservé 6 mois dans le cookie `adeldev_cookie_consent`.
- **Modifier son choix :** un lien vers `#gerer-cookies` rouvre le panneau, par exemple « Gérer les cookies » dans le pied de page.
- **Optimiseurs de scripts :** les scripts portent `data-no-optimize`, `data-no-defer` et `data-cfasync="false"`, pour que LiteSpeed Cache ou Cloudflare ne les retardent pas.

Le plugin ne charge pas GTM : celui-ci reste chargé par le thème ou par un extrait (WPCode, par exemple), qui doit s'exécuter après le plugin dans `wp_head`. Dans GTM, les balises Google (GA4, Google Ads) tiennent compte du consentement sans réglage supplémentaire.

## Installation

1. Télécharger `adeldev-cookie-consent.zip` depuis la [dernière release](https://github.com/AdelMahjoub/adeldev-cookie-consent/releases/latest).
2. Dans WordPress, aller dans Extensions → Ajouter → Téléverser une extension, puis l'activer.
3. Dans **Réglages → Cookies**, renseigner :
   - l'ID de mesure GA4 (`G-…`) ;
   - si le conteneur contient des balises Google Ads ;
   - la page de politique de confidentialité.
4. Ajouter un lien « Gérer les cookies » vers `#gerer-cookies`.

### Apparence

Dans la même page, section « Apparence » :

- **Format :** barre en bas, barre en haut, carte dans un coin ou fenêtre au centre.
- **Côté :** gauche ou droite, pour la carte et la bulle. Choisissez le côté opposé aux boutons flottants du site.
- **Décalage depuis le bas :** une valeur pour ordinateur et une pour les écrans de moins de 1024 px. Il sert à passer au-dessus d'une barre fixe, par exemple une barre d'appel sur mobile.
- **Bulle :** une icône permanente pour modifier son choix, avec une image au choix dans la médiathèque.
- **Couleurs :** fond, texte, boutons et texte des boutons. Laissées vides, elles reprennent les couleurs du thème (`accent-1`, `contrast`).

Un thème peut aussi définir ces valeurs en CSS : `--adeldev-cc-background`, `--adeldev-cc-text`, `--adeldev-cc-accent`, `--adeldev-cc-accent-text`.

## Mises à jour

- **Mises à jour du plugin :** WordPress les propose comme pour n'importe quelle extension, à partir des releases de ce dépôt (en-tête `Update URI`). On peut aussi activer les mises à jour automatiques sur chaque site.
- **Nouvelles versions de tarteaucitron :** une GitHub Action les recherche chaque semaine. Quand il y en a une, elle copie les fichiers et ouvre une pull request avec une nouvelle version du plugin.
- **Publication :** une release est publiée dès que la version du plugin change sur `main`.

## Licence

Ce plugin est distribué sous licence [GPL-2.0-or-later](LICENSE).

Il inclut, sans modification, des fichiers de [tarteaucitron.js](https://github.com/AmauriC/tarteaucitron.js) © AmauriC. Ces fichiers sont distribués sous licence MIT, dont le texte se trouve dans [assets/vendor/tarteaucitron/LICENSE](assets/vendor/tarteaucitron/LICENSE).
