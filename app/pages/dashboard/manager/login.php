<?php
Inc::clas('manager');
// withGet: false，避免把自己網址上（可能已帶有 redirect 參數）的 query string 原封不動地接到目的地，造成 redirect 參數一直巢狀累加
if(Manager::current()){ Router::redirect('dashboard/permission/', withGet: false); }

Inc::clas('captcha');

// 登入成功後要導回的頁面；統一加上 ROOT 前綴，避免被帶去站外網址
$redirect = trim(Type::string($_GET['redirect'] ?? '', ''), '/');
if($redirect === ''){ $redirect = 'dashboard/permission/'; }
?>
<?php Inc::component('header'); ?>
<?php Inc::component('navbar'); ?>

<div id="Login" class="ts-container" style="max-width: 360px;">
    <h2>登入</h2>
    <form @submit.prevent="submit()">
        <div class="ts-text is-label">帳號</div>
        <div class="ts-input u-top-spaced">
            <input type="text" v-model="datas.account" autocomplete="username">
        </div>
        <div class="ts-text is-label u-top-spaced">密碼</div>
        <div class="ts-input u-top-spaced">
            <input type="password" v-model="datas.password" autocomplete="current-password">
        </div>
        <template v-if="showCaptcha">
            <div class="ts-text is-label u-top-spaced">驗證碼</div>
            <div class="u-top-spaced">
                <img :src="captchaSrc" style="cursor: pointer; vertical-align: middle;" @click="refreshCaptcha()">
            </div>
            <div class="ts-input u-top-spaced">
                <input type="text" v-model="datas.captcha">
            </div>
        </template>
        <div class="u-top-spaced" v-if="message">{{ message }}</div>
        <button class="ts-button u-top-spaced" type="submit" :disabled="is.submitting">登入</button>
    </form>
    <p class="u-top-spaced">預設帳號：<code>admin</code>　密碼：<code>admin</code>（root，可控制所有人）</p>
</div>

<script type="module">
    import '<?=Uri::js('ajax')?>';
    import { createApp, reactive, ref } from '<?=Uri::js('vue')?>';

    createApp({setup(){
        let datas = reactive({ account: '', password: '', captcha: '' });
        let is = reactive({ submitting: false });
        let message = ref('');
        let showCaptcha = ref(false);
        let captchaSrc = ref('<?=Captcha::src()?>');

        const refreshCaptcha = () => { captchaSrc.value = '<?=Captcha::src()?>?' + Math.random(); };

        const submit = () => {
            is.submitting = true;
            message.value = '';
            $.ajax({
                type: 'post',
                url: '<?=Uri::api('dashboard/login')?>/',
                data: datas,
            }).done((resp) => {
                if(resp.type === 'success'){
                    window.location.href = '<?=Uri::page('')?>' + <?=json_encode($redirect)?>;
                }else{
                    message.value = resp.message;
                    if(resp.status === 'needs_captcha' || resp.status === 'captcha_not_match'){
                        showCaptcha.value = true;
                        refreshCaptcha();
                    }
                }
            }).fail(() => {
                message.value = '登入時發生錯誤';
            }).always(() => {
                is.submitting = false;
            });
        };
        return { datas, is, message, showCaptcha, captchaSrc, refreshCaptcha, submit };
    }}).mount('#Login');
</script>

<?php Inc::component('footer'); ?>
