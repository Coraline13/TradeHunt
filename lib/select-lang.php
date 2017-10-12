<div id="lang_sel_click" onclick="wpml_language_selector_click.toggle();" class="lang_sel_click">
    <ul>
        <li>
            <?php
            global $_LOCALE;

            $locales = [
                'en' => STRING_LANG_EN,
                'fr' => STRING_LANG_FR,
                'ro' => STRING_LANG_RO,
            ];
            ?>
            <a href="javascript:;" class="lang_sel_sel icl-<?php echo $_LOCALE ?> lang_sel_link">
                <?php echo "<img src=\"${GLOBALS['root']}static/img/flag-$_LOCALE.svg\" alt=\"\"/>&nbsp;"?>
                <span class="lang_text"><?php echo _t(null, $locales[$_LOCALE]) ?></span>
            </a>
            <ul>
                <?php
                foreach ($locales as $code => $string) {
                    if ($code == $_LOCALE) {
                        continue;
                    }
                    echo "<li class=\"icl-lang\" data-lang=\"$code\">";
                    echo "<a href=\"${GLOBALS['root']}set_locale.php?locale=$code\">";
                    echo "    <img src=\"${GLOBALS['root']}static/img/flag-$code.svg\" alt=\"\"/>&nbsp;&nbsp;";
                    echo "    <span class=\"icl_lang_sel_translated\">"._t(null, $string)."</span>";
                    echo "</a></li>";
                }
                ?>
            </ul>
        </li>
    </ul>
</div>
