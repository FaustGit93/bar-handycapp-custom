<?php
$t = [
    

      'menu_text' => 'Menu',

    'traduci_nome' => 'Automatically translate the name', // en.php

    // Login
    'titolo'                    => 'Restricted Area',
    'sottotitolo'               => 'Enter the administrator password',
    'placeholder'               => 'Password',
    'bottone'                   => 'Login',
    'errore_password'           => '❌ Wrong password. Try again.',

    // Admin - generale
    'pannello_admin'            => 'Admin Panel',
    'esci'                      => 'Logout',

    // Admin - categorie
    'gestione_categorie'        => '📂 Category Management',
    'nome_categoria'            => 'Category Name',
    'placeholder_categoria'     => 'E.g. Cocktails, Sandwiches...',
    'aggiungi'                  => '+ Add',
    'col_ordine'                => 'Order',
    'col_nome'                  => 'Name',
    'col_sposta'                => 'Move',
    'col_visibilita'            => 'Visibility',
    'col_azioni'                => 'Actions',
    'elimina'                   => 'Delete',
    'nessuna_categoria'         => 'No categories found.',
    'confirm_elimina_cat'       => 'Delete this category? Make sure it contains no items.',
    'title_visibile'            => 'Visible — click to hide',
    'title_nascosta'            => 'Hidden — click to show',

    // Admin - messaggi categorie
    'cat_aggiunta'              => '✅ Category added!',
    'cat_errore_ins'            => '❌ Error during insertion.',
    'cat_eliminata'             => '🗑️ Category deleted!',
    'cat_errore_el'             => '❌ Error during deletion.',
    'cat_impossibile_el'        => '⚠️ Cannot delete: there are',
    'cat_impossibile_el2'       => 'items in this category. Delete them first.',

    // Admin - piatti
    'nuovo_piatto'              => '🍽️ New Item / Drink',
    'categoria_menu'            => 'Menu Category',
    'seleziona_categoria'       => '-- Select a category --',
    'nome_prodotto'             => 'Product Name',
    'placeholder_prodotto'      => 'E.g. Aperol Spritz',
    'descrizione'               => 'Description / Ingredients',
    'contiene_allergeni'        => 'Does this item contain allergens?',
    'allergeni'                 => 'Allergens',
    'note_allergeni'            => 'Allergen notes (optional)',
    'placeholder_note'          => 'E.g. traces of nuts',
    'prezzo'                    => 'Price (€)',
    'disponibile_subito'        => 'Available immediately on the site',
    'aggiungi_menu'             => 'Add to Menu',

    // Admin - lista piatti
    'piatti_in_menu'            => '📋 Items in Menu',
    'nessun_piatto'             => 'No items found in the database.',
    'categoria_label'           => 'Category',
    'nome_label'                => 'Name',
    'descrizione_label'         => 'Description',
    'disponibile_label'         => 'Available',
    'salva_modifiche'           => 'Save changes',
    'confirm_elimina_piatto'    => 'Permanently delete',
    'title_disponibile'         => 'Available — click to mark as sold out',
    'title_esaurito'            => 'Sold out — click to make available',
    'title_modifica'            => 'Edit item',
    'title_elimina'             => 'Delete item',

    // Admin - messaggi piatti
    'piatto_inserito'           => '✅ Item added successfully!',
    'piatto_errore_ins'         => '❌ Error during insertion.',
    'piatto_aggiornato'         => '✅ Item updated successfully!',
    'piatto_errore_agg'         => '❌ Error during update.',
    'piatto_eliminato'          => '🗑️ Item deleted successfully!',
    'piatto_errore_el'          => '❌ Error during deletion.',

// Allergens
'all_allergeni' => 'Allergens',
'all_glutine'    => 'Gluten',
'all_crostacei'  => 'Crustaceans',
'all_uova'       => 'Eggs',
'all_pesce'      => 'Fish',
'all_arachidi'   => 'Peanuts',
'all_soia'       => 'Soy',
'all_latte'      => 'Milk',
'all_frutta_guscio' => 'Tree nuts',
'all_sedano'     => 'Celery',
'all_senape'     => 'Mustard',
'all_sesamo'     => 'Sesame',
'all_solfiti'    => 'Sulphur dioxide and sulphites',
'all_lupini'     => 'Lupin',
'all_molluschi'  => 'Molluscs',

//Theme
'tema_chiaro' => '☀️',
'tema_chiaro_testo' => '☀️ Light',
'tema_scuro' => '🌙',
'tema_scuro_testo' => '🌙 Dark',
'tema_sistema' => '🖥️',
'tema_sistema_testo' => '🖥️ System',
    
//Footer dev
'footer_sviluppato' => 'Developed by',

//VAT Number
'footer_piva' => 'VAT Number:',

//Footer Location
'footer_location' => 'Italy',

// Admin - image upload (errors)
'img_errore_upload'         => 'Image upload failed.',
'img_errore_peso'           => 'Image too large: the limit is 2MB.',
'img_errore_formato'        => 'Invalid format: only JPG, PNG or WEBP are allowed.',
'img_errore_salvataggio'    => 'Unable to save the image on the server.',
'img_errore_generico'       => 'Error while handling the image.',

// Admin - image/description/translation messages
'immagine_salvata'          => 'Image updated!',
'immagine_rimossa'          => 'Image removed!',
'immagine_nessuna_azione'   => 'Select a file or check the removal option.',
'descrizione_salvata'       => 'Description saved!',
'descrizione_errore'        => 'Error saving the description.',
'traduzione_salvata'        => 'Translation saved!',

// Admin - settings panel
'impostazioni_menu'             => 'Menu settings',
'layout_accordion_titolo'       => 'Group items into cards by category',
'layout_accordion_descrizione'  => 'Each category becomes an expandable card: click to open it and see the items inside.',
'layout_card_titolo'            => 'Show items as cards',
'layout_card_descrizione'       => 'Applies to the whole public menu (inside every category, in both list and accordion mode). Item images remain the ones already uploaded.',

// Admin - category images and descriptions
'contenuti_categorie'           => 'Category images and descriptions',
'contenuti_categorie_info'      => 'Optional: add an image and a short description for each category. If added, they are shown in the public menu; otherwise the category stays with just its name.',
'title_immagine'                => 'Image',
'title_descrizione'             => 'Description',
'immagine_categoria'            => 'Category image',
'rimuovi_immagine'              => 'Remove image',
'immagine_assente_categoria'    => 'No image uploaded for this category.',
'carica_immagine'               => 'Upload image',
'immagine_vincoli'              => 'JPG, PNG or WEBP formats — max size 2MB',
'salva_immagine'                => 'Save',
'descrizione_categoria'         => 'Category description',
'traduci_descrizione'           => 'Translate automatically',
'salva_descrizione'             => 'Save description',

// Admin - item image
'immagine_piatto'               => 'Item image',
'immagine_piatto_opzionale'     => 'Item image (optional)',
'immagine_assente'              => 'No image uploaded for this item.',

// Admin - bottom bar (dock)
'nav_categorie'                 => 'Categories',
'nav_nuovo_piatto'              => 'New item',
'nav_impostazioni'              => 'Settings',
'confirm_logout'                => 'Are you sure you want to log out?',

// Admin - manual translations
'traduzioni_titolo'             => 'Translations',
'salva_traduzione_btn'          => 'Save translation'

];
