<?php

//equipment wired
function equpwired_func(){
    $allowed_pages = [
        'satel',
        'lun',
    ];
    if ( is_page( $allowed_pages ) ) {
        add_shortcode('equipment_wired', 'equipment_func');
        function equipment_func($atts)
        {
            ob_start();
            global $wpdb;

            $query = "SELECT wpp.ID, wpp.post_title, wpp.post_content, pm1.meta_value as basic_kit, pm2.meta_value as price, pm3.meta_value as wireless, pm4.meta_value as number, wpm_piece.meta_value as pieces, pm5.meta_value as img, pmwhite.meta_value as img_white, wpp2.guid as imgsrc, wpp3.guid as img_whitesrc FROM wp_posts as wpp LEFT JOIN wp_postmeta AS pm1 ON (wpp.ID = pm1.post_id AND pm1.meta_key='basic_kit') LEFT JOIN wp_postmeta AS pm2 ON (wpp.ID = pm2.post_id  AND pm2.meta_key='price') LEFT JOIN wp_postmeta AS pm3 ON (wpp.ID = pm3.post_id  AND pm3.meta_key='wireless-wired') LEFT JOIN wp_postmeta AS pm4 ON (wpp.ID = pm4.post_id  AND pm4.meta_key='number') LEFT JOIN wp_postmeta AS wpm_piece ON (wpp.ID = wpm_piece.post_id  AND wpm_piece.meta_key='pieces') LEFT JOIN wp_postmeta AS pm5 ON (wpp.ID = pm5.post_id  AND pm5.meta_key='img') LEFT JOIN wp_postmeta AS pmwhite ON (wpp.ID = pmwhite.post_id  AND pmwhite.meta_key='img_white') LEFT JOIN wp_posts AS wpp2 ON (wpp2.ID = pm5.meta_value) LEFT JOIN wp_posts AS wpp3 ON (wpp3.ID = pmwhite.meta_value) WHERE wpp.post_type = 'post_ajax' AND wpp.post_status = 'publish' AND pm3.meta_value = 0 GROUP BY wpp.ID ORDER BY wpp.post_date DESC";
            $post_ajax = $wpdb->get_results($query);

            foreach ($post_ajax as $key => $value) {
                $post_ajax[$key] = (array)$value;
            }

            usort($post_ajax, function ($a, $b) {
                return ((int)$a['number'] - (int)$b['number']);
            });

            if (!empty($post_ajax)) { ?>
                <div class="section-offer" data-service="apartment">
                    <div class="selectors-container">
                        <p class="selector" data-target="#offer-standard">Проводной комплект</p>
                    </div>

                    <!-- БАЗОВЫЙ КОМПЛЕКТ ПРОВОДНОЙ-->
                    <div id="offer-standard" class="offer-container row" data-target="Проводной комплект">
                        <div class="equipment-container eight dfd_col-tabletop-7 dfd_col-laptop-7 dfd_col-mobile-12 columns animated fadeInLeft">
                            <div class="equipment-wrapper">
                                <div class="eq-header"><p>Базовый комплект проводной</p></div>

                                <?php foreach ($post_ajax as $ajax) { ?>
                                    <?php if ((int)$ajax['basic_kit'] && empty($ajax['wireless'])) { ?>
                                        <div class="eq-row">

                                            <div class="eq-image"><img class="active" src="<?php echo($ajax['imgsrc']); ?>" alt=""></div>
                                            <div class="eq-title"><p><?php echo $ajax['post_title']; ?></p></div>
                                            <div class="eq-quantity"><p><?php echo $ajax['pieces']; ?> шт.</p></div>
                                            <div class="eq-check"><i class="dfd-socicon-correct-symbol"></i></div>
                                            <div class="eq-info">
                                                <i class="dfd-socicon-information-button"></i>
                                                <div class="info-container animated fadeInUp">
                                                    <div class="info-image">
                                                        <img class="active" src="<?php echo($ajax['imgsrc']); ?>" alt="">
                                                    </div>
                                                    <div class="info-content">
                                                        <p><?php echo $ajax['post_title']; ?></p>
                                                        <span><?php echo $ajax['post_content']; ?></span>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    <?php } ?>
                                <?php }
                                ?>
                            </div>


                            <p class="equipment-notice">Вышеуказанная информация предназначена для ознакомления. Сумма
                                коммерческого
                                предложения может отличаться от указанной на сайте. Внешний вид оборудования может
                                отличаться от
                                представленного на фотографиях.</p>
                        </div>

                        <div class="summary-container four dfd_col-tabletop-5 dfd_col-laptop-5 dfd_col-mobile-12 columns animated fadeInRight">
                            <div class="summary sticky-summary">
                                <p class="summary-title">Базовый комплект</p>
                                <div class="summary-row equipment-price">
                                    <p class="item-title">Стоимость оборудования</p>
                                    <?php
                                    if(is_page('lun')){
                                        $price = 4800;
                                        $abon = 300;
                                        $montag = 800;
                                    }elseif(is_page('satel')){
                                        $price = 5100;
                                        $abon = 300;
                                        $montag = 800;
                                    }
                                    ?>
                                    <p class="item-value">от <span class="price-container price-wireless"><?php echo $price; ?></span> грн</p>
                                </div>
                                <div class="summary-row base-service-price" style="display: flex;">
                                    <p class="item-title">Абонентская плата</p>
                                    <p class="item-value">от <span><?php echo $abon; ?></span> грн/месяц</p>
                                </div>
                                <div class="summary-row base-install-price" style="display: flex;">
                                    <p class="item-title">Монтаж и подключение</p>
                                    <p class="item-value">от <span><?php echo $montag; ?></span> грн</p>
                                </div>
                                <div class="additional-summary animated fadeIn" style="display: none;">
                                    <p class="summary-title">Доп. оборудование</p>
                                    <div class="summary-row equipment-price">
                                        <p class="item-title">Стоимость оборудования</p>
                                        <p class="item-value"><span
                                                    class="price-container additional-equipment-price">0</span>
                                            грн</p>
                                    </div>
                                </div>
                                <div class="total-summary animated fadeIn" style="display: none;">
                                    <p class="summary-title">Общая стоимость</p>
                                    <div class="summary-row equipment-price">
                                        <p class="item-title">Стоимость оборудования</p>
                                        <p class="item-value"><span class="price-container total-price"><?php echo $price; ?></span> грн</p>
                                    </div>
                                    <div class="summary-row">
                                        <p class="item-title">Абонентская плата</p>
                                        <p class="item-value"><span><?php echo $abon; ?></span> грн/месяц</p>
                                    </div>
                                </div>
                                <div class="summary-row order-button-container">
                                    <span class="summary-submit service-order-button">Оставить заявку</span>
                                </div>
                            </div>
                        </div>


                    </div>

                </div>

                <?php
                echo do_shortcode('[vc_row el_class="equipment-callback-form"][vc_column][dfd_modal_box display_options="on_click" module_animation="transition.flipXIn" button_text="Обратный звонок" hover_border="border_style:solid|border_top_width:1|border_bottom_width:1|border_left_width:1|border_right_width:1|border_radius:1|border_color:%230e83cb" modal_tb_padding="60" modal_lr_padding="60" text_color="#ffffff" text_hover_color="#313131" background="#0e83cb" hover_background="rgba(255,255,255,0.01)" box_shadow="box_shadow_enable:disable|shadow_horizontal:0|shadow_vertical:15|shadow_blur:50|shadow_spread:0|box_shadow_color:rgba(0%2C0%2C0%2C0.35)" hover_box_shadow="box_shadow_enable:disable|shadow_horizontal:0|shadow_vertical:15|shadow_blur:50|shadow_spread:0|box_shadow_color:rgba(0%2C0%2C0%2C0.35)" button_font_options="line_height:45" el_class="equipment-modal"][dfd_heading style="style_02" subtitle="" module_animation="transition.slideUpBigIn" enable_delimiter="" undefined="" title_font_options="tag:h4" subtitle_font_options="tag:div" title_responsive="font_size_desktop:22|line_height_desktop:27|font_size_tablet:20|line_height_tablet:25|font_size_mobile:18|line_height_mobile:23" tutorials=""]Ajax[/dfd_heading][dfd_spacer screen_wide_spacer_size="20" screen_normal_resolution="1280" screen_tablet_resolution="1024" screen_mobile_resolution="800" screen_normal_spacer_size="20" screen_tablet_spacer_size="15" screen_mobile_spacer_size="15"][dfd_user_form module_animation="transition.slideUpBigIn" input_background="#ffffff" show_label_text="on" border_color="rgba(29,34,39,0.5)" horiz_margin_btw_inputs="10" letter_spacing=".8" button_backgrond="#0e83cb" hover_button_backgrond="#026daf" button_color_text="#ffffff" button_hover_color_text="#ffffff" btn_message="Отправить" btn_width="dfd-third-size" email_subject="Заказ оборудования" use_custom_layout="on" layout_builder="{1+}{``name``:````,``presets``:{1+}{``name``:``col1``}{+1}},{``name``:````,``presets``:{1+}{``name``:``add_col``}{+1}},{``name``:````,``presets``:{1+}{``name``:``add_col``}{+1}}{+1}" custom_template="{``1-1``:{``text_name``:{````:````,``required-1``:``1``,``name``:``Имя``}},``1-2``:{``telephone``:{``required-1``:``1``,``name``:``Телефон``}},``1-3``:{``email``:{``name``:``E-mail``}},``2-1``:{``telephone``:{``required-1``:``1``,``name``:``Телефон``}},``3-1``:{``textarea_name``:{}}}" borderwidth="1" border_radius="1"]Имя: {{text_name-1-1}}
Телефон: {{telephone-2-1}}
Заказ: {{textarea_name-3-1}}[/dfd_user_form][/dfd_modal_box][/vc_column][/vc_row]');
            }
            ?>
            <?php $output = ob_get_clean();
            return apply_filters('dfd_ajax_filter', $output);
        }
        wp_register_script('dfd-equipment', get_template_directory_uri().'/assets/js/equipment.js', array('jquery'), null, true);
        wp_enqueue_script('dfd-equipment');
        function wpse27856_set_content_type(){
            return "text/html";
        }
        add_filter( 'wp_mail_content_type','wpse27856_set_content_type' );
        $custom_css = file_get_contents(get_template_directory().'/inc/ajax.css');
        wp_add_inline_style( 'dfd_site_style', $custom_css );
    }
}
add_action( 'wp_enqueue_scripts', 'equpwired_func',999 );