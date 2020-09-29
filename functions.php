<?php
/**
 * DFD themes functions
 */


//add menu
add_action('admin_menu', function(){
    add_menu_page( 'Настройки страницы оплаты', __('Payment for services','dfd-native'), 'manage_options', 'payment-options', 'add_my_setting', '', "60.5" );
} );

// функция отвечает за вывод страницы настроек Онлайн оплата
function add_my_setting(){
    if (isset($_POST['payment_title_ajax'])) {
        update_option('payment_title_ajax', $_POST['payment_title_ajax']);
    }
    if (isset($_POST['enterprise'])) {
        update_option('enterprise_list', json_encode($_POST['enterprise']));
    } ?>
    <div class="wrap">
        <h2><?php echo get_admin_page_title() ?></h2>

        <form method="post" action="">
            <div class="inside">
                <h3><span>Заголовок</span></h3>
                <input type="text" name="payment_title_ajax" style="width:100%;" value="<?php echo get_option('payment_title_ajax'); ?>">
            </div>
            <div class="inside" id="enterprise">
                <h3><span>Список предприятий</span></h3>
                <?php
                $itemtmp = '<li><input type="text" name="enterprise[]" value="{{list}}"><span class="delete-ent button">Удалить</span></li>';
                $listenter = json_decode(get_option('enterprise_list',''));
                $n=0;
                echo '<ul class="enterprise_list">';
                do{
                    $replacement = isset($listenter[$n])? $listenter[$n] : '';
                    $item = preg_replace('/{{list}}/',$replacement, $itemtmp);
                    echo $item;
                    $n++;
                }while($n < count($listenter));
                echo '</ul>';
                echo '<span class="add-enterprise button" style="margin-bottom: 20px;">Добавить предприятие</span>';
                ?>

            </div>
            <div class="inside">
                <input type="submit" class="button-primary" name="payment_submit" value="Сохранить">
            </div>
        </form>
        <p>Шорткод формы: [payment_online]</p>
        <script>
            $('body').on('click','.delete-ent',function(e){
                var $target = $(e.target);
                $target.closest('li').remove();
            });
            $('#enterprise').find('.add-enterprise').on('click',function(e){
                var $list = $('.enterprise_list');
                var item = '<li><input type="text" name="enterprise[]" value=""><span class="delete-ent button">Удалить</span></li>';
                $list.append(item);
            });
        </script>
    </div>
    <?php
}

//Онлайн оплата
function my_wp_enqueue_scripts() {
    $allowed_pages = [
        'oplata-onlajn',
    ];
    if ( is_page( $allowed_pages ) ) {
        add_shortcode( 'payment_online', 'payment_callback' );
        function payment_callback(){
            ob_start();
            require_once get_template_directory().'/inc/payment.php';
            $output = ob_get_clean();
            return apply_filters('payment_online_filter', $output);
        }

        function payment_script(){ ?>
            <script>
                (function($){
                    $( document ).ready(function() {
                        $('.submit-payment').on('click', function(e){
                            e.preventDefault();
                            var errors = {};
                            var $widget = $('.payment-widget');
                            var payment_id = parseInt($widget.find('#payment_id').val(),10);
                            if(!payment_id){
                                errors['payment_id'] = 'Укажите номер договора';
                            }
                            var price = parseInt($widget.find('#pay_sum').val(),10);
                            if(!price){
                                errors['price'] = 'Укажите сумму';
                            }
                            var select = $('.payment_select').val();
                            if(!select){
                                select = $('.payment_select').find('.dk-option-selected:not(".dk-option-disabled")').attr('data-value');
                                if(!select){
                                    errors['select'] = 'Укажите предприятие';
                                }
                            }
                            if (Object.keys(errors).length) {
                                var errorhtml = '';
                                for(var error in errors){
                                    errorhtml += '<p>'+errors[error]+'</p>';
                                }
                                $widget.find('.alert').html(errorhtml);
                                return;
                            }
                            var data = {
                                action: 'create_payment_form',
                                select: select,
                                payment_id: payment_id,
                                price: price,
                                dfd_nonce: "<?php echo wp_create_nonce( 'dfd_name_of_nonce_field' ); ?>"
                            };
                            console.log("data");
                            console.log(data);
                            $.ajax({
                                type: "post",
                                url: ajax_var.url,
                                data: data,
                                success: function (form) {
                                    // console.log(form);
                                    // var response = JSON.parse(resp);
                                    if(form){
                                        var $wayforpayForm = $('.wayforpay-form');
                                        $wayforpayForm.html(form);
                                        setTimeout(function(){
                                            var $input = $wayforpayForm.find('form');
                                            if($input.length){
                                                $input.trigger('submit');
                                            }
                                        },500);
                                    }
                                },
                                error: function(err){
                                    console.log(err);
                                }
                            });
                        });
                    });

                })(jQuery);
            </script>
        <?php }
        add_action('wp_footer','payment_script');
    }
}
add_action( 'wp_enqueue_scripts', 'my_wp_enqueue_scripts' );


