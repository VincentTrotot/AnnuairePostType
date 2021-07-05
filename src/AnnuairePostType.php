<?php

namespace VincentTrotot\Annuaire;

use Timber\Timber;
use EasySlugger\Slugger;
use Symfony\Component\HttpFoundation\Request;

class AnnuairePostType
{
    public function __construct()
    {
        add_action('init', [$this, 'createPostType']);
        add_action('init', [$this, 'createCategory'], 0);
        add_filter('post_type_link', [$this, 'categoriesPostLink'], 1, 3);

        add_filter('manage_edit-vt_annuaire_columns', [$this, 'editColumns']);
        add_action('manage_posts_custom_column', [$this, 'customColumns']);
        add_filter('manage_edit-vt_annuaire_sortable_columns', [$this, 'sortableColumns']);
        add_action('restrict_manage_posts', [$this, 'filterCategory']);
        add_action('restrict_manage_posts', [$this, 'filterSubCategory']);

        add_action('admin_init', [$this, 'setupMetabox']);

        add_action('save_post', [$this, 'save']);

        add_filter('post_updated_messages', [$this, 'updatedMessages']);
        add_filter('parse_query', [$this, 'sortAnnuaire']);
    }

    /**
     * Création du custom post type  \
     * hook: init
     */
    public function createPostType()
    {
        $labels = [
            'name' => _x('Annuaire', 'annuaire'),
            'all_items' => __('Annuaire complet'),
            'singular_name' => _x('Fiche d\'annuaire', 'annuaire'),
            'add_new' => _x('Ajouter une fiche d\'annuaire', 'annuaire'),
            'add_new_item' => __('Ajouter une fiche d\'annuaire'),
            'edit_item' => __('Modifier la fiche d\'annuaire'),
            'new_item' => __('Nouvelle fiche d\'annuaire'),
            'view_item' => __('Voir la fiche d\'annuaire'),
            'search_items' => __('Rechercher dans l\'annuaire'),
            'not_found' =>  __('Pas de fiche trouvée'),
            'not_found_in_trash' => __('Pas de fiche trouvée dans la corbeille'),
            'parent_item_colon' => '',
            ];

        $args = [
            'label' => __('Annuaire'),
            'labels' => $labels,
            'public' => true,
            'can_export' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            '_builtin' => false,
            '_edit_link' => 'post.php?post=%d', // ?
            'capability_type' => 'post',
            'menu_icon' => 'dashicons-book-alt',
            'hierarchical' => false,
            'rewrite' =>[ 'slug' => 'annuaire/%vt_annuaire_category%' ],
            'has_archive' => 'annuaires',
            'supports'=>['title', 'editor', 'thumbnail', 'excerpt', 'author'] ,
            'show_in_nav_menus' => true,
            'taxonomies' =>[ 'vt_annuaire_category', 'vt_annuaire_sub_category']
        ];

        register_post_type('vt_annuaire', $args);
    }

    /**
     * Création des catégories et des sous-catégories  \
     * hook: init
     */
    public function createCategory()
    {
        $labels = [
            'name' => _x('Types d\'annuaire', 'taxonomy general name'),
            'singular_name' => _x('Type d\'annuaire', 'taxonomy singular name'),
            'search_items' =>  __('Rechercher dans les types'),
            'popular_items' => __('Types populaires'),
            'all_items' => __('Tous les types'),
            'parent_item' => null,
            'parent_item_colon' => null,
            'edit_item' => __('Modifier le type'),
            'update_item' => __('Mettre à jour le type'),
            'add_new_item' => __('Ajouter un type'),
            'new_item_name' => __('Nom du nouveau type'),
            'separate_items_with_commas' => __('Séparez les types avec des virgules'),
            'add_or_remove_items' => __('Ajouter ou supprimer un type'),
            'choose_from_most_used' => __('Choisir parmi les types les plus utilisés'),
        ];

        register_taxonomy('vt_annuaire_category', 'vt_annuaire', [
            'label' => __('Type de fiche d\'annuaire'),
            'labels' => $labels,
            'hierarchical' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'query_var' => true,
            'show_in_nav_menus' => true,
            'rewrite' =>[ 'slug' => 'annuaire' ],
        ]);

        $labels = [
            'name' => _x('Sous-catégories d\'annuaire', 'taxonomy general name'),
            'singular_name' => _x('Sous-catégorie d\'annuaire', 'taxonomy singular name'),
            'search_items' =>  __('Rechercher dans les sous-catégories'),
            'popular_items' => __('Sous-catégories populaires'),
            'all_items' => __('Toutes les sous-catégories'),
            'parent_item' => null,
            'parent_item_colon' => null,
            'edit_item' => __('Modifier la sous-catégorie'),
            'update_item' => __('Mettre à jour la sous-catégorie'),
            'add_new_item' => __('Ajouter une sous-catégorie'),
            'new_item_name' => __('Nom de la nouvelle sous-catégorie'),
            'separate_items_with_commas' => __('Séparez les sous-catégories avec des virgules'),
            'add_or_remove_items' => __('Ajouter ou supprimer une sous-catégorie'),
            'choose_from_most_used' => __('Choisir parmi les sous-catégories les plus utilisées'),
        ];

        register_taxonomy('vt_annuaire_sub_category', 'vt_annuaire', [
            'label' => __('Sous-catégorie de fiche d\'annuaire'),
            'labels' => $labels,
            'hierarchical' => false,
            'show_ui' => true,
            'show_in_rest' => true,
            'query_var' => true,
            'show_in_nav_menus' => true,
            'rewrite' =>[ 'slug' => 'type-annuaire' ],
        ]);
    }

