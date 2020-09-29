<style>
    .payment-header .payment-widget {
        max-width: 420px;
        width: 100%;
        background: white;
        box-shadow: 1px 1px 20px rgba(0, 0, 0, 0.1);
        margin: 0 auto;
        overflow: hidden;
        position: relative;
        z-index: 2;
    }
    .payment-widget .widget-header {
        width: 100%;
        height: 80px;
        background: #0e83cb;
        text-align: center;
    }
    .widget-header p {
        line-height: 80px;
        margin-left: 0px;
        color: white;
        font-size: 35px;
        text-transform: uppercase;
    }
    .payment-widget .widget-content {
        padding: 35px 40px;
        overflow: hidden;
    }
    .payment-widget form span {
        color: #000;
        font-size: 1.1em;
        display: block;
    }

    form .sum-fields .sum-field:nth-child(1) {
        margin-right: 20px;
    }

    form .sum-fields input {
        width: 120px;
        font-size: 32px;
        display: block;
        margin-bottom: 0;
    }

    .payment-widget form input {
        display: inline-block;
        line-height: 40px;
        background: transparent;
        border: none;
        outline: none;
        color: #000;
        padding-bottom: 3px;
        margin-bottom: 0;
    }

    .payment-widget .field-item {
        margin-bottom: 30px;
        border-bottom: 2px solid #dcdcdc;
    }
    .payment-widget .input-sum > * {
        vertical-align: middle;
        display: inline-block;
        line-height: 40px;
        font-size: 1.1em;
    }
    .payment-widget .input-sum input {
        font-size: 32px;
        line-height: 32px;
    }
    .payment-widget .input-sum input::placeholder,
    .payment-widget .input-sum input::-webkit-input-placeholder{
        color: #000;
        font-size: 32px;
        line-height: 32px;
    }

    .payment-widget .submit-form, .payment-widget .submit-payment {
        line-height: 50px;
        text-align: center;
        cursor: pointer;
        display: block;
    }

    .payment-widget .loader-container {
        width: 100%;
        height: 100%;
        background: white;
        position: absolute;
        top: 0;
        left: 0;
        display: none;
        text-align: center;
    }
    .payment-widget .loader-container .loader {
        width: 100%;
        height: 100%;
        position: relative;
    }
    .payment-widget .loader-container .loader svg {
        position: absolute;
        top: calc(50% - 45px);
        left: calc(50% - 45px);
    }
    .payment-widget .submit-payment {
        width: 100%;
        height: 50px;
        background: #0e83cb;
        color: white;
        margin: 0;
        border: 2px solid #0e83cb;
        border-radius: 3px;
        font-size: 20px;
        text-transform: uppercase;
        transition: all 0.2s ease-in;
    }
    .payment-widget .submit-payment:hover {
        background: transparent;
        color: #0e83cb;
    }
    .payment-widget .wayforpay-form {
        display: none;
    }
    .payment-widget .alert {
        margin: 10px;
    }
    .payment-widget .alert p {
        margin-bottom: 0;
        color:red;
    }
    .payment-top {
        max-width: 420px;
        width: 100%;
        margin: 0 auto 30px;
        text-align: center;
    }
    .payment-top .top-title {
        font-size: 25px;
    }
    @media only screen and (max-width: 580px){
        .payment-wrapper {
            padding: 0 10px;
        }
        .payment-widget .widget-content {
            padding: 20px;
        }
    }
</style>
<div class="payment-wrapper">
    <div class="payment-top">
        <?php
        $title = get_option('payment_title_ajax');
        if(!empty($title)){
            echo '<p class="top-title">'.$title.'</p>';
        }
        $listenter = json_decode(get_option('enterprise_list',''));
        $companies = array(
            'Охранные системы' => 'shumer_com_ua1',
            'Вялов С. В.' => 'shumer_com_ua',
            'Вялова Н. И.' => 'shumer_com_ua2',
            'Вялов Ю. С.' => 'shumer_com_ua3'
        );
        if(!empty($listenter) && is_array($listenter)){ ?>
            <div class="wpb_wrapper widget">
                <select autocomplete="off" class="payment_select" style="">
                    <option disabled selected class="hidden-option"><?php echo 'Выберите предприятие'; ?></option>
                    <?php
                    foreach($listenter as $opt){
                        $company = isset($companies[$opt])? $companies[$opt] : '';
                        echo '<option value="'.$company.'">'.$opt.'</option>';
                    }
                    ?>
                </select>
            </div>
            <?php
        } ?>
    </div>
    <div class="payment-header">
        <div class="payment-widget">
            <div class="widget-header"><p>Оплата услуг</p></div>
            <div class="widget-content">
                <form method="POST" action="" accept-charset="utf-8">
                    <div class="field-item number">
                        <span>Номер договора</span>
                        <input type="text" name="number" id="payment_id" value="" required autofocus="true">
                    </div>
                    <div class="field-item sum-fields">
                        <span>Сумма</span>
                        <div class="input-sum">
                            <input type="text" name="pay_sum" id="pay_sum" value="" required placeholder="0.00">
                            <span>грн.</span>
                        </div>
                    </div>
                    <span class="submit-payment">Продолжить</span>
                </form>
                <div class="payment-btn-container"></div>
                <p class="alert"></p>
                <div class="wayforpay-form"></div>
                <div class="loader-container animated fadeIn">
                    <div class="loader" title="2">
                        <svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="90px" height="90px" viewBox="0 0 50 50" xml:space="preserve">
<path fill="#000" d="M43.935,25.145c0-10.318-8.364-18.683-18.683-18.683c-10.318,0-18.683,8.365-18.683,18.683h4.068c0-8.071,6.543-14.615,14.615-14.615c8.072,0,14.615,6.543,14.615,14.615H43.935z" transform="rotate(256.763 25 25)">
    <animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 25 25" to="360 25 25" dur="0.6s" repeatCount="indefinite"></animateTransform>
</path>
</svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


