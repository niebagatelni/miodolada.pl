<?php

function custom_admin_bar_shortcode_button($wp_admin_bar) {
    if (!current_user_can('manage_options')) {
        return; // Tylko dla administratorów
    }

    // Dodaj główny przycisk
    $wp_admin_bar->add_node([
        'id'    => 'shortcodes_button',
        'title' => 'Shortcodes',
        'href'  => '#',
        'meta'  => [
            'onclick' => 'return false;', // Zablokowanie domyślnej akcji linku
        ],
    ]);
}

add_action('admin_bar_menu', 'custom_admin_bar_shortcode_button', 100);

// Wyświetlanie okienka z listą shortcode'ów
function custom_shortcode_list_script() {
    if (!current_user_can('manage_options')) {
        return; // Tylko dla administratorów
    }

    // Pobierz wszystkie shortcode'y
    global $shortcode_tags;
    $shortcodes = $shortcode_tags;

    // Wstaw CSS i JavaScript
    ?>
    <style>
        #shortcodes-modal {
            display: none;
            position: fixed;
            top: 20%;
            left: 50%;
            transform: translate(-50%, -20%);
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            z-index: 99999;
            width: 400px;
            max-height: 60%;
            overflow-y: auto; /* Dodanie przewijania */
        }
        #shortcodes-modal ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        #shortcodes-modal ul li {
            padding: 5px 0;
            border-bottom: 1px solid #ccc;
        }
        #shortcodes-modal .close {
            text-align: right;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 10px;
            display: block;
        }
        #shortcodes-modal h3 {
            margin-top: 0;
        }
        #shortcodes-modal pre {
            background: #f4f4f4;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 12px;
            white-space: pre-wrap; /* Łamanie długich ciągów tekstowych */
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const button = document.getElementById('wp-admin-bar-shortcodes_button');
            const modal = document.getElementById('shortcodes-modal');
            const close = document.querySelector('#shortcodes-modal .close');

            button.addEventListener('click', function () {
                modal.style.display = 'block';
            });

            close.addEventListener('click', function () {
                modal.style.display = 'none';
            });

            window.addEventListener('click', function (event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
    <div id="shortcodes-modal">
        <div class="close">×</div>
        <h3>Zarejestrowane shortcodes</h3>
        <ul>
            <?php foreach ($shortcodes as $shortcode => $function): ?>
                <li>
                    <strong><?php echo esc_html($shortcode); ?></strong>
                    <pre><?php echo esc_html(is_callable($function) ? 'Funkcja: ' . (is_string($function) ? $function : 'Anonimowa') : 'Nieznany typ'); ?></pre>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
}

add_action('wp_footer', 'custom_shortcode_list_script');
add_action('admin_footer', 'custom_shortcode_list_script');


?>