    /**
     * Paramétrage du slug des catégories  \
     * hook: post_type_link
     */
    public function categoriesPostLink($post_link, $id = 0)
    {
        $post = get_post($id);
        if (is_object($post)) {
            $terms = wp_get_object_terms($post->ID, 'vt_annuaire_category');
            if ($terms) {
                return str_replace('%vt_annuaire_category%', Slugger::slugify($terms[0]->name), $post_link);
            }
        }
        return $post_link;
    }

    /**
     * Paramétrage des colonnes  \
     * hook: manage_edit-vt_annuaire_columns
     */
    public function editColumns($columns)
    {
        $columns = [
            'cb' => '<input type=\'checkbox\' />',
            'title' => 'Fiche d\'annuaire',
            'vt_col_annuaire_category' => 'Catégorie de fiche d\'annuaire',
            'vt_col_annuaire_sub_category' => 'Type de fiche d\'annuaire',
            'vt_annuaire_auth' => 'Auteur',
            ];

        return $columns;
    }

    /**
     * Paramétrage des colonnes  \
     * hook:manage_posts_custom_column
     */
    public function customColumns($column)
    {
        global $post;
        $custom = get_post_custom();
        switch ($column) {
            case 'vt_col_annuaire_category':
                $types = get_the_terms($post->ID, 'vt_annuaire_category');
                $type_html = [];
                if ($types) {
                    foreach ($types as $type) {
                        array_push($type_html, $type->name);
                    }
                    echo implode(', ', $type_html);
                }
                break;

            case 'vt_col_annuaire_sub_category':
                $types = get_the_terms($post->ID, 'vt_annuaire_sub_category');
                $type_html = [];
                if ($types) {
                    foreach ($types as $type) {
                        array_push($type_html, $type->name);
                    }
                    echo implode(', ', $type_html);
                }
                break;
            
            case 'vt_annuaire_auth':
                the_author();
                break;
        }
    }

    /**
     * Paramétrage des colonnes triables  \
     * hook: manage_edit-vt_annuaire_sortable_columns
     */
    public function sortableColumns($columns)
    {
        $columns['vt_col_annuaire_category'] = 'vt_annuaire_category';

        return $columns;
    }

    /**
     * Filtrage des catégories  \
     * hook: restrict_manage_posts
     */
    public function filterCategory()
    {
        global $typenow;
        $request = new Request($_GET);
        if ($typenow == 'vt_annuaire') {
            $current_category = $request->query->get('vt_annuaire_cat', '');
            $args_category = [
                'taxonomy' => 'vt_annuaire_category',
                'orderby' => 'name',
                'order'   => 'ASC',
                'hide_empty' => 0
            ];
            $cats = get_categories($args_category);
            $context['current_category'] = $current_category;
            $context['categories'] = $cats;

            Timber::render('templates/filter-category.html.twig', $context);
        }
    }

    /**
     * Ajoute un select sur la page d'admin des fiches d'annuaire  \
     * pour filtrer selon le custom tag vt_annuaire_sub_category  \
     * hook: restrict_manage_posts
     */
    public function filterSubCategory()
    {
        global $typenow;
        if ($typenow == 'vt_annuaire') {
            $terms = get_terms([
                    'taxonomy' => 'vt_annuaire_sub_category',
                    'hide_empty' => false,
                ]);
            if ($terms != null) {
                $context['terms'] = $terms;
                Timber::render('templates/filter-subcategory.html.twig', $context);
            }
        }
    }

    /**
     * Paramétrage de la meta box  \
     * hook: admin_init
     */
    public function setupMetabox()
    {
        add_meta_box(
            'vt_annuaire_meta',
            'Contact',
            [$this, 'displayMetabox'],
            'vt_annuaire',
            'side'
        );
    }

    /**
     * Affiche la meta box
     */
    public function displayMetabox()
    {
        $context['post'] = new Annuaire();
        $context['nonce'] = wp_create_nonce('vt-annuaire-nonce');
        
        if (function_exists('wp_enqueue_media')) {
            wp_enqueue_media();
        } else {
            wp_enqueue_style('thickbox');
            wp_enqueue_script('media-upload');
            wp_enqueue_script('thickbox');
        }
        
        Timber::render('templates/annuaire-meta-box.html.twig', $context);
    }

