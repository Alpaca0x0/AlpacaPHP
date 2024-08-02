<?php Inc::component('header'); ?>
<?php Inc::component('navbar'); ?>

<div id="Demo" class="ts-container">
    <form id="Form" @submit.prevent="submit()">
        <div class="ts-grid is-2-columns">
            <div class="column">
                <div class="ts-text is-label">姓名</div>
                <div class="ts-input u-top-spaced">
                    <input type="text" v-model="datas.name">
                </div>
            </div>
            <div class="column">
                <div class="ts-text is-label">年紀</div>
                <div class="ts-input u-top-spaced">
                    <input type="number" v-model.number="datas.age">
                </div>
            </div>
        </div>
        <button class="ts-button" type="submit">Submit</button>
    </form>
</div>

<div class="ts-divider is-section"></div>

<div class="ts-container">
    <?php Inc::component('info');?>
</div>

<script type="module">
    import '<?=Uri::js('ajax')?>';
    import { createApp, reactive, } from '<?=Vue::uri()?>';
    // 
    const el = {
        form: document.querySelector('form#Form'),
    };
    let is = {
        submitting: false 
    };
    // 
    const Demo = createApp({setup(){
        let datas = reactive({
            name: '',
            age: '',
        });
        // 
        const submit = () => {
            is.submitting = true;
            $.ajax({
                type: 'post',
                url: '<?=Uri::auth('demo')?>',
                data: datas,
            }).fail((resp) => {
                console.error(resp);
            }).done((resp) => {
                console.log(resp);
            }).always(() => {
                is.submitting = false;
            });
        };
        return { el, submit, datas }
    }}).mount('div#Demo');
    // 
</script>

<?php Inc::component('footer'); ?>