function create_payment_form(){
    if(session_id() == '') {
        session_start();
    }
    if(isset($_POST) && !empty($_POST['payment_id'])){
        if ( ! wp_verify_nonce( $_POST['dfd_nonce'], 'dfd_name_of_nonce_field') ){
            print 'Извините, проверочные данные не соответствуют.';
            exit;
        }
        $errors = [];

        if(isset($_POST['select'])){
            $select = sanitize_text_field($_POST['select']);
        }
        if(isset($_POST['payment_id'])){
            $payment_id = (int)sanitize_text_field($_POST['payment_id']);
        }
        if(isset($_POST['price'])){
            $price = (int)sanitize_text_field($_POST['price']);
        }

        if(empty($_POST['select'])){
            $errors[] = 'Укажите предприятие';
        }
        if(empty($_POST['payment_id'])){
            $errors[] = 'Укажите номер договора';
        }
        if(empty($_POST['price'])){
            $errors[] = 'Укажите сумму';
        }

        if(!count($errors)){

            $arrayCompanyKeys = require get_template_directory().'/inc/company_keys.php';

            $company = null;
            if(isset($arrayCompanyKeys[$select])){
                $company = $select;
            }
            $order = rand(10000, 99999);

            if(!empty($company)){

                $public = $company;
                $private = json_decode(base64_decode($arrayCompanyKeys[$company]['private']));

                require_once get_template_directory(). "/inc/WayForPay.php";
                $wayforpay = new WayForPay($public, $private);

                $params = array(
                    'merchantAccount' => $public,
                    'merchantDomainName' => 'https://shumer.com.ua/',
                    'orderReference' => $order,
                    'orderDate' => time(),
                    'amount' => $price,
                    'currency' => 'UAH',
                    'productName' => $arrayCompanyKeys[$company]['name'],
                    'productCount' => 1,
                    'productPrice' => $price
                );

                $sign = hash_hmac('md5', implode(';', $params), $private);

                $params['merchantTransactionSecureType'] = 'AUTO';
                $params['returnUrl'] = 'https://shumer.com.ua/result/';
//                $params['serviceUrl'] = 'https://shumer.com.ua/request_payment/';
                $params['merchantSignature'] = $sign;
                $params['orderNo'] = $payment_id;


                $html = $wayforpay->buildForm($params);
//                $html = $wayforpay->buildWidgetButton($params);

                echo $html;
                exit;
            }

            exit;
        }else{
            echo json_encode(['errors'=> implode(',', $errors)]);
            exit;
        }
    }
}
add_action('wp_ajax_create_payment_form', 'create_payment_form');
add_action('wp_ajax_nopriv_create_payment_form', 'create_payment_form');


//callback после оплаты
//add_action('wp', function(){
//    if(is_page('request_payment')) { //проверим чтобы код не срабатывал на всех страницах
//        if (!empty($_POST) ) {
//
//            $json = file_get_contents('php://input');
//            $result = json_decode($json, TRUE);
//
//            if ($result['transactionStatus'] == 'Approved') {
//                $answer = array(
//                    'orderReference' => $result['orderReference'],
//                    'status' => 'accept',
//                    'time' => time()
//                );
//
//                $arrayCompanyKeys = require get_template_directory().'/inc/company_keys.php';
//                $private = json_decode(base64_decode($arrayCompanyKeys[$result['merchantAccount']]['private']));
//
//                $sign = hash_hmac('md5', implode(';', $answer), $private);
//                $answer['signature'] = $sign;
//                echo json_encode($answer);
//
//                $merchant = $result['merchantAccount'];
//                $company = $arrayCompanyKeys[$merchant]['name'];
//                $order = $result['orderReference'];
//                $payment_id = isset($result['orderNo'])? $result['orderNo'] : '';
//                $amount = $result['amount'];
//                $body = 'Успешная оплата<br>';
//                if(!empty($order)){
//                    $body .= 'Номер заказа: '.$order .'<br>';
//                }
//                if(!empty($company)){
//                    $body .= 'Предприятие: '.$company .'<br>';
//                }
//                if(!empty($payment_id)){
//                    $body .= 'Номер договора: '.$payment_id .'<br>';
//                }
//                if(!empty($amount)){
//                    $body .= 'Сумма: '. $amount . ' грн.<br>';
//                }
//
//                $headers = array('Content-Type: text/html; charset=UTF-8');
//                wp_mail('shumer4949@gmail.com', 'Успешная оплата', $body, $headers);
//                wp_mail('vyalov.u.s@gmail.com', 'Успешная оплата', $body, $headers);
//                die;
//            }
//        }
//    }
//});


