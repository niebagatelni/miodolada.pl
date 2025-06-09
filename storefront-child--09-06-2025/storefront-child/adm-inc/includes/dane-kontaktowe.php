<?php


//--------------------------------------------------------------------------
// Dane kontaktowe
//
//global $contact_fields;
//
adm_include_in_theme('/adm-inc/generated-dane-kontaktowe.php');

// Rejestracja strony w menu
add_action('admin_menu', 'register_contact_data_page');

function register_contact_data_page() {
    add_options_page(
        'Dane kontaktowe', // Tytuł strony
        'Dane kontaktowe', // Nazwa w menu
        'manage_options',   // Wymagane uprawnienia
        'contact-data',     // Slug strony
        'display_contact_data_page' // Funkcja wyświetlająca zawartość
    );
}

// Funkcja wyświetlająca stronę
function display_contact_data_page() {
$contact_fields = [
    'O stronie' => [
        'url_address' => 'Adres URL',
    ],
    'Twoja firma' => [
        'company_name' => 'Nazwa firmy',
        'company_full_name' => 'Pełna nazwa firmy',
        'company_address' => 'Adres firmy',
        'company_phone' => 'Telefon firmy',
        'company_email' => 'E-mail firmy',
        'company_nip' => 'NIP firmy',
        'company_regon' => 'REGON firmy',
    ],
    'Administrator' => [
        'admin_name' => 'Imię i nazwisko lub nazwa administratora',
        'admin_address' => 'Adres administratora',
        'admin_phone' => 'Telefon administratora',
        'admin_email' => 'E-mail administratora',
        'admin_nip' => 'NIP administratora',
        'admin_regon' => 'REGON administratora',
    ],
    'Operator' => [
        'operator_name' => 'Imię i nazwisko lub nazwa operatora',
        'operator_address' => 'Adres operatora',
        'operator_phone' => 'Telefon operatora',
        'operator_email' => 'E-mail operatora',
        'operator_nip' => 'NIP operatora',
    ],
];

	
   // Sprawdzenie uprawnień
    if (!current_user_can('manage_options')) {
        wp_die(__('Nie masz uprawnień do dostępu do tej strony.'));
    }

    // Obsługa formularza
    if (isset($_POST['save_contact_data']) && check_admin_referer('save_contact_data_nonce')) {


// Utworzenie katalogu jeśli nie istnieje
$directory = get_stylesheet_directory() . '/adm-inc/generated';
if (!file_exists($directory)) {
    wp_mkdir_p($directory);
}

// Przygotowanie zawartości pliku
$file_content = '<?php';




// Jedna pętla dla stałych i shortcode'ów
foreach ($contact_fields as $section => $fields) {
    $file_content .= "\n// {$section}\n";
    foreach ($fields as $field_key => $field_label) {
        if (isset($_POST[$field_key])) {
            $value = sanitize_text_field($_POST[$field_key]);
            $constant_name = strtoupper($field_key);
            
            // Podstawowa definicja stałej
            $file_content .= sprintf("define('%s', '%s');\n", $constant_name, addslashes($value));
            
            // Podstawowy shortcode
            $file_content .= sprintf("add_shortcode('%s', function() { return '%s'; });\n", 
                $field_key, 
                addslashes($value)
            );

            // Dodatkowe stałe i shortcody dla email i phone
            if (strpos($field_key, 'email') !== false) {
                // Stała z aktywnym linkiem email
                $file_content .= sprintf("define('%s_ACTIVE', '<a href=\"mailto:%s\">%s</a>');\n",
                    $constant_name,
                    addslashes($value),
                    addslashes($value)
                );
                
                // Shortcode z aktywnym linkiem email
                $file_content .= sprintf("add_shortcode('%s_active', function() { return '<a href=\"mailto:%s\">%s</a>'; });\n\n",
                    $field_key,
                    addslashes($value),
                    addslashes($value)
                );
            }
            elseif (strpos($field_key, 'phone') !== false) {
                // Formatowanie numeru telefonu - usuwanie spacji i myślników
                $clean_phone = preg_replace('/[\s-]/', '', $value);
                
                // Stała z aktywnym linkiem telefonu
                $file_content .= sprintf("define('%s_ACTIVE', '<a href=\"tel:%s\">%s</a>');\n",
                    $constant_name,
                    addslashes($clean_phone),
                    addslashes($value)
                );
                
                // Shortcode z aktywnym linkiem telefonu
                $file_content .= sprintf("add_shortcode('%s_active', function() { return '<a href=\"tel:%s\">%s</a>'; });\n\n",
                    $field_key,
                    addslashes($clean_phone),
                    addslashes($value)
                );
            } else {
                $file_content .= "\n";
            }
        }
    }
}





// Zapisanie pliku
$file_path = $directory . '/generated-dane-kontaktowe.php';
file_put_contents($file_path, $file_content);

// Sprawdzenie czy plik został utworzony
if (file_exists($file_path)) {
    echo '<div class="notice notice-success is-dismissible">';
    echo '<p>Plik z definicjami został wygenerowany pomyślnie.</p>';
    echo '</div>';
} else {
    echo '<div class="notice notice-error is-dismissible">';
    echo '<p>Wystąpił błąd podczas generowania pliku.</p>';
    echo '</div>';
}



        foreach ($contact_fields as $section => $fields) {
            foreach ($fields as $field_key => $field_label) {
                if (isset($_POST[$field_key])) {
                    $sanitized_value = sanitize_text_field($_POST[$field_key]);
                    update_option("adm__".$field_key, $sanitized_value);
                }
            }
        }
        
        echo '<div class="notice notice-success is-dismissible"><p>Dane zostały zapisane.</p></div>';
    }

    // Wyświetlenie formularza
    ?>
    <div class="wrap">
        <h1>Dane kontaktowe</h1>
        
        <form method="post" action="">
            <?php 
            wp_nonce_field('save_contact_data_nonce');
           // global $contact_fields;
            
            foreach ($contact_fields as $section => $fields): ?>
                <h2><?php echo esc_html($section); ?></h2>
                <table class="form-table">
                    <?php foreach ($fields as $field_key => $field_label): ?>
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr($field_key); ?>">
                                    <?php echo esc_html($field_label); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text"
                                       id="<?php echo esc_attr($field_key); ?>"
                                       name="<?php echo esc_attr($field_key); ?>"
                                       value="<?php echo esc_attr(get_option("adm__".$field_key, '')); ?>"
                                       class="regular-text"
                                />
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endforeach; ?>
            
            <p class="submit">
                <input type="submit" name="save_contact_data" class="button button-primary" value="Zapisz zmiany"/>
            </p>
        </form>
    </div>
    <?php
}


add_action('init', function () {
    $generated_file = ADM_THEME_DIR . 'adm-inc/generated/generated-dane-kontaktowe.php';
    if (file_exists($generated_file)) {
        require_once $generated_file;
    }
});

