# AnnuairePostType

AnnuairePostType est un package permettant d'ajouter un Custom Post Type `vt_annuaire` à un theme WordPress.  
Ce Custom Post Type ajoute quatre metadata : un responsable, une adresse, un numéro de téléphone et un courriel.

La classe `VincentTrotot\AnnuairePostType` paramètre le Custom Post Type tandis que la classe `VincentTrotot\Annuaire` est une espèce de wrapper du Post (la classe hérite de la classe `Timber\TimberPost`).

## Installation

```bash
composer require vtrotot/annuaire-post-type
```

## Utilisation

Votre theme doit instancier la classe `AnnuairePostType`

```php
new VincentTrotot\Annuaire\AnnuairePostType();
```

Vous pouvez ensuite récupérer un Post de type annuaire:

```php
$post = new VincentTrotot\Annuaire\Annuaire();
```

Ou récupérer plusieurs posts avec :

```php
$args = [
    'post_type' => 'vt_annuaire',
    ...
];
$posts = new Timber\TimberRequest($args, VincentTrotot\Annuaire\Annuaire::class);
```
