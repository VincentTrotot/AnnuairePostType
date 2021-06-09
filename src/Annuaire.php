<?php

namespace VincentTrotot\Annuaire;

use Timber\Post;

class Annuaire extends Post
{
    public $contact;
    public $address;
    public $phone;
    public $mail;

    public function __construct($pid = null)
    {
        parent::__construct($pid);
        $this->contact = $this->meta('vt_annuaire_contact');
        $this->address = $this->meta('vt_annuaire_address');
        $this->phone = $this->meta('vt_annuaire_phone');
        $this->mail = $this->meta('vt_annuaire_mail');
    }
}
