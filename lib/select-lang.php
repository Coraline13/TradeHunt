<div id="select-lang">
    <label><?php echo _t('u', STRING_SELECT_LANGUAGE) ?>:</label>
    <ul id="lang-list">
        <?php
        global $_LOCALE, $_SUPPORTED_LOCALES;

        $locales = [
            'en' => STRING_LANG_EN,
            'fr' => STRING_LANG_FR,
            'ro' => STRING_LANG_RO,
        ];

        foreach ($locales as $code => $string) {
            $selected = $_LOCALE == $code ? 'lang-selected' : '';
            echo "<li><a href=\"${GLOBALS['root']}set_locale.php?locale=$code\">";
            echo "<img src=\"${GLOBALS['root']}static/img/flag-$code.svg\" alt=\"\"/>&nbsp;";
            echo "<span class=\"$selected\">"._t(null, $string)."</span>";
            echo "</a></li>";
        }
        ?>
    </ul>
</div>