    /**
     * Sauvegarde des données  \
     * hook: save_post
     */
    public function save()
    {
        global $post;

        // - still require nonce

        if (!isset($_POST['vt-annuaire-nonce'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['vt-annuaire-nonce'], 'vt-annuaire-nonce')) {
            return $post->ID;
        }

        if (!current_user_can('edit_post', $post->ID)) {
            return $post->ID;
        }


        // saving meta
        update_post_meta($post->ID, "vt_annuaire_contact", $_POST['vt_annuaire_contact']);
        update_post_meta($post->ID, "vt_annuaire_address", $_POST['vt_annuaire_address']);
        update_post_meta($post->ID, "vt_annuaire_phone", $_POST['vt_annuaire_phone']);
        update_post_meta($post->ID, "vt_annuaire_mail", $_POST['vt_annuaire_mail']);
    }

    /**
     * Paramétrage des messages de mise à jour  \
     * hook: post_updated_messages
     */
    public function updatedMessages($messages)
    {
        global $post, $post_ID;
        $request = new Request($_GET);
        $revision = $request->query->get('revision');

        $messages['vt_annuaire'] = [
            0 => '', // Unused. Messages start at index 1.
            1 => sprintf(
                __('Annuaire mis à jour. <a href="%s">Voir la fiche d\'annuaire</a>'),
                esc_url(get_permalink($post_ID))
            ),
            2 => __('Champ mis à jour.'),
            3 => __('Champ supprimé.'),
            4 => __('Annuaire mis à jour.'),
            /* translators: %s: date and time of the revision */
            5 => $revision ? sprintf(
                __('Event restored to revision from %s'),
                wp_post_revision_title((int) $revision, false)
            ) : false,
            6 => sprintf(
                __('Fiche d\'annuaire publié. <a href="%s">Voir la fiche d\'annuaire</a>'),
                esc_url(get_permalink($post_ID))
            ),
            7 => __('Fiche d\'annuaire sauvegardé.'),
            8 => sprintf(
                __(
                    'Fiche d\'annuaire soumise. '
                    .'<a target="_blank" href="%s">Prévisualiser la fiche d\'annuaire</a>'
                ),
                esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))
            ),
            9 => sprintf(
                __(
                    'Fiche d\'annuaire programmée pour : '
                    .'<strong>%1$s</strong>. '
                    .'<a target="_blank" href="%2$s">Prévisualiser la fiche d\'annuaire</a>'
                ),
                date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date)),
                esc_url(get_permalink($post_ID))
            ),
            10 => sprintf(
                __(
                    'Brouillon de la fiche d\'annuaire mis à jour. '
                    .'<a target="_blank" href="%s">Prévisualiser la fiche d\'annuaire</a>'
                ),
                esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))
            ),
        ];

        return $messages;
    }

    /**
     * Paramétrage du tri  \
     * hook: parse_query
     */
    public function sortAnnuaire($query)
    {
        global $pagenow;
        $request = new Request($_GET);
        $post_type = $request->query->get('post_type', '');
        $vt_annuaire_type = $request->query->get('vt_annuaire_type');
        $vt_annuaire_category = $request->query->get('vt_annuaire_category');
        $vt_annuaire_cat = $request->query->get('vt_annuaire_cat');
        
        if (! isset($query->query_vars['meta_query'])) {
            $query->query_vars['meta_query'] =[];
        }
        
        // append to meta_query array
        if (is_admin() && $pagenow=='edit.php' && $post_type == 'vt_annuaire') {
            if ($vt_annuaire_type && $vt_annuaire_category) {
                $query->query_vars['meta_query']['relation'] = 'OR';
            }

            if ($vt_annuaire_type) {
                $meta = [
                    'key'  =>   'vt_annuaire_type',
                    'value' =>   $vt_annuaire_type
                ];
                $query->query_vars['meta_query'][] = $meta;
            }

            if ($vt_annuaire_cat) {
                $meta = [
                    'key'  =>   'vt_annuaire_category',
                    'value' =>   $vt_annuaire_cat
                ];
                $query->query_vars['meta_query'][] = $meta;
            }
        }
    }

    public static function menu($taxonomy, $before = 'Annuaire des ') : string
    {
        $result = '';
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ]);
        if ($terms != null) {
            $result .= '<ul class="menu-annuaires">';
            foreach ($terms as $term) {
                $link = get_term_link($term->term_id);
                $name = $before.strtolower($term->name);
                $result .= '<li><a href="'.$link.'">'.$name.'</a></li>';
            }
            $result .= '</ul>';
        }
        return $result;
    }

    public static function tags() : string
    {
        $result = '';
        $terms = get_terms([
            'taxonomy' => 'vt_annuaire_sub_category',
            'hide_empty' => false,
        ]);
        if ($terms != null) {
            $result .= '<div class="menu-annuaires"> Voir par catégorie : ';
            $first = true;
            foreach ($terms as $term) {
                $link = get_term_link($term->term_id);
                $name = strtolower($term->name);
                if (!$first) {
                    $result .= ' \ ';
                }
                $result .= '<span><a href="'.$link.'">'.$name.'</a></span>';
                $first = false;
            }
            $result .= '</div>';
        }
        return $result;
    }
}