//страница возврата
if (!function_exists('result_payment')) {
    add_shortcode('result_payment', 'result_payment');
    function result_payment($atts)
    {
        $html = '<div class="payment-result"><h4>Ваш заказ в обработке</h4><br>';
        $html .= '</div>';
        if(!empty($_POST)){

            $arrayCompanyKeys = require get_template_directory().'/inc/company_keys.php';
            $post = $_POST;
            $merchant = $post['merchantAccount'];
            $company = $arrayCompanyKeys[$merchant]['name'];
            $order = $post['orderReference'];
            $payment_id = isset($post['orderNo'])? $post['orderNo'] : '';
            $amount = $post['amount'];
            $body = 'Платёж в обработке<br>';
            if(!empty($order)){
                $body .= 'Номер заказа: '.$order .'<br>';
            }
            if(!empty($company)){
                $body .= 'Предприятие: '.$company .'<br>';
            }
            if(!empty($payment_id)){
                $body .= 'Номер договора: '.$payment_id .'<br>';
            }
            if(!empty($amount)){
                $body .= 'Сумма: '. $amount . ' грн.<br>';
            }
//            $headers = array('Content-Type: text/html; charset=UTF-8');
//            wp_mail('shumer4949@gmail.com', 'Платёж в обработке', $body, $headers);
//            wp_mail('vyalov.u.s@gmail.com', 'Платёж в обработке', $body, $headers);
        }
        echo $html;

    }
}

//скрытие футера
function my_plugin_body_class($classes) {
    $allowed_pages = [
        'fizicheskaya-ohrana',
        'pozharnyj-monitoring',
        'ohrana-perimetra',
        'knopka-trevozhnogo-vyzova',
        'uslugi',
        'domofon',
        'mobilnaya-trevozhnaya-knopka',
        'pultovoe-nablyudenie',
    ];
    if ( is_page( $allowed_pages ) ) {
        $classes[] = 'uslugi-page';
    }
    return $classes;
}
add_filter('body_class', 'my_plugin_body_class');


//tiny degrees 360
function tiny_degrees() {
    $allowed_pages = [
        'ajax',
    ];
    if ( is_page( $allowed_pages ) ) {
        add_shortcode( 'tiny_degrees', 'tiny_func' );
        function tiny_func($atts){
            $html = '';
            ob_start();
            $atts = shortcode_atts( array(
                'color' => 'Black',
                'name'  => 'DoorProtect'
            ), $atts );
            $color = $atts['color'];
            $name = $atts['name'];
            $allowed_names = [
                'DoorProtect',
                'Hub',
                'MotionProtect',
                'SpaceControl'
            ];
            if(empty($name) || !in_array($name, $allowed_names)){
                return 'Выберите название [tiny_degrees name="?"] (DoorProtect, Uub, MotionProtect, SpaceControl)<br> и цвет (не обязательно)';
            }
            $devicesurl = get_template_directory_uri() . '/assets/images/devices/';
            $devicesdir = get_template_directory() . '/assets/images/devices/';
            $path= $devicesdir . $name . '/' . $color;
            $url = $devicesurl . $name . '/' . $color;
            $html .= '<div class="tiny-menu">';
            $html .= '<ul class="tiny-list">';
            foreach (glob($path."/*.{jpg,png,gif}", GLOB_BRACE) as $filename){
                $img = basename($filename);
                $html .= '<li><img src="'.$url.'/'.$img.'" /></li>';
            }
            $html .= '</ul>';
            $html .= '</div>';
            $html .= ob_get_clean();
            return apply_filters('tiny_filter', $html);
        }
        function tiny_css(){ ?>
            <style>
                .tiny-menu {
                    width: 100%;
                    max-width:400px;
                    height: auto;
                    max-height: 255px;
                    margin: 30px auto 0;
                    overflow: hidden;
                }
                .tiny-menu ul {
                    width: 100%;
                    max-width:400px;
                    height: auto;
                    max-height: 255px;
                }
                .tiny-menu li:not(:first-child) {
                    display:none;
                }
                .tiny-menu ul li img {
                    display: block;
                    width: 100%;
                    max-width:400px;
                    height: auto;
                    max-height: 255px;
                }
            </style>
        <?php }
        add_action('wp_head','tiny_css');
        wp_register_script('jquery-punch', get_template_directory_uri() . '/assets/js/jquery.ui.touch-punch.min.js', array('jquery'), null, true);
        wp_register_script('tiny_degrees', get_template_directory_uri() . '/assets/js/tiny-degrees.js', array('jquery'), null, true);
        wp_enqueue_script('jquery-punch');
        wp_enqueue_script('tiny_degrees');
    }
}
add_action( 'wp_enqueue_scripts', 'tiny_degrees' );


//cyr to lat
function dfd_translit($s) {
    $s = (string) $s; // преобразуем в строковое значение
    $s = strip_tags($s); // убираем HTML-теги
    $s = str_replace(array("\n", "\r"), " ", $s); // убираем перевод каретки
    $s = preg_replace("/\s+/", ' ', $s); // удаляем повторяющие пробелы
    $s = trim($s); // убираем пробелы в начале и конце строки
    $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s); // переводим строку в нижний регистр (иногда надо задать локаль)
    $s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>''));
    $s = preg_replace("/[^0-9a-z-_ ]/i", "", $s); // очищаем строку от недопустимых символов
    $s = str_replace(" ", "-", $s); // заменяем пробелы знаком минус
    return $s; // возвращаем результат
}


