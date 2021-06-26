<?php

namespace VincentTrotot\Annuaire;

use Timber\Post;

class Annuaire extends Post
{
    public $contact;
    public $address;
    public $phone;
    public $mail;
    
    public static $sub_categories = [];

    public function __construct($pid = null)
    {
        parent::__construct($pid);
        $this->contact = $this->meta('vt_annuaire_contact');
        $this->address = $this->meta('vt_annuaire_address');
        $this->phone = $this->meta('vt_annuaire_phone');
        $this->mail = $this->meta('vt_annuaire_mail');
        
        $sub_categories = $this->terms([
            'query' => [
                'taxonomy' =>'vt_annuaire_sub_category',
                'orderby' => 'title',
                'order' => 'ASC'
            ]
        ]);
        
        foreach ($sub_categories as $sub_categorie) {
            if (!in_array($sub_categorie, self::$sub_categories)) {
               self::$sub_categories[] = $sub_categorie;
           }
        }
    }
}
